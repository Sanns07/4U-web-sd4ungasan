<?php

namespace Tests\Feature\Student;

use App\Enums\Religion;
use App\Enums\UserRole;
use App\Models\DailyHabitJournal;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyHabitJournalTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_without_student_profile_sees_empty_state(): void
    {
        $user = User::factory()->create(['role' => UserRole::Student]);

        $this->actingAs($user)->get('/siswa/jurnal')
            ->assertOk()
            ->assertSee('Profil siswa belum terhubung');
    }

    public function test_student_sees_journal_page_with_worship_items_based_on_religion(): void
    {
        $student = Student::factory()->create(['religion' => Religion::Islam]);

        $response = $this->actingAs($student->user)->get('/siswa/jurnal');

        $response->assertOk()
            ->assertSee('Jurnal 7 Kebiasaan Anak Hebat')
            ->assertSee('Subuh')
            ->assertSee('Zuhur')
            ->assertSee('Isya')
            ->assertSee("Mengaji / Membaca Al-Qur'an");
    }

    public function test_student_can_create_and_update_journal_entry_for_today(): void
    {
        $student = Student::factory()->create(['religion' => Religion::Islam]);
        $today = now()->toDateString();

        $payload = [
            'journal_date' => $today,
            'wake_up_time' => '05:00',
            'worship_items' => ['Subuh', 'Zuhur', 'Isya'],
            'exercise_activity' => 'Lari pagi 20 menit di sekitar rumah',
            'meal_breakfast' => 'Nasi uduk telur balado',
            'meal_lunch' => 'Nasi ayam bakar sayur asem',
            'meal_dinner' => 'Nasi sup ikan',
            'learning_activity' => 'Membaca buku sains bab 3 dan latihan soal',
            'social_activity' => 'Membantu tetangga menyiram tanaman',
            'sleep_time' => '21:00',
        ];

        // 1. Create
        $response = $this->actingAs($student->user)->post('/siswa/jurnal', $payload);
        $response->assertRedirect(route('student.journal.index', ['date' => $today]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('daily_habit_journals', [
            'student_id' => $student->id,
            'journal_date' => $today,
            'exercise_activity' => 'Lari pagi 20 menit di sekitar rumah',
        ]);

        $this->assertSame(1, DailyHabitJournal::query()->where('student_id', $student->id)->count());

        // 2. Update (upsert on same date)
        $payload['exercise_activity'] = 'Bersepeda keliling komplek 30 menit';
        $payload['wake_up_time'] = '04:45';

        $response2 = $this->actingAs($student->user)->post('/siswa/jurnal', $payload);
        $response2->assertRedirect(route('student.journal.index', ['date' => $today]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('daily_habit_journals', [
            'student_id' => $student->id,
            'journal_date' => $today,
            'exercise_activity' => 'Bersepeda keliling komplek 30 menit',
        ]);

        $this->assertSame(1, DailyHabitJournal::query()->where('student_id', $student->id)->count());
    }

    public function test_student_can_fill_journal_for_past_date(): void
    {
        $student = Student::factory()->create(['religion' => Religion::Catholic]);
        $yesterday = now()->subDay()->toDateString();

        $payload = [
            'journal_date' => $yesterday,
            'wake_up_time' => '05:30',
            'worship_items' => ['Doa Pagi', 'Doa Malam'],
            'exercise_activity' => 'Senam SKJ',
            'meal_breakfast' => 'Roti bakar keju',
            'meal_lunch' => 'Nasi goreng sayur',
            'meal_dinner' => 'Nasi lauk tempe tahu',
            'learning_activity' => 'Belajar matematika perkalian',
            'social_activity' => 'Merapikan mainan bersama adik',
            'sleep_time' => '21:15',
        ];

        $response = $this->actingAs($student->user)->post('/siswa/jurnal', $payload);
        $response->assertRedirect(route('student.journal.index', ['date' => $yesterday]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('daily_habit_journals', [
            'student_id' => $student->id,
            'journal_date' => $yesterday,
        ]);
    }

    public function test_student_cannot_submit_journal_for_future_date(): void
    {
        $student = Student::factory()->create();
        $tomorrow = now()->addDay()->toDateString();

        $payload = [
            'journal_date' => $tomorrow,
            'wake_up_time' => '05:00',
            'exercise_activity' => 'Lari',
            'meal_breakfast' => 'Sarapan',
            'meal_lunch' => 'Makan siang',
            'meal_dinner' => 'Makan malam',
            'learning_activity' => 'Belajar',
            'social_activity' => 'Sosial',
            'sleep_time' => '21:00',
        ];

        $response = $this->actingAs($student->user)->post('/siswa/jurnal', $payload);
        $response->assertSessionHasErrors(['journal_date']);

        $this->assertDatabaseMissing('daily_habit_journals', [
            'student_id' => $student->id,
            'journal_date' => $tomorrow,
        ]);
    }

    public function test_student_page_never_displays_another_students_journal(): void
    {
        $studentA = Student::factory()->create();
        $studentB = Student::factory()->create();
        $today = now()->toDateString();

        DailyHabitJournal::query()->create([
            'student_id' => $studentB->id,
            'journal_date' => $today,
            'wake_up_time' => '05:15:00',
            'worship_items' => ['Doa Khusus Siswa B'],
            'exercise_activity' => 'Olahraga Rahasia Siswa B',
            'meal_breakfast' => 'Menu Rahasia B',
            'meal_lunch' => 'Siang B',
            'meal_dinner' => 'Malam B',
            'learning_activity' => 'Belajar Rahasia B',
            'social_activity' => 'Sosial B',
            'sleep_time' => '21:30:00',
        ]);

        $response = $this->actingAs($studentA->user)->get('/siswa/jurnal');
        $response->assertOk()
            ->assertDontSee('Olahraga Rahasia Siswa B')
            ->assertDontSee('Menu Rahasia B');
    }
}
