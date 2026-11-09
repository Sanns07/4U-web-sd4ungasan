<?php

namespace Tests\Feature\Admin;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_admin_can_render_all_post_pages_and_student_is_forbidden(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $post = Post::factory()->create(['author_user_id' => $admin->id]);

        $this->get('/admin/posts')->assertRedirect('/login');

        foreach (['/admin/posts', '/admin/posts/create', "/admin/posts/{$post->id}/edit", "/admin/posts/{$post->id}/preview"] as $uri) {
            $this->actingAs($admin)->get($uri)->assertOk();
            $this->actingAs($student)->get($uri)->assertForbidden();
        }

        $this->actingAs($admin)->get('/admin/posts/create')
            ->assertSee('data-post-editor', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('data-inline-image-template', false);
    }

    public function test_admin_can_create_update_publish_archive_and_permanently_delete_post(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/posts', $this->validData())
            ->assertRedirect()
            ->assertSessionHas('success');

        $post = Post::query()->where('slug', 'agenda-sekolah')->firstOrFail();
        $coverPath = $post->cover_image_path;
        $this->assertSame(PostStatus::Draft, $post->status);
        $this->assertStringNotContainsString('onclick', $post->body_html);
        Storage::disk('public')->assertExists($coverPath);

        $this->actingAs($admin)->put("/admin/posts/{$post->id}", $this->validData([
            'title' => 'Agenda Sekolah Diperbarui',
            'slug' => $post->slug,
            'cover_image' => null,
            'cover_alt_text' => 'Agenda sekolah diperbarui',
        ]))->assertSessionHas('success');

        $this->assertSame('Agenda Sekolah Diperbarui', $post->fresh()->title);
        $this->actingAs($admin)->patch("/admin/posts/{$post->id}/publikasikan")
            ->assertSessionHas('success');
        $this->assertSame(PostStatus::Published, $post->fresh()->status);
        $this->get("/informasi/{$post->slug}")->assertOk();

        $this->actingAs($admin)->patch("/admin/posts/{$post->id}/arsipkan")
            ->assertSessionHas('success');
        $this->get("/informasi/{$post->slug}")->assertNotFound();

        $this->actingAs($admin)->delete("/admin/posts/{$post->id}")
            ->assertRedirect('/admin/posts')
            ->assertSessionHas('success');
        $this->assertNull(Post::withTrashed()->find($post->id));
        Storage::disk('public')->assertMissing($coverPath);
    }

    public function test_post_filters_empty_state_and_safe_old_editor_input_render(): void
    {
        $admin = User::factory()->admin()->create();
        Post::factory()->create([
            'author_user_id' => $admin->id,
            'title' => 'Pengumuman Ujian',
            'type' => PostType::Announcement,
            'status' => PostStatus::Archived,
            'published_at' => '2026-08-10 08:00:00',
        ]);

        $this->actingAs($admin)->get('/admin/posts?type=pengumuman&status=archived&month=2026-08')
            ->assertOk()
            ->assertSee('Pengumuman Ujian');
        $this->actingAs($admin)->get('/admin/posts?q=tidak-ada-sama-sekali')
            ->assertOk()
            ->assertSee('Konten tidak ditemukan.');

        $this->actingAs($admin)->from('/admin/posts/create')->post('/admin/posts', $this->validData([
            'title' => '',
            'slug' => '',
            'body_html' => '<p>Isi aman</p><script>window.__xss = true</script>',
        ]))->assertRedirect('/admin/posts/create')->assertSessionHasErrors(['title', 'slug']);

        $this->actingAs($admin)->get('/admin/posts/create')
            ->assertOk()
            ->assertSee('<p>Isi aman</p>', false)
            ->assertDontSee('<script>window.__xss', false);
    }

    /** @return array<string, mixed> */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'type' => PostType::News->value,
            'title' => 'Agenda Sekolah',
            'slug' => 'agenda-sekolah',
            'excerpt' => 'Ringkasan agenda sekolah untuk seluruh warga sekolah.',
            'body_html' => '<p onclick="alert(1)">Isi agenda sekolah.</p>',
            'cover_image' => $this->fakeImage('cover.jpg'),
            'cover_alt_text' => 'Kegiatan agenda sekolah',
            'status' => PostStatus::Draft->value,
            'published_at' => null,
            'inline_images' => [],
        ], $overrides);
    }

    private function fakeImage(string $name): UploadedFile
    {
        $content = base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2Q==', true);

        return UploadedFile::fake()->createWithContent($name, $content);
    }
}
