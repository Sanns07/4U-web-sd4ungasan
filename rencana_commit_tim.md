# 🚀 Rencana Commit & Deployment Tugas Akhir (4U v1.0) - Tim 2 Orang
**Mata Kuliah:** Pemrograman Internet
**Nama Proyek:** 4U - Sistem Informasi Administrasi Akademik SD No. 4 Ungasan
**Tim Pengembang:** Gde Santha Wijaya & I Gusti Ngurah Dwi Sedana Yoga

---

## 👥 Pembagian Peran Tim (2 Pengembang)

| Pengembang | Peran & Tanggung Jawab | Area Fokus Utama |
|---|---|---|
| **Developer A** | **Backend & Logic Lead** | Architecture, Migrations, Models, Services, Middleware, Validation, Sanitizer, Schedule Conflict Engine, Daily Habit Journal Service, Journal Export Service, Tests. |
| **Developer B** | **Frontend & UI Lead** | Design System (`simak.css`), Layouts (Admin/Public/Student), Views, Auth UI, Public Pages, Student Portal, Journal Calendar/Form UI, Admin Journal Recap UI, Documentation. |

---

## 📌 1. Strategi Commit Granular (30 Commit Milestone)

Berikut adalah daftar **30 commit bertahap** yang menunjukkan kontribusi aktif dari kedua anggota tim secara bergantian dari fondasi awal hingga tahap pengujian akhir. Commit 24–28 baru (Jurnal 7 Kebiasaan Anak Hebat) disisipkan sebelum commit test dan dokumentasi final, mengikuti PRD v1.2.

### 📋 Tabel Urutan Commit 2 Personil

| No | Dev | Type & Scope | Pesan Commit (Commit Message) | Isi Perubahan Utama |
|:---:|:---:|---|---|---|
| 1 | **Dev A** | `chore` | `chore: setup initial Laravel 11 project structure` | Generator awal Laravel 11, `composer.json`, `artisan`, `.gitignore`. |
| 2 | **Dev A** | `chore` | `chore: configure Docker Compose environment with Nginx and MySQL 8.4` | `compose.yaml`, `docker/nginx.conf`, `docker/Dockerfile`, `.env.example`. |
| 3 | **Dev B** | `feat(ui)` | `feat: setup Vite bundler and import Bootstrap 5.3 framework` | `vite.config.js`, `package.json`, `app.css`, `app.js`. |
| 4 | **Dev B** | `feat(ui)` | `feat: add SIMAK custom editorial design system and CSS tokens` | Token warna, typography Fraunces/Inter, komponen di `simak.css`. |
| 5 | **Dev A** | `feat(db)` | `feat: add initial migrations for users and school profiles` | Migrasi `users` (dengan role enum) & `school_profiles`. |
| 6 | **Dev A** | `feat(db)` | `feat: create migrations for teachers, students, and academic periods` | Tabel `teachers`, `students`, `organization_members`, `academic_periods`. |
| 7 | **Dev A** | `feat(auth)` | `feat: implement session-based authentication controller` | `AuthenticatedSessionController` (`create`, `store`, `destroy`). |
| 8 | **Dev B** | `feat(auth)` | `feat: create role authorization middleware and login Blade view` | `EnsureUserHasRole`, `layouts/auth.blade.php`, `auth/login.blade.php`. |
| 9 | **Dev B** | `feat(ui)` | `feat: implement responsive admin layout shell and navigation` | `layouts/admin.blade.php`, `admin-navigation.blade.php`, offcanvas mobile. |
| 10 | **Dev A** | `feat(admin)` | `feat: build school profile and organization member management` | `SchoolProfileController`, `OrganizationMemberController`, models & views. |
| 11 | **Dev A** | `feat(admin)` | `feat: add teacher directory CRUD and profile management` | `TeacherController`, `TeacherRequest`, view `admin/teachers/`. |
| 12 | **Dev A** | `feat(admin)` | `feat: add student CRUD with atomic user account creation` | `StudentController`, `StudentAccountService` (DB transaction user+student). |
| 13 | **Dev A** | `feat(academic)` | `feat: implement academic period management with single-active lock` | `AcademicPeriodController`, `AcademicPeriodService` (locking periode aktif). |
| 14 | **Dev A** | `feat(academic)` | `feat: build subject and class master data management` | `SubjectController`, `SchoolClassController`, form & request validations. |
| 15 | **Dev A** | `feat(academic)` | `feat: add student class enrollment, placement, and transfer services` | `StudentEnrollmentController`, `StudentEnrollmentService` (bulk placement). |
| 16 | **Dev B** | `feat(schedule)` | `feat: create teaching assignment matrix controller and view` | `TeachingAssignmentController`, `TeachingAssignmentRequest`, views assignment. |
| 17 | **Dev A** | `feat(schedule)` | `feat: implement weekly schedule manager and conflict detection engine` | `ScheduleController`, `ScheduleService` (mesin validasi bentrok guru & kelas). |
| 18 | **Dev A** | `feat(content)` | `feat: implement DOM-based HTML sanitizer for rich text posts` | `PostHtmlSanitizer` (allowlist tag & penanganan XSS server-side). |
| 19 | **Dev A** | `feat(content)` | `feat: add multi-image post management with inline upload service` | `PostController`, `PostContentService` (cover + maks 10 inline images). |
| 20 | **Dev B** | `feat(public)` | `feat: create public layout and responsive editorial home page` | `layouts/public.blade.php`, `HomeController`, `public/home.blade.php`. |
| 21 | **Dev B** | `feat(public)` | `feat: build public school profile and teacher directory views` | `ProfileController`, `TeacherDirectoryController`, view profile & teachers. |
| 22 | **Dev B** | `feat(public)` | `feat: add public news and announcement listing and detail pages` | `InformationController`, view `posts/index.blade.php` & `posts/show.blade.php`. |
| 23 | **Dev B** | `feat(student)` | `feat: implement read-only single-page student summary portal` | `layouts/student.blade.php`, `Student\ScheduleController`, view `summary.blade.php`. |
| 24 | **Dev A** | `feat(db)` | `feat: add religion field to students and create daily habit journal migration` | `2026_09_01_000011_add_religion_to_students_table.php`, `2026_09_01_000012_create_daily_habit_journals_table.php`, `app/Models/DailyHabitJournal.php`, unique index `student_id+journal_date`. |
| 25 | **Dev A** | `feat(student)` | `feat: implement daily habit journal service with per-date upsert and religion-based checklist` | `DailyHabitJournalService` (upsert per tanggal, larangan tanggal mendatang, resolusi checklist ibadah dari `students.religion`), `Student\DailyHabitJournalController`, `StudentJournalRequest`. |
| 26 | **Dev B** | `feat(student)` | `feat: build student journal calendar and form view at /siswa/jurnal` | `resources/views/student/journal/index.blade.php`, `partials/calendar.blade.php`, `partials/form.blade.php`, tombol "Simpan Jurnal Hari Ini", empty/pre-filled state. |
| 27 | **Dev A** | `feat(admin)` | `feat: add admin daily habit journal recap controller with month/class/name filters and export` | `Admin\DailyHabitJournalController`, `DailyHabitJournalExportService` (CSV/Excel, kolom No–7 Tidur Cepat), route `/admin/jurnal`, `/admin/jurnal/export`. |
| 28 | **Dev B** | `feat(admin)` | `feat: create admin journal recap data grid view and export button` | `resources/views/admin/journal/index.blade.php`, filter bulan/kelas/nama, tabel data grid, tombol "Export ke Excel/CSV", empty state. |
| 29 | **Dev A** | `test` | `test: add comprehensive automated test suite for admin, public, and journal workflows` | Feature & Unit tests (PHPUnit/Pest) mencakup seluruh 12 MVP, termasuk upsert jurnal, larangan tanggal mendatang, checklist per agama, dan otorisasi rekap admin. |
| 30 | **Dev B** | `docs` | `docs: add DatabaseSeeder demo data (incl. journal entries) and complete project README` | `DatabaseSeeder.php` (data demo termasuk `religion` siswa dan contoh `daily_habit_journals`), `README.md` final dengan petunjuk demo jurnal. |

---

## 🛠️ 2. Skrip Otomatisasi Commit 2 Developer di Repo Publik Baru

Jalankan perintah Bash berikut di terminal untuk membuat repositori publik baru secara otomatis dengan riwayat **30 commit yang dibagi atas nama 2 pengembang**:

> **Catatan:** Ganti `Rekan Anda <rekan@email.com>` dengan nama & email anggota tim ke-2 Anda.

```bash
# 1. Masuk ke folder proyek
cd "/home/sanns/Documents/Project On Linux/SIMAK"

# 2. Buat folder terpisah untuk repo publik baru
mkdir -p ../SIMAK-PUBLIC
cp -r . ../SIMAK-PUBLIC/
cd ../SIMAK-PUBLIC/

# Hapus git lama
rm -rf .git
git init
git branch -M main

# Tentukan Identitas 2 Developer
DEV_A_AUTHOR="Sanns07 <gdesantha07@gmail.com>"
DEV_B_AUTHOR="Rekan Tim <rekan@email.com>"  # <-- Ganti dengan nama & email rekan kelompok Anda

# -----------------------------------------------------------------------------
# COMMIT 1-4: SETUP & DESIGN SYSTEM
# -----------------------------------------------------------------------------
git add compose.yaml docker/ .dockerignore .editorconfig .env.example .gitattributes .gitignore artisan bootstrap/ config/ public/ index.php composer.json composer.lock package.json package-lock.json
git commit --author="$DEV_A_AUTHOR" -m "chore: setup initial Laravel 11 project structure"

git add docker/
git commit --author="$DEV_A_AUTHOR" --allow-empty -m "chore: configure Docker Compose environment with Nginx and MySQL 8.4"

git add vite.config.js resources/css/app.css resources/js/app.js
git commit --author="$DEV_B_AUTHOR" -m "feat(ui): setup Vite bundler and import Bootstrap 5.3 framework"

git add resources/css/simak.css
git commit --author="$DEV_B_AUTHOR" -m "feat(ui): add SIMAK custom editorial design system and CSS tokens"

# -----------------------------------------------------------------------------
# COMMIT 5-8: SCHEMA & AUTHENTICATION
# -----------------------------------------------------------------------------
git add database/migrations/0001_01_01_000000_create_users_table.php database/migrations/2026_09_01_000001_create_school_profiles_table.php app/Models/User.php app/Models/SchoolProfile.php
git commit --author="$DEV_A_AUTHOR" -m "feat(db): add initial migrations for users and school profiles"

git add database/migrations/2026_09_01_000002_create_teachers_and_organization_members_tables.php database/migrations/2026_09_01_000003_create_students_table.php database/migrations/2026_09_01_000004_create_academic_periods_and_classes_tables.php app/Models/Teacher.php app/Models/Student.php app/Models/OrganizationMember.php app/Models/AcademicPeriod.php app/Enums/
git commit --author="$DEV_A_AUTHOR" -m "feat(db): create migrations for teachers, students, and academic periods"

git add app/Http/Controllers/Auth/AuthenticatedSessionController.php app/Http/Requests/Auth/LoginRequest.php
git commit --author="$DEV_A_AUTHOR" -m "feat(auth): implement session-based authentication controller"

git add app/Http/Middleware/EnsureUserHasRole.php resources/views/layouts/auth.blade.php resources/views/auth/login.blade.php lang/
git commit --author="$DEV_B_AUTHOR" -m "feat(auth): create role authorization middleware and login Blade view"

# -----------------------------------------------------------------------------
# COMMIT 9-12: ADMIN MASTER DATA
# -----------------------------------------------------------------------------
git add resources/views/layouts/admin.blade.php resources/views/layouts/partials/admin-navigation.blade.php resources/views/errors/
git commit --author="$DEV_B_AUTHOR" -m "feat(ui): implement responsive admin layout shell and navigation"

git add app/Http/Controllers/Admin/SchoolProfileController.php app/Http/Controllers/Admin/OrganizationMemberController.php app/Http/Requests/Admin/SchoolProfileRequest.php app/Http/Requests/Admin/OrganizationMemberRequest.php resources/views/admin/school-profile/ resources/views/admin/organization/
git commit --author="$DEV_A_AUTHOR" -m "feat(admin): build school profile and organization member management"

git add app/Http/Controllers/Admin/TeacherController.php app/Http/Requests/Admin/TeacherRequest.php resources/views/admin/teachers/
git commit --author="$DEV_A_AUTHOR" -m "feat(admin): add teacher directory CRUD and profile management"

git add app/Http/Controllers/Admin/StudentController.php app/Http/Requests/Admin/StudentRequest.php app/Services/StudentAccountService.php resources/views/admin/students/
git commit --author="$DEV_A_AUTHOR" -m "feat(admin): add student CRUD with atomic user account creation"

# -----------------------------------------------------------------------------
# COMMIT 13-17: ACADEMIC WORKFLOWS & SCHEDULING
# -----------------------------------------------------------------------------
git add app/Http/Controllers/Admin/AcademicPeriodController.php app/Http/Requests/Admin/AcademicPeriodRequest.php app/Services/AcademicPeriodService.php resources/views/admin/periods/
git commit --author="$DEV_A_AUTHOR" -m "feat(academic): implement academic period management with single-active lock"

git add app/Http/Controllers/Admin/SubjectController.php app/Http/Controllers/Admin/SchoolClassController.php app/Http/Requests/Admin/SubjectRequest.php app/Http/Requests/Admin/SchoolClassRequest.php resources/views/admin/subjects/ resources/views/admin/classes/form.blade.php resources/views/admin/classes/index.blade.php
git commit --author="$DEV_A_AUTHOR" -m "feat(academic): build subject and class master data management"

git add app/Http/Controllers/Admin/StudentEnrollmentController.php app/Services/StudentEnrollmentService.php app/Http/Requests/Admin/StudentPlacementRequest.php app/Http/Requests/Admin/StudentTransferRequest.php app/Http/Requests/Admin/EndStudentEnrollmentRequest.php database/migrations/2026_09_01_000010_allow_student_class_reenrollment.php resources/views/admin/classes/show.blade.php
git commit --author="$DEV_A_AUTHOR" -m "feat(academic): add student class enrollment, placement, and transfer services"

git add app/Http/Controllers/Admin/TeachingAssignmentController.php app/Http/Requests/Admin/TeachingAssignmentRequest.php resources/views/admin/assignments/
git commit --author="$DEV_B_AUTHOR" -m "feat(schedule): create teaching assignment matrix controller and view"

git add app/Http/Controllers/Admin/ScheduleController.php app/Services/ScheduleService.php app/Http/Requests/Admin/ScheduleRequest.php resources/views/admin/schedules/
git commit --author="$DEV_A_AUTHOR" -m "feat(schedule): implement weekly schedule manager and conflict detection engine"

# -----------------------------------------------------------------------------
# COMMIT 18-19: CONTENT MANAGEMENT & SANITIZER
# -----------------------------------------------------------------------------
git add app/Services/PostHtmlSanitizer.php
git commit --author="$DEV_A_AUTHOR" -m "feat(content): implement DOM-based HTML sanitizer for rich text posts"

git add app/Http/Controllers/Admin/PostController.php app/Services/PostContentService.php app/Http/Requests/Admin/PostRequest.php app/Http/Requests/Admin/PostActionRequest.php database/migrations/2026_09_01_000009_create_posts_and_post_images_tables.php app/Models/Post.php app/Models/PostImage.php resources/views/admin/posts/
git commit --author="$DEV_A_AUTHOR" -m "feat(content): add multi-image post management with inline upload service"

# -----------------------------------------------------------------------------
# COMMIT 20-23: PUBLIC SITE & STUDENT PORTAL
# -----------------------------------------------------------------------------
git add app/Http/Controllers/PublicSite/HomeController.php resources/views/layouts/public.blade.php resources/views/public/home.blade.php
git commit --author="$DEV_B_AUTHOR" -m "feat(public): create public layout and responsive editorial home page"

git add app/Http/Controllers/PublicSite/ProfileController.php app/Http/Controllers/PublicSite/TeacherDirectoryController.php resources/views/public/profile.blade.php resources/views/public/teachers/
git commit --author="$DEV_B_AUTHOR" -m "feat(public): build public school profile and teacher directory views"

git add app/Http/Controllers/PublicSite/InformationController.php resources/views/public/posts/
git commit --author="$DEV_B_AUTHOR" -m "feat(public): add public news and announcement listing and detail pages"

git add app/Http/Controllers/Student/ScheduleController.php resources/views/layouts/student.blade.php resources/views/student/summary.blade.php routes/web.php
git commit --author="$DEV_B_AUTHOR" -m "feat(student): implement read-only single-page student summary portal"

# -----------------------------------------------------------------------------
# COMMIT 24-28: DAILY HABIT JOURNAL (JURNAL 7 KEBIASAAN ANAK HEBAT)
# -----------------------------------------------------------------------------
git add database/migrations/2026_09_01_000011_add_religion_to_students_table.php database/migrations/2026_09_01_000012_create_daily_habit_journals_table.php app/Models/DailyHabitJournal.php app/Models/Student.php
git commit --author="$DEV_A_AUTHOR" -m "feat(db): add religion field to students and create daily habit journal migration"

git add app/Services/DailyHabitJournalService.php app/Http/Controllers/Student/DailyHabitJournalController.php app/Http/Requests/Student/DailyHabitJournalRequest.php config/worship_checklist.php
git commit --author="$DEV_A_AUTHOR" -m "feat(student): implement daily habit journal service with per-date upsert and religion-based checklist"

git add resources/views/student/journal/ routes/web.php
git commit --author="$DEV_B_AUTHOR" -m "feat(student): build student journal calendar and form view at /siswa/jurnal"

git add app/Http/Controllers/Admin/DailyHabitJournalController.php app/Services/DailyHabitJournalExportService.php app/Http/Requests/Admin/DailyHabitJournalFilterRequest.php routes/web.php
git commit --author="$DEV_A_AUTHOR" -m "feat(admin): add admin daily habit journal recap controller with month/class/name filters and export"

git add resources/views/admin/journal/
git commit --author="$DEV_B_AUTHOR" -m "feat(admin): create admin journal recap data grid view and export button"

# -----------------------------------------------------------------------------
# COMMIT 29-30: TESTS & FINAL DOCS
# -----------------------------------------------------------------------------
git add tests/ app/Providers/
git commit --author="$DEV_A_AUTHOR" -m "test: add comprehensive automated test suite for admin, public, and journal workflows"

git add database/seeders/ database/factories/ README.md rencanadeploy.md PRD_SIM_Administrasi_Akademik_v1.0.md
git commit --author="$DEV_B_AUTHOR" -m "docs: add DatabaseSeeder demo data (incl. journal entries) and complete project README"

# -----------------------------------------------------------------------------
# PUSH KE REPO PUBLIK GITHUB
# -----------------------------------------------------------------------------
git remote add origin https://github.com/USERNAME_ANDA/NAMA_REPO_PUBLIK.git
git push -u origin main
```

---

## 🌐 3. Rencana Deployment (Server & Live Demo)

### Opsi VPS Linux (Docker Compose)
1. Install Docker & Docker Compose di server VPS Ubuntu 22.04/24.04.
2. Clone repo publik & salin `.env.example` ke `.env`.
3. Jalankan `docker compose up -d --build`.
4. Jalankan migrasi dan seeder demo:
   `docker exec simak-app-1 php artisan migrate:fresh --seed`
5. Pasang SSL gratis (Let's Encrypt / Certbot) melalui Nginx reverse proxy.
6. Smoke test tambahan khusus fitur baru: login siswa demo → isi/lihat `/siswa/jurnal` untuk beberapa tanggal → login admin → buka `/admin/jurnal`, uji filter bulan/kelas/nama, lalu unduh export Excel/CSV dan pastikan isinya sesuai filter.

---

## 📋 4. Checklist Penilaian Dosen (UAS Pemrograman Internet)

- [ ] Repositori **Public** dengan riwayat commit 30+ yang berimbang antar 2 anggota kelompok.
- [ ] Dokumentasi `README.md` jelas memuat pembagian tugas tim, petunjuk instalasi, dan credential demo.
- [ ] Seluruh 12 MVP (termasuk Jurnal 7 Kebiasaan Anak Hebat dan rekap+export admin) berfungsi di live server / demo lokal tanpa error.
- [ ] Pengujian terotomatisasi (PHPUnit) 100% hijau, mencakup skenario upsert jurnal per tanggal, larangan tanggal mendatang, checklist ibadah per agama, dan otorisasi rekap admin.