<?php

namespace Tests\Feature\Admin;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Post;
use App\Models\User;
use App\Services\PostContentService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PostContentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PostContentService $service;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->service = app(PostContentService::class);
        $this->admin = User::factory()->admin()->create();
    }

    public function test_it_saves_sanitized_multi_image_content_in_editor_order(): void
    {
        $post = $this->service->save(new Post, $this->validData([
            'body_html' => '<p onclick="alert(1)">Pembuka</p><script>alert(1)</script>'
                .'<figure data-image-token="depan"><img src="https://evil.test/x.jpg"></figure>'
                .'<h2>Agenda</h2><iframe src="https://evil.test"></iframe>'
                .'<figure data-image-token="belakang"></figure><p>Penutup</p>',
            'cover_alt_text' => '  Gedung sekolah  ',
            'inline_images' => [
                'depan' => [
                    'file' => $this->fakeImage('depan.jpg', 200),
                    'alt_text' => '  Siswa di depan sekolah  ',
                    'caption' => '  Suasana pagi  ',
                ],
                'belakang' => [
                    'file' => $this->fakeImage('belakang.png', 200),
                    'alt_text' => 'Kegiatan kelas',
                    'caption' => '   ',
                ],
            ],
        ]), $this->admin);

        $this->assertSame(PostStatus::Draft, $post->status);
        $this->assertNull($post->published_at);
        $this->assertSame('Gedung sekolah', $post->cover_alt_text);
        $this->assertCount(2, $post->images);
        $this->assertSame('Siswa di depan sekolah', $post->images[0]->alt_text);
        $this->assertSame('Suasana pagi', $post->images[0]->caption);
        $this->assertNull($post->images[1]->caption);
        $this->assertStringNotContainsString('script', strtolower($post->body_html));
        $this->assertStringNotContainsString('iframe', strtolower($post->body_html));
        $this->assertStringNotContainsString('onclick', strtolower($post->body_html));
        $this->assertStringNotContainsString('<img', strtolower($post->body_html));

        $firstMarker = '<figure data-image-id="'.$post->images[0]->id.'"></figure>';
        $secondMarker = '<figure data-image-id="'.$post->images[1]->id.'"></figure>';
        $this->assertMatchesRegularExpression(
            '/<p>Pembuka<\/p>.*'.preg_quote($firstMarker, '/').'.*<h2>Agenda<\/h2>.*'.preg_quote($secondMarker, '/').'.*<p>Penutup<\/p>/s',
            $post->body_html,
        );

        Storage::disk('public')->assertExists($post->cover_image_path);

        foreach ($post->images as $image) {
            Storage::disk('public')->assertExists($image->file_path);
        }

        $rendered = $post->renderedBodyHtml();
        $this->assertSame(2, substr_count($rendered, '<img '));
        $this->assertStringContainsString('alt="Siswa di depan sekolah"', $rendered);
        $this->assertStringContainsString('<figcaption>Suasana pagi</figcaption>', $rendered);
    }

    public function test_blank_or_zero_width_body_and_alt_text_are_rejected_before_files_are_written(): void
    {
        foreach (['<p> &nbsp; </p>', "<p>\u{200B}\u{200C}\u{FEFF}</p>"] as $body) {
            try {
                $this->service->save(new Post, $this->validData(['body_html' => $body]), $this->admin);
                $this->fail('Body tanpa teks terlihat seharusnya ditolak.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('body_html', $exception->errors());
            }
        }

        foreach (['   ', "\u{200B}\u{2060}"] as $altText) {
            try {
                $this->service->save(new Post, $this->validData(['cover_alt_text' => $altText]), $this->admin);
                $this->fail('Alt text cover kosong seharusnya ditolak.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('cover_alt_text', $exception->errors());
            }
        }

        $this->assertDatabaseCount('posts', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_inline_image_limit_duplicate_markers_and_missing_alt_are_rejected(): void
    {
        $slots = [];
        $markers = '';

        foreach (range(1, 11) as $number) {
            $token = 'slot-'.$number;
            $markers .= '<figure data-image-token="'.$token.'"></figure>';
            $slots[$token] = [
                'file' => $this->fakeImage("{$token}.jpg"),
                'alt_text' => "Gambar {$number}",
            ];
        }

        try {
            $this->service->save(new Post, $this->validData([
                'body_html' => '<p>Artikel</p>'.$markers,
                'inline_images' => $slots,
            ]), $this->admin);
            $this->fail('Gambar inline ke-11 seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('inline_images', $exception->errors());
        }

        try {
            $this->service->save(new Post, $this->validData([
                'body_html' => '<p>Artikel</p><figure data-image-token="satu"></figure><figure data-image-token="satu"></figure>',
                'inline_images' => [
                    'satu' => [
                        'file' => $this->fakeImage('satu.jpg'),
                        'alt_text' => 'Gambar satu',
                    ],
                ],
            ]), $this->admin);
            $this->fail('Marker gambar ganda seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('body_html', $exception->errors());
        }

        try {
            $this->service->save(new Post, $this->validData([
                'body_html' => '<p>Artikel</p><figure data-image-token="satu"></figure>',
                'inline_images' => [
                    'satu' => [
                        'file' => $this->fakeImage('satu.jpg'),
                        'alt_text' => "\u{200B}",
                    ],
                ],
            ]), $this->admin);
            $this->fail('Alt text gambar inline kosong seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('inline_images.satu.alt_text', $exception->errors());
        }

        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_database_failure_rolls_back_record_and_newly_stored_files(): void
    {
        Post::factory()->create(['slug' => 'slug-yang-sama']);

        try {
            $this->service->save(new Post, $this->validData(['slug' => 'slug-yang-sama']), $this->admin);
            $this->fail('Slug duplikat seharusnya memicu kegagalan database.');
        } catch (QueryException) {
            // Expected: the service must remove the cover written before the transaction failed.
        }

        $this->assertDatabaseCount('posts', 1);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_inline_storage_failure_rolls_back_database_and_cleans_every_new_file(): void
    {
        try {
            $this->service->save(new Post, $this->validData([
                'body_html' => '<p>Artikel</p><figure data-image-token="valid"></figure><figure data-image-token="rusak"></figure>',
                'inline_images' => [
                    'valid' => [
                        'file' => $this->fakeImage('valid.jpg'),
                        'alt_text' => 'Gambar valid',
                    ],
                    'rusak' => [
                        'file' => 'bukan-upload',
                        'alt_text' => 'Gambar rusak',
                    ],
                ],
            ]), $this->admin);
            $this->fail('Slot tanpa UploadedFile seharusnya menggagalkan transaksi.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('inline_images', $exception->errors());
        }

        $this->assertDatabaseCount('posts', 0);
        $this->assertDatabaseCount('post_images', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_replacing_cover_and_removing_inline_image_cleans_orphan_files_after_commit(): void
    {
        Storage::disk('public')->put('posts/covers/lama.jpg', 'cover-lama');
        Storage::disk('public')->put('posts/inline/lama.jpg', 'inline-lama');
        $post = Post::factory()->create([
            'author_user_id' => $this->admin->id,
            'cover_image_path' => 'posts/covers/lama.jpg',
            'cover_alt_text' => 'Cover lama',
            'body_html' => '<p>Isi lama</p>',
        ]);
        $image = $post->images()->create([
            'file_path' => 'posts/inline/lama.jpg',
            'alt_text' => 'Gambar lama',
            'caption' => null,
        ]);
        $post->update(['body_html' => '<p>Isi lama</p><figure data-image-id="'.$image->id.'"></figure>']);

        $saved = $this->service->save($post, $this->validData([
            'slug' => $post->slug,
            'body_html' => '<p>Isi baru tanpa gambar inline.</p>',
            'cover_image' => $this->fakeImage('baru.webp'),
            'cover_alt_text' => '  Cover baru  ',
            'inline_images' => [],
        ]), $this->admin);

        $this->assertSame('Cover baru', $saved->cover_alt_text);
        $this->assertCount(0, $saved->images);
        $this->assertDatabaseMissing('post_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing('posts/covers/lama.jpg');
        Storage::disk('public')->assertMissing('posts/inline/lama.jpg');
        Storage::disk('public')->assertExists($saved->cover_image_path);
    }

    public function test_foreign_image_marker_is_removed_without_deleting_its_owned_record_or_file(): void
    {
        Storage::disk('public')->put('posts/inline/milik-a.jpg', 'milik-a');
        $postA = Post::factory()->create(['author_user_id' => $this->admin->id]);
        $foreignImage = $postA->images()->create([
            'file_path' => 'posts/inline/milik-a.jpg',
            'alt_text' => 'Milik artikel A',
        ]);
        $postB = Post::factory()->create([
            'author_user_id' => $this->admin->id,
            'cover_image_path' => 'posts/covers/b.jpg',
            'cover_alt_text' => 'Cover B',
        ]);
        Storage::disk('public')->put('posts/covers/b.jpg', 'cover-b');

        $saved = $this->service->save($postB, $this->validData([
            'slug' => $postB->slug,
            'cover_image' => null,
            'cover_alt_text' => 'Cover B',
            'body_html' => '<p>Isi artikel B</p><figure data-image-id="'.$foreignImage->id.'"></figure>',
        ]), $this->admin);

        $this->assertStringNotContainsString('data-image-id', $saved->body_html);
        $this->assertDatabaseHas('post_images', ['id' => $foreignImage->id, 'post_id' => $postA->id]);
        Storage::disk('public')->assertExists('posts/inline/milik-a.jpg');
    }

    public function test_publish_archive_future_schedule_and_hard_delete_lifecycle_are_enforced(): void
    {
        $post = $this->service->save(new Post, $this->validData(), $this->admin);
        $coverPath = $post->cover_image_path;

        $published = $this->service->publish($post);
        $this->assertSame(PostStatus::Published, $published->status);
        $this->assertNotNull($published->published_at);
        $this->assertTrue($published->published_at->between(now()->subMinute(), now()->addMinute()));

        try {
            $this->service->deleteArchived($published);
            $this->fail('Konten yang belum diarsipkan seharusnya tidak dapat dihapus permanen.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }

        Storage::disk('public')->assertExists($coverPath);
        $archived = $this->service->archive($published);
        $this->assertSame(PostStatus::Archived, $archived->status);
        $this->service->deleteArchived($archived);

        $this->assertNull(Post::withTrashed()->find($post->id));
        Storage::disk('public')->assertMissing($coverPath);

        $futureDate = now()->addDays(3)->startOfMinute();
        $future = $this->service->save(new Post, $this->validData([
            'slug' => 'informasi-terjadwal',
            'status' => PostStatus::Published->value,
            'published_at' => $futureDate->toDateTimeString(),
        ]), $this->admin);

        $this->assertSame(PostStatus::Published, $future->status);
        $this->assertTrue($future->published_at->equalTo($futureDate));
        $this->assertTrue($future->published_at->isFuture());
    }

    /** @return array<string, mixed> */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'type' => PostType::News->value,
            'title' => 'Kegiatan Sekolah',
            'slug' => 'kegiatan-sekolah-'.fake()->unique()->numerify('####'),
            'excerpt' => 'Ringkasan kegiatan sekolah untuk warga sekolah.',
            'body_html' => '<p>Isi artikel yang dapat dibaca.</p>',
            'cover_image' => $this->fakeImage('cover.jpg', 200),
            'cover_alt_text' => 'Gedung sekolah',
            'status' => PostStatus::Draft->value,
            'published_at' => null,
            'inline_images' => [],
        ], $overrides);
    }

    private function fakeImage(string $name, int $kilobytes = 1): UploadedFile
    {
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $encoded = match ($extension) {
            'png' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            'webp' => 'UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEADsD+JaQAA3AAAAAA',
            default => '/9j/4AAQSkZJRgABAQAAAQABAAD/2Q==',
        };
        $content = base64_decode($encoded, true);
        $content .= str_repeat("\0", max(0, ($kilobytes * 1024) - strlen($content)));

        return UploadedFile::fake()->createWithContent($name, $content);
    }
}
