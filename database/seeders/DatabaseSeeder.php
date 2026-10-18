<?php

namespace Database\Seeders;

use App\Enums\DayOfWeek;
use App\Enums\EnrollmentStatus;
use App\Enums\Gender;
use App\Enums\OrganizationCategory;
use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Enums\Religion;
use App\Enums\Semester;
use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\AcademicPeriod;
use App\Models\DailyHabitJournal;
use App\Models\OrganizationMember;
use App\Models\Post;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\SchoolProfile;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['username' => 'admin.simak'],
            [
                'email' => 'admin@simak.local',
                'password' => Hash::make('AdminDemo!2026'),
                'role' => UserRole::Admin,
                'is_active' => true,
            ],
        );

        $studentUser = User::query()->updateOrCreate(
            ['username' => '0087432195'],
            [
                'email' => 'alya@simak.local',
                'password' => Hash::make('SiswaDemo!2026'),
                'role' => UserRole::Student,
                'is_active' => true,
            ],
        );

        User::query()->updateOrCreate(
            ['username' => 'siswa.nonaktif'],
            [
                'email' => 'nonaktif@simak.local',
                'password' => Hash::make('SiswaDemo!2026'),
                'role' => UserRole::Student,
                'is_active' => false,
            ],
        );

        $student = Student::query()->updateOrCreate(
            ['nisn' => '0087432195'],
            [
                'user_id' => $studentUser->id,
                'nis' => '260017',
                'name' => 'Alya Safitri',
                'gender' => Gender::Female,
                'religion' => Religion::Islam,
                'birth_place' => 'Denpasar',
                'birth_date' => '2010-03-17',
                'address' => 'Denpasar, Bali',
                'parent_name' => 'Dewi Lestari',
                'parent_phone' => '081234567890',
                'status' => StudentStatus::Active,
            ],
        );

        $period = AcademicPeriod::query()->updateOrCreate(
            ['academic_year' => '2026/2027', 'semester' => Semester::Odd],
            [
                'start_date' => '2026-07-13',
                'end_date' => '2026-12-19',
                'is_active' => true,
            ],
        );

        $teacherData = [
            ['198507172010012001', 'Ratna Dewi, M.Pd.', Gender::Female, 'Guru Matematika'],
            ['198209102009011002', 'Sinta Maharani, S.Pd.', Gender::Female, 'Guru Bahasa Indonesia'],
            ['198711232011011003', 'Agus Santosa, M.Pd.', Gender::Male, 'Guru Fisika'],
            ['199002142014012004', 'Ayu Lestari, S.Pd.', Gender::Female, 'Guru Biologi'],
            ['198405192008011005', 'Wayan Arta, S.Pd.', Gender::Male, 'Guru Kimia'],
            ['199104062015012006', 'Eka Pratama, S.S.', Gender::Male, 'Guru Bahasa Inggris'],
        ];

        $teachers = collect($teacherData)->map(function (array $data): Teacher {
            return Teacher::query()->updateOrCreate(
                ['nip' => $data[0]],
                [
                    'name' => $data[1],
                    'gender' => $data[2],
                    'public_title' => $data[3],
                    'is_public' => true,
                    'is_active' => true,
                ],
            );
        });

        $class = SchoolClass::query()->updateOrCreate(
            ['academic_period_id' => $period->id, 'name' => 'XI IPA 1'],
            [
                'grade_level' => 11,
                'room' => 'Ruang 11',
                'homeroom_teacher_id' => $teachers[0]->id,
                'is_active' => true,
            ],
        );

        StudentEnrollment::query()->updateOrCreate(
            ['student_id' => $student->id, 'class_id' => $class->id],
            [
                'enrolled_at' => $period->start_date,
                'ended_at' => null,
                'status' => EnrollmentStatus::Active,
            ],
        );

        SchoolProfile::query()->updateOrCreate(
            ['id' => 1],
            [
                'school_name' => 'SD No. 4 Ungasan',
                'address' => 'Jl. Uluwatu, Ungasan, Kuta Selatan, Badung, Bali',
                'phone' => '(0361) 701 234',
                'email' => 'info@sd4ungasan.sch.id',
                'vision' => 'Menjadi sekolah dasar yang unggul, berkarakter, dan berbudaya.',
                'mission' => "Menumbuhkan pembelajaran bermakna dan menyenangkan.\nMenguatkan karakter 7 Kebiasaan Anak Hebat.\nMengembangkan potensi dan kreativitas setiap siswa.",
                'updated_by' => $admin->id,
            ],
        );

        OrganizationMember::query()->updateOrCreate(
            ['teacher_id' => $teachers[0]->id, 'position_name' => 'Kepala Sekolah'],
            [
                'category' => OrganizationCategory::Principal,
                'display_order' => 1,
                'is_active' => true,
            ],
        );

        $subjectData = [
            ['MAT-W', 'Matematika Wajib'],
            ['BIN', 'Bahasa Indonesia'],
            ['FIS', 'Fisika'],
            ['BIO', 'Biologi'],
            ['KIM', 'Kimia'],
            ['BIG', 'Bahasa Inggris'],
        ];

        $subjects = collect($subjectData)->map(fn (array $data): Subject => Subject::query()->updateOrCreate(
            ['code' => $data[0]],
            ['name' => $data[1], 'is_active' => true],
        ));

        $days = DayOfWeek::cases();

        foreach ($subjects as $index => $subject) {
            $assignment = TeachingAssignment::query()->updateOrCreate(
                [
                    'teacher_id' => $teachers[$index]->id,
                    'subject_id' => $subject->id,
                    'class_id' => $class->id,
                ],
                ['is_active' => true],
            );

            Schedule::query()->updateOrCreate(
                [
                    'teaching_assignment_id' => $assignment->id,
                    'day_of_week' => $days[$index],
                    'start_time' => '07:30:00',
                ],
                [
                    'end_time' => '08:50:00',
                    'room' => $index === 2 ? 'Lab. Fisika' : ($index === 3 ? 'Lab. Biologi' : $class->room),
                    'notes' => 'Data demonstrasi lokal',
                ],
            );
        }

        Post::query()->updateOrCreate(
            ['slug' => 'penerimaan-peserta-didik-baru-2026'],
            [
                'author_user_id' => $admin->id,
                'type' => PostType::News,
                'title' => 'Penerimaan Peserta Didik Baru 2026',
                'excerpt' => 'Informasi awal penerimaan peserta didik baru dan rangkaian pengenalan lingkungan sekolah dasar.',
                'body_html' => '<p>SD No. 4 Ungasan membuka rangkaian penerimaan peserta didik baru tahun 2026.</p><h2>Informasi pendaftaran</h2><p>Calon siswa dan orang tua dapat mengikuti pembaruan resmi melalui halaman informasi sekolah.</p>',
                'cover_alt_text' => 'Kegiatan siswa SD No. 4 Ungasan',
                'status' => PostStatus::Published,
                'published_at' => '2026-08-28 08:00:00',
            ],
        );

        Post::query()->updateOrCreate(
            ['slug' => 'jadwal-pertemuan-orang-tua-semester-ganjil'],
            [
                'author_user_id' => $admin->id,
                'type' => PostType::Announcement,
                'title' => 'Jadwal Pertemuan Orang Tua Semester Ganjil',
                'excerpt' => 'Pertemuan orang tua dan wali siswa dilaksanakan untuk menyelaraskan program belajar semester ganjil.',
                'body_html' => '<p>Pertemuan orang tua dan wali siswa akan dilaksanakan di aula SD No. 4 Ungasan.</p><p>Detail waktu per tingkat akan disampaikan melalui wali kelas masing-masing.</p>',
                'cover_alt_text' => 'Aula pertemuan SD No. 4 Ungasan',
                'status' => PostStatus::Published,
                'published_at' => '2026-08-30 09:00:00',
            ],
        );

        Post::query()->updateOrCreate(
            ['slug' => 'catatan-internal-program-sekolah'],
            [
                'author_user_id' => $admin->id,
                'type' => PostType::News,
                'title' => 'Catatan Internal Program Sekolah',
                'excerpt' => 'Konten draft untuk pengujian akses publik.',
                'body_html' => '<p>Konten ini masih dalam proses penyuntingan.</p>',
                'status' => PostStatus::Draft,
                'published_at' => null,
            ],
        );

        DailyHabitJournal::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'journal_date' => now()->toDateString(),
            ],
            [
                'wake_up_time' => '05:00:00',
                'worship_items' => ['Subuh', 'Zuhur', 'Asar', 'Magrib', 'Isya', "Mengaji / Membaca Al-Qur'an"],
                'exercise_activity' => 'Senam pagi 15 menit dan peregangan',
                'meal_breakfast' => 'Nasi uduk, telur dadar, susu hangat',
                'meal_lunch' => 'Nasi putih, ayam bakar, sayur asem, air putih',
                'meal_dinner' => 'Nasi putih, tumis buncis tahu, ikan nila bakar',
                'learning_activity' => 'Mengerjakan tugas matematika bab trigonometri dan membaca novel 20 menit',
                'social_activity' => 'Membantu ibu membersihkan meja makan dan menyapa tetangga',
                'sleep_time' => '21:15:00',
            ],
        );
    }
}
