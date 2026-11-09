<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Post;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_required_public_pages_render_final_navigation_and_no_foundation_placeholder(): void
    {
        $this->seed();

        foreach (['/', '/profil', '/guru', '/informasi'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSeeInOrder(['Beranda', 'Profil', 'Guru', 'Informasi', 'Masuk Portal'])
                ->assertDontSee('Tahap Fondasi');
        }
    }

    public function test_home_and_information_only_link_to_currently_published_posts(): void
    {
        $this->seed();

        $this->get('/')
            ->assertOk()
            ->assertSee('Penerimaan Peserta Didik Baru 2026')
            ->assertSee('Jadwal Pertemuan Orang Tua Semester Ganjil')
            ->assertDontSee('Catatan Internal Program Sekolah');

        $this->get('/informasi')
            ->assertOk()
            ->assertSee('Baca selengkapnya')
            ->assertDontSee('Catatan Internal Program Sekolah');

        $this->get('/informasi/penerimaan-peserta-didik-baru-2026')
            ->assertOk()
            ->assertSee('Penerimaan Peserta Didik Baru 2026')
            ->assertSee('Informasi pendaftaran');
    }

    public function test_unpublished_or_future_information_is_not_publicly_accessible(): void
    {
        $this->seed();
        $adminId = Post::query()->value('author_user_id');

        foreach ([
            ['slug' => 'masih-draft', 'status' => PostStatus::Draft, 'published_at' => null],
            ['slug' => 'sudah-diarsipkan', 'status' => PostStatus::Archived, 'published_at' => now()->subDay()],
            ['slug' => 'terbit-masa-depan', 'status' => PostStatus::Published, 'published_at' => now()->addDay()],
        ] as $state) {
            Post::query()->create([
                'author_user_id' => $adminId,
                'type' => PostType::News,
                'title' => str($state['slug'])->headline(),
                'slug' => $state['slug'],
                'excerpt' => 'Tidak boleh terlihat publik.',
                'body_html' => '<p>Konten privat.</p>',
                'status' => $state['status'],
                'published_at' => $state['published_at'],
            ]);
        }

        $response = $this->get('/informasi')->assertOk();

        foreach (['masih-draft', 'sudah-diarsipkan', 'terbit-masa-depan'] as $slug) {
            $response->assertDontSee(str($slug)->headline());
            $this->get("/informasi/{$slug}")->assertNotFound();
        }
    }

    public function test_profile_and_teacher_directory_use_database_visibility_rules_and_empty_search_state(): void
    {
        $this->seed();

        $this->get('/profil')
            ->assertOk()
            ->assertSee('SD No. 4 Ungasan')
            ->assertSee('Menjadi sekolah dasar yang unggul')
            ->assertSee('Kepala Sekolah');

        Teacher::factory()->create([
            'name' => 'Guru Tidak Publik',
            'gender' => Gender::Male,
            'is_active' => true,
            'is_public' => false,
        ]);

        $this->get('/guru')
            ->assertOk()
            ->assertSee('Ratna Dewi, M.Pd.')
            ->assertDontSee('Guru Tidak Publik');

        $this->get('/guru?q=nama-yang-tidak-ada')
            ->assertOk()
            ->assertSee('Guru tidak ditemukan');
    }

    public function test_information_search_filter_and_empty_state_work(): void
    {
        $this->seed();

        $this->get('/informasi?tipe=pengumuman')
            ->assertOk()
            ->assertSee('Jadwal Pertemuan Orang Tua Semester Ganjil')
            ->assertDontSee('Penerimaan Peserta Didik Baru 2026');

        $this->get('/informasi?q=tidak-ada-sama-sekali')
            ->assertOk()
            ->assertSee('Informasi tidak ditemukan');
    }
}
