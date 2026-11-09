<?php

namespace Tests\Feature\Admin;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Http\Requests\Admin\PostRequest;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PostRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_request_normalizes_slug_cover_alt_and_inline_metadata(): void
    {
        $request = $this->validatedRequest($this->validInput([
            'title' => '  Prestasi Siswa Tingkat Provinsi  ',
            'slug' => '',
            'cover_alt_text' => '  Penyerahan piala  ',
            'inline_images' => [
                'slot-satu' => ['alt_text' => '  Tim siswa  ', 'caption' => '  Juara pertama  '],
            ],
        ]), [
            'cover_image' => $this->fakeImage('cover.jpg', 200),
            'inline_images' => [
                'slot-satu' => ['file' => $this->fakeImage('inline.png', 200)],
            ],
        ]);

        $validated = $request->validated();
        $this->assertSame('Prestasi Siswa Tingkat Provinsi', $validated['title']);
        $this->assertSame('prestasi-siswa-tingkat-provinsi', $validated['slug']);
        $this->assertSame('Penyerahan piala', $validated['cover_alt_text']);
        $this->assertSame('Tim siswa', $validated['inline_images']['slot-satu']['alt_text']);
        $this->assertSame('Juara pertama', $validated['inline_images']['slot-satu']['caption']);
    }

    public function test_request_accepts_jpeg_png_and_webp_up_to_two_megabytes(): void
    {
        $allowedFiles = [
            $this->fakeImage('cover.jpg', 2048),
            $this->fakeImage('cover.png', 2048),
            $this->fakeImage('cover.webp', 2048),
        ];

        foreach ($allowedFiles as $index => $file) {
            $request = $this->validatedRequest($this->validInput([
                'slug' => 'format-gambar-'.$index,
            ]), ['cover_image' => $file]);

            $this->assertSame('Gedung sekolah', $request->validated('cover_alt_text'));
        }
    }

    public function test_request_rejects_svg_oversized_files_eleventh_image_and_invisible_alt_text(): void
    {
        $this->assertValidationErrors(
            $this->validInput(),
            ['cover_image' => UploadedFile::fake()->createWithContent('cover.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>')],
            ['cover_image'],
        );
        $this->assertValidationErrors(
            $this->validInput(),
            ['cover_image' => $this->fakeImage('besar.jpg', 2049)],
            ['cover_image'],
        );
        $this->assertValidationErrors(
            $this->validInput(['cover_alt_text' => "\u{200B}\u{FEFF}"]),
            ['cover_image' => $this->fakeImage('cover.jpg')],
            ['cover_alt_text'],
        );

        $slots = [];
        $files = [];

        foreach (range(1, 11) as $number) {
            $slots["slot-{$number}"] = ['alt_text' => "Gambar {$number}", 'caption' => ''];
            $files["slot-{$number}"] = ['file' => $this->fakeImage("gambar-{$number}.jpg")];
        }

        $this->assertValidationErrors(
            $this->validInput(['inline_images' => $slots]),
            ['cover_image' => $this->fakeImage('cover.jpg'), 'inline_images' => $files],
            ['inline_images'],
        );
    }

    public function test_existing_image_metadata_must_belong_to_the_edited_post(): void
    {
        $postA = Post::factory()->create(['author_user_id' => $this->admin->id]);
        $foreignImage = $postA->images()->create([
            'file_path' => 'posts/inline/asing.jpg',
            'alt_text' => 'Asing',
        ]);
        $postB = Post::factory()->create([
            'author_user_id' => $this->admin->id,
            'cover_image_path' => 'posts/covers/b.jpg',
            'cover_alt_text' => 'Cover B',
        ]);

        $this->assertValidationErrors(
            $this->validInput([
                'slug' => $postB->slug,
                'cover_alt_text' => '  Cover baru  ',
                'existing_images' => [
                    $foreignImage->id => ['alt_text' => 'Gambar asing', 'caption' => ''],
                ],
            ]),
            [],
            ['existing_images'],
            $postB,
        );

        $request = $this->validatedRequest($this->validInput([
            'slug' => $postB->slug,
            'cover_alt_text' => '  Cover baru  ',
        ]), [], $postB);
        $this->assertSame('Cover baru', $request->validated('cover_alt_text'));
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $files
     */
    private function validatedRequest(array $input, array $files, ?Post $post = null): PostRequest
    {
        $request = PostRequest::create('/admin/posts', 'POST', $input, [], $files);
        $request->setContainer(app());
        $request->setRedirector(app('redirect'));
        $request->setUserResolver(fn (): User => $this->admin);
        $request->setRouteResolver(fn (): object => new class($post)
        {
            public function __construct(private readonly ?Post $post) {}

            public function parameter(string $key, mixed $default = null): mixed
            {
                return $key === 'post' ? $this->post : $default;
            }
        });
        $request->validateResolved();

        return $request;
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $files
     * @param  list<string>  $expectedKeys
     */
    private function assertValidationErrors(array $input, array $files, array $expectedKeys, ?Post $post = null): void
    {
        try {
            $this->validatedRequest($input, $files, $post);
            $this->fail('Request seharusnya gagal validasi.');
        } catch (ValidationException $exception) {
            foreach ($expectedKeys as $key) {
                $this->assertArrayHasKey($key, $exception->errors());
            }
        }
    }

    /** @return array<string, mixed> */
    private function validInput(array $overrides = []): array
    {
        return array_merge([
            'type' => PostType::News->value,
            'title' => 'Informasi Sekolah',
            'slug' => 'informasi-sekolah-'.fake()->unique()->numerify('####'),
            'excerpt' => 'Ringkasan informasi sekolah untuk masyarakat.',
            'body_html' => '<p>Isi informasi sekolah.</p>',
            'cover_alt_text' => 'Gedung sekolah',
            'status' => PostStatus::Draft->value,
            'published_at' => null,
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
