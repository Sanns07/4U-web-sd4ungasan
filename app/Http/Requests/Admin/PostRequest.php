<?php

namespace App\Http\Requests\Admin;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Post;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        $title = trim((string) $this->input('title'));
        $slug = trim((string) $this->input('slug'));

        $normalized = [
            'title' => $title,
            'slug' => Str::slug($slug !== '' ? $slug : $title),
            'excerpt' => trim((string) $this->input('excerpt')),
            'body_html' => (string) $this->input('body_html'),
            'cover_alt_text' => trim((string) $this->input('cover_alt_text')),
        ];

        if ($this->exists('existing_images')) {
            $normalized['existing_images'] = $this->normalizeImageMetadata($this->input('existing_images'));
        }

        if ($this->exists('inline_images')) {
            $normalized['inline_images'] = $this->normalizeImageMetadata($this->input('inline_images'));
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        $post = $this->route('post');
        $hasCover = $post instanceof Post && $post->cover_image_path !== null;

        return [
            'type' => ['required', Rule::enum(PostType::class)],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', Rule::unique('posts', 'slug')->ignore($post)],
            'excerpt' => ['required', 'string', 'max:220'],
            'body_html' => ['required', 'string', 'max:250000'],
            'cover_image' => [Rule::requiredIf(! $hasCover), 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cover_alt_text' => [Rule::requiredIf($hasCover || $this->hasFile('cover_image')), 'nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(PostStatus::class)],
            'published_at' => ['nullable', 'date'],
            'existing_images' => ['sometimes', 'array', 'max:10'],
            'existing_images.*.alt_text' => ['required', 'string', 'max:255'],
            'existing_images.*.caption' => ['nullable', 'string', 'max:500'],
            'inline_images' => ['sometimes', 'array', 'max:10'],
            'inline_images.*.file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'inline_images.*.alt_text' => ['required', 'string', 'max:255'],
            'inline_images.*.caption' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + [
            'type' => 'jenis informasi',
            'title' => 'judul',
            'slug' => 'slug',
            'excerpt' => 'ringkasan',
            'body_html' => 'isi artikel',
            'cover_image' => 'gambar cover',
            'cover_alt_text' => 'alt text cover',
            'status' => 'status publikasi',
            'published_at' => 'tanggal terbit',
            'existing_images.*.alt_text' => 'alt text gambar inline',
            'existing_images.*.caption' => 'caption gambar inline',
            'inline_images.*.file' => 'file gambar inline',
            'inline_images.*.alt_text' => 'alt text gambar inline',
            'inline_images.*.caption' => 'caption gambar inline',
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.',
            'cover_image.required' => 'Gambar cover wajib diunggah.',
            'cover_alt_text.required' => 'Alt text cover wajib diisi.',
            'inline_images.max' => 'Satu artikel hanya boleh memiliki maksimal 10 gambar inline.',
            'inline_images.*.alt_text.required' => 'Setiap gambar inline wajib memiliki alt text.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $post = $this->route('post');
            $existingImages = $this->input('existing_images', []);

            if (is_array($existingImages)) {
                $ownedImageIds = $post instanceof Post
                    ? $post->images()->pluck('id')->map(fn (int $id): string => (string) $id)->all()
                    : [];

                foreach ($existingImages as $id => $metadata) {
                    if (! in_array((string) $id, $ownedImageIds, true)) {
                        $validator->errors()->add('existing_images', 'Referensi gambar inline tidak valid untuk artikel ini.');

                        break;
                    }

                    if (is_array($metadata) && $this->isVisiblyEmpty($metadata['alt_text'] ?? null)) {
                        $validator->errors()->add("existing_images.{$id}.alt_text", 'Setiap gambar inline wajib memiliki alt text.');
                    }
                }
            }

            $inlineImages = $this->input('inline_images', []);

            if (is_array($inlineImages)) {
                foreach ($inlineImages as $token => $metadata) {
                    if (is_array($metadata) && $this->isVisiblyEmpty($metadata['alt_text'] ?? null)) {
                        $validator->errors()->add("inline_images.{$token}.alt_text", 'Setiap gambar inline wajib memiliki alt text.');
                    }
                }
            }

            $requiresCoverAlt = ($post instanceof Post && filled($post->cover_image_path)) || $this->hasFile('cover_image');

            if ($requiresCoverAlt && $this->isVisiblyEmpty($this->input('cover_alt_text'))) {
                $validator->errors()->add('cover_alt_text', 'Alt text cover wajib diisi.');
            }
        });
    }

    /** @return array<int|string, mixed> */
    private function normalizeImageMetadata(mixed $images): array
    {
        if (! is_array($images)) {
            return [];
        }

        foreach ($images as $key => $metadata) {
            if (! is_array($metadata)) {
                continue;
            }

            if (array_key_exists('alt_text', $metadata)) {
                $metadata['alt_text'] = trim((string) $metadata['alt_text']);
            }

            if (array_key_exists('caption', $metadata)) {
                $metadata['caption'] = trim((string) $metadata['caption']);
            }

            $images[$key] = $metadata;
        }

        return $images;
    }

    private function isVisiblyEmpty(mixed $value): bool
    {
        $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/[\s\x{00A0}\x{200B}-\x{200D}\x{2060}\x{FEFF}]+/u', '', $value) ?? '';

        return $value === '';
    }
}
