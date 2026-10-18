# 4U - SD No. 4 Ungasan

Aplikasi monolith Laravel untuk administrasi akademik **SD No. 4 Ungasan** (4U) yang dilengkapi dengan halaman landing page serta fitur-fitur administrasi yang dirancang khusus sesuai kebutuhan sekolah dasar. Aplikasi ini menggunakan Blade, Bootstrap 5.3, MySQL, dan autentikasi session, tanpa SPA, REST API terpisah, atau akun Guru.

Role aplikasi hanya Publik, Admin, dan Siswa. Guru adalah data tenaga pendidik yang dikelola Admin: tidak memiliki `user_id`, akun login, route privat, atau Portal Guru. Orang tua juga bukan role aplikasi; pengisian Jurnal 7 Kebiasaan Anak Hebat dilakukan lewat akun Siswa yang sama.

## Fitur tersedia

- Halaman publik dinamis: Beranda, Profil Sekolah dan Struktur Organisasi, Direktori Guru, Informasi, serta detail informasi.
- Login Admin/Siswa berbasis username, CSRF, regenerasi session, pembatasan percobaan login, status akun aktif, dan logout.
- Portal Siswa: halaman ringkas read-only (identitas, alamat, kelas/periode aktif, wali kelas, guru mapel, jadwal Senin-Sabtu miliknya sendiri beserta empty state) **dan** modul Jurnal 7 Kebiasaan Anak Hebat: kalender + form harian (bangun pagi, ibadah, olahraga, makan, belajar, bermasyarakat, tidur) yang dapat diisi/diubah siswa untuk tanggal apa saja (termasuk lampau, tidak untuk tanggal mendatang), dengan checklist ibadah yang menyesuaikan otomatis field `religion` siswa.
- Admin: profil sekolah, struktur organisasi, guru, siswa dan akun siswa (termasuk field `religion`), periode akademik, kelas/riwayat penempatan siswa, mata pelajaran, penugasan guru-mapel-kelas, jadwal, serta dashboard rekap Jurnal 7 Kebiasaan dengan filter bulan/kelas/nama dan export Excel/CSV (read-only, admin tidak mengubah isi jurnal siswa).
- Validasi transaksi untuk satu periode aktif, satu kelas aktif per siswa/periode, pemindahan/riwayat enrollment, assignment duplikat, serta bentrok kelas, guru, dan ruang pada jadwal.
- Berita dan pengumuman: draft/terbit/arsip, waktu terbit terjadwal, preview, cover, rich text tersanitasi, dan maksimal 10 gambar inline.
- Validasi upload JPEG/JPG, PNG, atau WebP maksimal 2 MB per file; alt text wajib, caption opsional, dan pembersihan file yatim.

## URL aplikasi

| Area | URL |
|---|---|
| Beranda | `/` |
| Profil Sekolah | `/profil` |
| Direktori Guru | `/guru` |
| Informasi | `/informasi` |
| Detail informasi | `/informasi/{slug}` |
| Masuk Portal | `/login` |
| Portal Siswa (ringkas) | `/siswa` |
| Jurnal 7 Kebiasaan (Siswa) | `/siswa/jurnal`, `/siswa/jurnal/{tanggal}` |
| Dashboard Admin | `/admin` |
| Profil/Organisasi | `/admin/profil-sekolah`, `/admin/organisasi` |
| Guru/Siswa | `/admin/guru`, `/admin/siswa` |
| Periode/Kelas/Mapel | `/admin/periode`, `/admin/kelas`, `/admin/mapel` |
| Penugasan/Jadwal | `/admin/penugasan`, `/admin/jadwal` |
| Konten informasi | `/admin/posts` |
| Rekap Jurnal (Admin) | `/admin/jurnal`, `/admin/jurnal/export` |
| Health check | `/up` |

Tidak ada route privat `/guru/*`, registrasi publik, Portal Guru, akun/role orang tua terpisah, REST API khusus, atau SPA.

## Kebutuhan lokal

- Docker Engine 29+ dan Docker Compose v2 untuk bootstrap baru dengan Compose.
- Port host `8080` untuk web dan `3307` untuk MySQL.
- Alternatif non-Docker: PHP **8.3+** (image proyek memakai 8.4), Composer 2, Node.js 22, dan MySQL 8.4/MariaDB yang kompatibel.
- Ekstensi PHP: `pdo_mysql`, `mbstring`, `dom`, `fileinfo`, dan OpenSSL.

Host saat ini dapat menjalankan container 4U (SD No. 4 Ungasan) yang sudah ada, tetapi belum menyediakan `docker compose`/`docker-compose` maupun PHP host. Compose tidak perlu dipasang untuk memakai container yang sudah hidup.

## Menjalankan dari clone baru dengan Compose v2

```bash
cp .env.example .env
docker compose build app
docker compose run --rm app composer install
docker compose run --rm app php artisan key:generate
docker compose run --rm node npm ci
docker compose run --rm node npm run build
docker compose up -d mysql app nginx
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

Buka `http://localhost:8080`, lalu cek `http://localhost:8080/up`.

Untuk menghentikan aplikasi tanpa menghapus database:

```bash
docker compose down
```

`docker compose down -v` menghapus volume MySQL dan hanya boleh dipakai jika memang ingin membuang seluruh data lokal.

## Alternatif jika Compose v2 belum tersedia

Alternatif ini hanya untuk container project yang sudah dibuat dan sehat, bukan pengganti bootstrap clone baru:

```bash
docker ps -a --filter label=com.docker.compose.project=simak --format '{{.Names}}'
docker exec simak-app-1 php artisan migrate --seed
docker exec simak-app-1 php artisan storage:link
docker exec simak-app-1 php artisan test
docker exec simak-app-1 vendor/bin/pint --test
```

Untuk memasang/build dependency frontend sebagai user workspace tanpa Compose:

```bash
docker run --rm --user "$(id -u):$(id -g)" -v "$PWD:/var/www/html" -w /var/www/html node:22-alpine npm ci
docker run --rm --user "$(id -u):$(id -g)" -v "$PWD:/var/www/html" -w /var/www/html node:22-alpine npm run build
```

## Akun demo

| Role | Username | Password | Tujuan login |
|---|---|---|---|
| Admin | `admin.simak` | `AdminDemo!2026` | `/admin` |
| Siswa | `0087432195` | `SiswaDemo!2026` | `/siswa`, `/siswa/jurnal` |
| Uji akun nonaktif | `siswa.nonaktif` | `SiswaDemo!2026` | Ditolak saat login |

Seed data siswa demo mencakup field `religion` terisi (mis. `islam`, `hindu`) beserta beberapa entri contoh `daily_habit_journals` pada tanggal berbeda, agar kalender dan dashboard rekap admin langsung terlihat berisi.

Kredensial ini hanya untuk lokal/demo dan wajib diganti sebelum penggunaan non-lokal.

## Perintah pengembangan dan verifikasi

```bash
# Semua test (menggunakan SQLite in-memory dari phpunit.xml, tidak menghapus MySQL development)
php artisan test

# Format/check PHP
vendor/bin/pint --test

# Build asset produksi
npm run build

# Audit route, migrasi, dan Blade
php artisan migrate:status
php artisan route:list --except-vendor
php artisan view:cache
```

Dalam container aktif, awali perintah PHP di atas dengan `docker exec simak-app-1`. Seeder bersifat idempotent. `php artisan migrate:fresh --seed` bersifat destruktif untuk database target dan bukan perintah operasi normal.

## Upload dan storage

Jalankan `php artisan storage:link` setelah setup. File publik berada di `storage/app/public` dengan path:

- `school-logos/`
- `organization-members/`
- `teachers/`
- `posts/covers/`
- `posts/inline/`

Semua upload gambar dibatasi JPEG/JPG, PNG, atau WebP maksimal 2 MB. Artikel memerlukan satu cover dan alt text cover; maksimal 10 gambar inline aktif dengan alt text wajib serta caption opsional. HTML artikel dibersihkan di server sebelum disimpan dan file yang tidak lagi direferensikan dihapus setelah transaksi berhasil.

Jurnal 7 Kebiasaan tidak melibatkan upload file (seluruh input berupa teks, time picker, dan checkbox), sehingga tidak menambah direktori storage baru.

Simpan dan backup `storage/app/public`; `public/storage` hanya symlink dan tidak perlu dibackup terpisah.

## Aturan integritas dan keamanan

- Hanya satu periode akademik dapat aktif.
- Siswa hanya memiliki satu kelas aktif dalam satu periode; penempatan/pemindahan memakai transaksi dan riwayat tetap tersimpan.
- Periode yang telah direferensikan tidak dapat mengubah identitas/rentang tanggal yang dapat merusak histori.
- Assignment guru-mapel-kelas duplikat ditolak. Jadwal wajib selesai setelah mulai dan tidak boleh konflik kelas, guru, atau ruang di periode yang sama.
- Artikel publik hanya berstatus `published` dengan `published_at` yang sudah berlaku. Draft dan arsip tidak dapat dibuka dari publik.
- Kombinasi `student_id + journal_date` pada `daily_habit_journals` wajib unik; menyimpan ulang pada tanggal yang sama melakukan *upsert*, bukan menambah record baru. Tanggal jurnal tidak boleh lebih besar dari hari ini.
- Opsi checklist ibadah pada form jurnal mengikuti `students.religion`; siswa tanpa `religion` terisi diarahkan untuk meminta admin melengkapi data.
- Semua mutasi memakai Form Request, CSRF, mass-assignment allowlist, dan transaksi lintas tabel bila diperlukan.
- Admin ditolak dari Portal Siswa; Siswa ditolak dari seluruh Admin dan hanya melihat/mengubah data dirinya sendiri, termasuk jurnal miliknya. Admin hanya berhak membaca dan mengekspor rekap jurnal, tidak mengubah isinya.
- Password selalu di-hash, tidak ada registrasi publik, dan `.env` tidak dilacak Git.

## Struktur proyek

- `app/Enums`, `app/Models`, `app/Services`: domain, relasi, dan aturan bisnis (termasuk `DailyHabitJournal` model dan `DailyHabitJournalService`).
- `app/Http/Controllers`, `app/Http/Requests`: route server-rendered, validasi, dan otorisasi (termasuk `Student\DailyHabitJournalController` dan `Admin\DailyHabitJournalController`).
- `database/migrations`, `database/seeders`: skema dan data demo (termasuk migrasi `religion` pada `students` dan tabel `daily_habit_journals`).
- `resources/views`, `resources/css/simak.css`, `resources/js/app.js`: Blade dan visual runtime (termasuk `resources/views/student/journal/` dan `resources/views/admin/journal/`).
- `ui-prototype/`: prototype final yang menjadi acuan desain, bukan runtime Laravel.
- `compose.yaml`, `docker/`: PHP-FPM, Nginx, MySQL, dan tooling Node.

## Deployment (menunggu server)

Deployment belum dilakukan karena server produksi belum tersedia. Saat server siap, gunakan web root `public/`, HTTPS, PHP 8.3+, Composer 2, MySQL yang kompatibel, serta direktori `storage` dan `bootstrap/cache` yang writable. Atur `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` produksi, kredensial database/session unik, dan ganti seluruh password demo.

Runbook ringkas:

1. Backup database dan `storage/app/public` terlebih dahulu.
2. Jalankan `composer install --no-dev --optimize-autoloader`.
3. Build asset dengan `npm ci && npm run build` (Node hanya diperlukan saat build).
4. Jalankan `php artisan migrate --force`, `php artisan storage:link`, lalu `php artisan optimize`.
5. Smoke test `/up`, seluruh halaman publik, login Admin/Siswa, upload gambar, serta isi/lihat Jurnal 7 Kebiasaan dan rekap+export admin.

Jangan menjalankan `migrate:fresh` atau seed akun demo di production kecuali instance demo yang memang terisolasi.

## Backup dan pemulihan

Backup harus mencakup dump MySQL **dan** `storage/app/public`, disimpan di luar repository. Contoh Compose:

```bash
docker compose exec -T mysql sh -c 'exec mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --single-transaction --routines --triggers "$MYSQL_DATABASE"' > /path/di-luar-repo/simak.sql
tar -czf /path/di-luar-repo/simak-storage.tgz storage/app/public
```

Untuk pemulihan, import dump ke database yang benar, pulihkan arsip storage, jalankan ulang `php artisan storage:link`, lalu smoke test aplikasi:

```bash
docker compose exec -T mysql sh -c 'exec mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' < /path/di-luar-repo/simak.sql
```

Uji proses restore sebelum demo atau deployment penting.