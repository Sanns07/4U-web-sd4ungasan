<?php

namespace Tests\Feature\Admin;

use App\Enums\Religion;
use App\Models\DailyHabitJournal;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyHabitJournalRecapTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_journal_recap_and_student_is_forbidden(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create();

        // Guest redirects to login
        $this->get('/admin/jurnal')->assertRedirect('/login');
        $this->get('/admin/jurnal/export')->assertRedirect('/login');

        // Student is forbidden (403)
        $this->actingAs($student->user)->get('/admin/jurnal')->assertForbidden();
        $this->actingAs($student->user)->get('/admin/jurnal/export')->assertForbidden();

        // Admin is authorized
        $this->actingAs($admin)->get('/admin/jurnal')
            ->assertOk()
            ->assertSee('Rekap Jurnal 7 Kebiasaan');
    }

    public function test_admin_can_filter_journals_by_student_name_and_month(): void
    {
        $admin = User::factory()->admin()->create();

        $studentA = Student::factory()->create(['name' => 'Ahmad Fajar', 'religion' => Religion::Islam]);
        $studentB = Student::factory()->create(['name' => 'Budi Santoso', 'religion' => Religion::Christian]);

        DailyHabitJournal::query()->create([
            'student_id' => $studentA->id,
            'journal_date' => '2026-09-01',
            'wake_up_time' => '04:45:00',
            'worship_items' => ['Subuh'],
            'exercise_activity' => 'Jogging Ahmad',
            'meal_breakfast' => 'Bubur Ayam',
            'meal_lunch' => 'Soto',
            'meal_dinner' => 'Nasi Telur',
            'learning_activity' => 'Koding PHP',
            'social_activity' => 'Ronda',
            'sleep_time' => '21:00:00',
        ]);

        DailyHabitJournal::query()->create([
            'student_id' => $studentB->id,
            'journal_date' => '2026-09-02',
            'wake_up_time' => '05:30:00',
            'worship_items' => ['Doa Pagi'],
            'exercise_activity' => 'Badminton Budi',
            'meal_breakfast' => 'Roti',
            'meal_lunch' => 'Bakso',
            'meal_dinner' => 'Capcay',
            'learning_activity' => 'Belajar Fisika',
            'social_activity' => 'Piknik',
            'sleep_time' => '22:00:00',
        ]);

        // Filter for Ahmad
        $response = $this->actingAs($admin)->get('/admin/jurnal?q=Ahmad&month=2026-09');
        $response->assertOk()
            ->assertSee('Ahmad Fajar')
            ->assertSee('Jogging Ahmad')
            ->assertDontSee('Badminton Budi');
    }

    public function test_admin_can_export_journals_to_csv(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create(['name' => 'Citra Lestari', 'religion' => Religion::Islam]);

        DailyHabitJournal::query()->create([
            'student_id' => $student->id,
            'journal_date' => '2026-09-05',
            'wake_up_time' => '05:00:00',
            'worship_items' => ['Subuh', 'Magrib'],
            'exercise_activity' => 'Senam Pagi Citra',
            'meal_breakfast' => 'Lontong Sayur',
            'meal_lunch' => 'Nasi Padang',
            'meal_dinner' => 'Buah Segar',
            'learning_activity' => 'Menulis Esai',
            'social_activity' => 'Kerja Bakti',
            'sleep_time' => '21:30:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/jurnal/export?q=Citra');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Citra Lestari', $content);
        $this->assertStringContainsString('Senam Pagi Citra', $content);
        $this->assertStringContainsString('1. Bangun Pagi', $content);
    }
}
