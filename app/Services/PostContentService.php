<?php

namespace App\Services;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class PostContentService
{
    public function __construct(private readonly PostHtmlSanitizer $sanitizer) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function save(Post $post, array $data, User $author): Post
    {
        $data['cover_alt_text'] = trim((string) ($data['cover_alt_text'] ?? $post->cover_alt_text));
        $existingImages = $post->exists ? $post->images()->get()->keyBy('id') : collect();
        $inlineImages = Arr::get($data, 'inline_images', []);

        if (! is_array($inlineImages)) {
            throw ValidationException::withMessages(['inline_images' => 'Data gambar inline tidak valid.']);
        }
        $allowedTokens = array_map('strval', array_keys($inlineImages));
        $preliminaryBody = $this->sanitizer->sanitize(
            (string) $data['body_html'],
            $existingImages->keys()->all(),
            $allowedTokens,
        );
        $existingIdsInBody = $this->sanitizer->imageIds($preliminaryBody);
        $tokensInBody = $this->sanitizer->imageTokens($preliminaryBody);

        $this->validateBodyAndImageReferences($preliminaryBody, $existingIdsInBody, $tokensInBody, $allowedTokens);
        $this->validateImageMetadata($post, $data, $existingImages, $existingIdsInBody, $tokensInBody, $inlineImages);

        $newPaths = [];
        $obsoletePaths = [];
        $oldCoverPath = $post->cover_image_path;

        try {
            $newCoverPath = $this->storeUpload(Arr::get($data, 'cover_image'), 'posts/covers');

            if ($newCoverPath) {
                $newPaths[] = $newCoverPath;
            }

            $savedPost = DB::transaction(function () use (
                $post,
                $data,
                $author,
                $existingImages,
                $inlineImages,
                $preliminaryBody,
                $tokensInBody,
                $newCoverPath,
                $oldCoverPath,
                &$newPaths,
                &$obsoletePaths,
            ): Post {
                $post->fill(Arr::only($data, ['type', 'title', 'slug', 'excerpt', 'cover_alt_text', 'status']));
                $post->author_user_id ??= $author->id;
                $post->body_html = '<p>Konten sedang diproses.</p>';

                if ($newCoverPath) {
                    $post->cover_image_path = $newCoverPath;

                    if ($oldCoverPath) {
                        $obsoletePaths[] = $oldCoverPath;
                    }
                }

                $this->applyPublicationDate($post, $data);
                $post->save();

                $this->updateExistingImageMetadata($existingImages, Arr::get($data, 'existing_images', []));

                $bodyHtml = $preliminaryBody;
                $newImageIds = [];

                foreach ($tokensInBody as $token) {
                    $uploadData = $inlineImages[$token] ?? null;

                    if (! is_array($uploadData) || ! ($uploadData['file'] ?? null) instanceof UploadedFile) {
                        throw ValidationException::withMessages([
                            'inline_images' => 'Setiap slot gambar yang disisipkan wajib memiliki file gambar.',
                        ]);
                    }

                    $path = $this->storeUpload($uploadData['file'], 'posts/inline');

                    if (! $path) {
                        throw new RuntimeException('File gambar inline tidak dapat disimpan.');
                    }

                    $newPaths[] = $path;
                    $image = $post->images()->create([
                        'file_path' => $path,
                        'alt_text' => trim((string) $uploadData['alt_text']),
                        'caption' => $this->nullableTrim($uploadData['caption'] ?? null),
                    ]);
                    $newImageIds[] = $image->id;
                    $bodyHtml = str_replace(
                        '<figure data-image-token="'.$token.'"></figure>',
                        '<figure data-image-id="'.$image->id.'"></figure>',
                        $bodyHtml,
                    );
                }

                $allowedFinalIds = $existingImages->keys()->merge($newImageIds)->all();
                $bodyHtml = $this->sanitizer->sanitize($bodyHtml, $allowedFinalIds);
                $referencedIds = $this->sanitizer->imageIds($bodyHtml);

                foreach ($existingImages as $image) {
                    if (! in_array($image->id, $referencedIds, true)) {
                        $obsoletePaths[] = $image->file_path;
                        $image->delete();
                    }
                }

                $post->body_html = $bodyHtml;
                $this->assertPublishable($post);
                $post->save();

                return $post->fresh(['author', 'images']);
            });
        } catch (Throwable $exception) {
            $this->deleteFiles($newPaths);

            throw $exception;
        }

        $this->deleteFiles(array_unique($obsoletePaths));

        return $savedPost;
    }

    public function publish(Post $post): Post
    {
        $this->assertPublishable($post);

        $post->update([
            'status' => PostStatus::Published,
            'published_at' => now(),
        ]);

        return $post->fresh(['author', 'images']);
    }

    public function archive(Post $post): Post
    {
        $post->update(['status' => PostStatus::Archived]);

        return $post->fresh(['author', 'images']);
    }

    public function deleteArchived(Post $post): void
    {
        if ($post->status !== PostStatus::Archived) {
            throw ValidationException::withMessages([
                'status' => 'Konten harus diarsipkan sebelum dihapus permanen dari panel.',
            ]);
        }

        $paths = array_filter([
            $post->cover_image_path,
            ...$post->images()->pluck('file_path')->all(),
        ]);

        DB::transaction(function () use ($post): void {
            $post->images()->delete();

            if (! $post->forceDelete()) {
                throw new RuntimeException('Konten tidak dapat dihapus permanen.');
            }
        });

        $this->deleteFiles($paths);
    }

    /**
     * @param  list<int>  $existingIds
     * @param  list<string>  $tokens
     * @param  list<string>  $submittedTokens
     */
    private function validateBodyAndImageReferences(string $bodyHtml, array $existingIds, array $tokens, array $submittedTokens): void
    {
        if ($this->visibleText($bodyHtml) === '') {
            throw ValidationException::withMessages(['body_html' => 'Isi artikel wajib memuat teks.']);
        }

        if (count($existingIds) !== count(array_unique($existingIds)) || count($tokens) !== count(array_unique($tokens))) {
            throw ValidationException::withMessages([
                'body_html' => 'Setiap gambar inline hanya boleh disisipkan satu kali dalam artikel.',
            ]);
        }

        if (count($existingIds) + count($tokens) > 10) {
            throw ValidationException::withMessages([
                'inline_images' => 'Satu artikel hanya boleh memiliki maksimal 10 gambar inline.',
            ]);
        }

        $unusedTokens = array_diff($submittedTokens, $tokens);

        if ($unusedTokens !== []) {
            throw ValidationException::withMessages([
                'inline_images' => 'Hapus slot gambar yang tidak disisipkan ke isi artikel atau sisipkan gambarnya pada posisi kursor.',
            ]);
        }
    }

    /**
     * @param  Collection<int, PostImage>  $existingImages
     * @param  list<int>  $existingIdsInBody
     * @param  list<string>  $tokensInBody
     * @param  array<int|string, mixed>  $inlineImages
     * @param  array<string, mixed>  $data
     */
    private function validateImageMetadata(
        Post $post,
        array $data,
        Collection $existingImages,
        array $existingIdsInBody,
        array $tokensInBody,
        array $inlineImages,
    ): void {
        $hasCover = Arr::get($data, 'cover_image') instanceof UploadedFile || filled($post->cover_image_path);

        if (! $hasCover) {
            throw ValidationException::withMessages(['cover_image' => 'Gambar cover wajib diunggah.']);
        }

        if ($this->visibleText((string) ($data['cover_alt_text'] ?? '')) === '') {
            throw ValidationException::withMessages(['cover_alt_text' => 'Alt text cover wajib diisi.']);
        }

        $existingMetadata = Arr::get($data, 'existing_images', []);
        $existingMetadata = is_array($existingMetadata) ? $existingMetadata : [];

        foreach ($existingIdsInBody as $imageId) {
            /** @var PostImage|null $image */
            $image = $existingImages->get($imageId);
            $metadata = $existingMetadata[$imageId] ?? $existingMetadata[(string) $imageId] ?? [];
            $altText = is_array($metadata) && array_key_exists('alt_text', $metadata)
                ? $metadata['alt_text']
                : $image?->alt_text;

            if ($this->visibleText((string) $altText) === '') {
                throw ValidationException::withMessages([
                    "existing_images.{$imageId}.alt_text" => 'Setiap gambar inline wajib memiliki alt text.',
                ]);
            }
        }

        foreach ($tokensInBody as $token) {
            $metadata = $inlineImages[$token] ?? null;

            if (! is_array($metadata) || $this->visibleText((string) ($metadata['alt_text'] ?? '')) === '') {
                throw ValidationException::withMessages([
                    "inline_images.{$token}.alt_text" => 'Setiap gambar inline wajib memiliki alt text.',
                ]);
            }
        }
    }

    /**
     * @param  Collection<int, PostImage>  $images
     * @param  array<int|string, array<string, mixed>>  $metadata
     */
    private function updateExistingImageMetadata($images, array $metadata): void
    {
        foreach ($images as $image) {
            $values = $metadata[$image->id] ?? $metadata[(string) $image->id] ?? null;

            if (! is_array($values)) {
                continue;
            }

            $image->update([
                'alt_text' => trim((string) $values['alt_text']),
                'caption' => $this->nullableTrim($values['caption'] ?? null),
            ]);
        }
    }

    /** @param array<string, mixed> $data */
    private function applyPublicationDate(Post $post, array $data): void
    {
        $status = $data['status'] instanceof PostStatus ? $data['status'] : PostStatus::from($data['status']);
        $requestedDate = filled($data['published_at'] ?? null) ? Carbon::parse($data['published_at']) : null;

        $post->published_at = match ($status) {
            PostStatus::Draft => null,
            PostStatus::Published => $requestedDate ?? ($post->published_at ?: now()),
            PostStatus::Archived => $requestedDate ?? $post->published_at,
        };
    }

    private function storeUpload(mixed $file, string $directory): ?string
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        $path = $file->store($directory, 'public');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('File gambar tidak dapat disimpan.');
        }

        return $path;
    }

    /** @param iterable<string> $paths */
    private function deleteFiles(iterable $paths): void
    {
        $paths = array_values(array_filter(iterator_to_array((function () use ($paths) {
            foreach ($paths as $path) {
                yield $path;
            }
        })(), false)));

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function assertPublishable(Post $post): void
    {
        if (
            ! $post->cover_image_path
            || $this->visibleText((string) $post->cover_alt_text) === ''
            || $this->visibleText((string) $post->body_html) === ''
        ) {
            throw ValidationException::withMessages([
                'status' => 'Konten belum lengkap. Cover, alt text cover, dan isi artikel wajib tersedia sebelum diterbitkan.',
            ]);
        }
    }

    private function visibleText(string $value): string
    {
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return preg_replace('/[\s\x{00A0}\x{200B}-\x{200D}\x{2060}\x{FEFF}]+/u', '', $value) ?? '';
    }
}
