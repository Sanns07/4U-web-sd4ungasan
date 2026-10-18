# SIM Akademik — UI Prototype v1.1

Prototype HTML/CSS/JavaScript statis untuk review UI sebelum integrasi Laravel Blade. Seluruh halaman memakai Bootstrap 5.3.3, `assets/css/simak.css`, dan JavaScript ringan tanpa SPA atau library UI tambahan.

Scope role: **Publik, Admin, Siswa**. Guru adalah data tenaga pendidik tanpa akun, login, dashboard, route privat, atau Portal Guru.

## Preview

Dari folder `SIMAK` jalankan:

```bash
python3 -m http.server 4173 -d ui-prototype
```

Kemudian buka:

- [`public/index.html`](public/index.html) — halaman publik
- [`student/jadwal.html`](student/jadwal.html) — satu halaman final siswa (`GET /siswa`)
- [`admin/index.html`](admin/index.html) — dashboard Panel Admin
- [`auth/login.html`](auth/login.html) — login Admin/Siswa

## Halaman publik

- `resources/views/layouts/public.blade.php` → [`layouts/public.html`](layouts/public.html)
- `resources/views/public/home.blade.php` → [`public/index.html`](public/index.html)
- `resources/views/public/profile.blade.php` → [`public/profil.html`](public/profil.html)
- `resources/views/public/teachers/index.blade.php` → [`public/guru.html`](public/guru.html)
- `resources/views/public/posts/index.blade.php` → [`public/informasi.html`](public/informasi.html)
- `resources/views/public/posts/show.blade.php` → [`public/artikel-detail.html`](public/artikel-detail.html)
- `resources/views/auth/login.blade.php` → [`auth/login.html`](auth/login.html)

## Portal Siswa

- `resources/views/layouts/student.blade.php` → [`layouts/student.html`](layouts/student.html)
- `resources/views/student/index.blade.php` → [`student/jadwal.html`](student/jadwal.html)

Halaman final siswa bersifat read-only dan menampilkan nama, NISN, kelas aktif, tahun ajaran/semester, jadwal Senin–Sabtu, guru, ruang, serta logout. Pilihan “Skenario demo” memperlihatkan state tanpa kelas, tanpa periode aktif, dan tanpa jadwal satu per satu.

[`student/index.html`](student/index.html) dan [`student/guru.html`](student/guru.html) ditandai **legacy v1.0**, tidak ditautkan dari navigasi final, dan tidak dikonversi menjadi Blade.

## Panel Admin

- `resources/views/layouts/admin.blade.php` → [`layouts/admin.html`](layouts/admin.html)
- Dashboard → [`admin/index.html`](admin/index.html)
- Profil sekolah → [`admin/profil-sekolah.html`](admin/profil-sekolah.html)
- Struktur organisasi → [`admin/organisasi.html`](admin/organisasi.html)
- Berita & pengumuman + rich-text/multi-gambar → [`admin/konten.html`](admin/konten.html)
- Guru → [`admin/guru.html`](admin/guru.html)
- Siswa & akun → [`admin/siswa.html`](admin/siswa.html)
- Periode akademik → [`admin/periode.html`](admin/periode.html)
- Kelas & penempatan → [`admin/kelas.html`](admin/kelas.html)
- Mata pelajaran → [`admin/mapel.html`](admin/mapel.html)
- Penugasan guru–mapel–kelas → [`admin/penugasan.html`](admin/penugasan.html)
- Jadwal → [`admin/jadwal.html`](admin/jadwal.html)
- Katalog empty/error/validation state → [`admin/states.html`](admin/states.html)

Setiap file mencantumkan saran view Blade dan route di komentar HTML. Form memakai label, `name`, validasi, multipart untuk upload, confirmation modal, filter, pagination, dan simulasi toast. `states.html` memisahkan state alternatif agar tidak tampil bersamaan dengan data normal.

## Sistem visual dan aset

- Hijau tua, emas, dan krem; heading serif; label kapital kecil; garis tipis dan ruang lega mengikuti UI Reference.
- Layout responsif untuk 360 px, tablet, dan desktop. Tabel admin berubah menjadi card berlabel pada mobile.
- [`assets/css/simak.css`](assets/css/simak.css) — token dan komponen publik, siswa, admin.
- [`assets/js/public.js`](assets/js/public.js) — utilitas publik/login dan pemilih state siswa.
- [`assets/js/admin.js`](assets/js/admin.js) — shell admin, form/filter simulation, preview upload, editor, state, dan toast.
- Media prototype memakai placeholder CSS lokal agar tidak bergantung pada layanan gambar eksternal.

## Catatan integrasi Blade

- Lindungi `/admin` dengan middleware auth + role admin dan `/siswa` dengan auth + role siswa.
- Data siswa selalu diambil dari akun yang login, bukan ID bebas dari URL.
- Login hanya mengarah ke `/admin` atau `/siswa`.
- Render `posts.body_html` hanya sesudah sanitasi allowlist server-side.
- Alt text cover/inline wajib, caption inline opsional, maksimal 10 gambar inline.
- Tanggal contoh memakai zona waktu `Asia/Makassar`.
