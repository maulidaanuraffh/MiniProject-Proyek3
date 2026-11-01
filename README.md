# Pantau MBG

Aplikasi web untuk pelaporan, peninjauan, tindak lanjut, dan pemantauan laporan keamanan makanan Program MBG. Dibuat sebagai Mini Project Proyek 3 (Kasus A) D3 Teknik Informatika Politeknik Negeri Bandung.

## Identitas

- **Nama:** Maulida Nur Afifah
- **NIM:** 251511017
- **Kelas:** 2A
- **Judul:** Pelaporan dan Pemantauan Keamanan Makanan MBG
- **URL Repository:** https://github.com/maulidaanuraffh/MiniProject-Proyek3

## Kebutuhan Environment

- PHP 8.5 (dengan ekstensi standar Laravel, termasuk `pdo_mysql` dan `fileinfo`)
- Composer 2.x
- MySQL 8 atau MariaDB 10.4 (dikembangkan dan diuji dengan MariaDB 10.4)
- Node.js 20 atau lebih baru dan npm
- Git
- Windows dengan XAMPP (opsional, untuk menjalankan MariaDB)

## Langkah Instalasi

1. Clone repository:
   ```bash
   git clone https://github.com/maulidaanuraffh/MiniProject-Proyek3.git mini-project-mbg
   cd mini-project-mbg
   ```

2. Salin file konfigurasi dan buat application key:
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

3. Install dependensi PHP dan JavaScript:
   ```bash
   composer install
   npm install
   npm run build
   ```

4. Buat database kosong di MariaDB atau MySQL:
   ```sql
   CREATE DATABASE mbg_laporan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

## Konfigurasi Database

Ubah bagian database di file `.env`:

```env
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mbg_laporan
DB_USERNAME=root
DB_PASSWORD=
```

Jika MySQL 8 yang dipakai, ganti `DB_CONNECTION` menjadi `mysql`.

Jangan commit file `.env`. File ini sudah masuk `.gitignore`.

## Migration dan Seeding

Jalankan migration untuk membuat tabel:

```bash
php artisan migrate
```

Isi data demo (akun, sekolah, distribusi, dan laporan dalam berbagai status):

```bash
php artisan db:seed
```

Seeder aman dijalankan berulang kali. Data yang sama tidak akan terduplikasi.

## Akun Demo

Semua akun memakai password `password`.

| Role | Email | Keterangan |
|---|---|---|
| Pelapor | `pelapor@demo.test` | Membuat laporan dan melihat laporan miliknya sendiri |
| Pemantau Komunitas | `pemantau@demo.test` | Melihat semua laporan, meninjau, dan melihat rekap |
| Koordinator Komunitas | `koordinator@demo.test` | Melihat semua laporan, meneruskan, menutup, melihat rekap, dan mengelola sekolah dan distribusi |

Registrasi publik hanya membuat akun dengan role Pelapor. Akun Pemantau dan Koordinator hanya dibuat lewat seeder.

## Cara Menjalankan Aplikasi

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000` di browser. Halaman `/` akan mengarahkan ke dashboard, dan jika belum login, ke halaman login.

## Fitur Utama

- **Autentikasi:** registrasi (khusus pelapor), login, dan logout dengan session.
- **Pelaporan:** form laporan dengan validasi di server. Pelapor memilih sekolah lalu distribusi yang sesuai.
- **Bukti pendukung:** satu file per laporan, format JPG, JPEG, PNG, atau PDF, maksimal 2 MB. File disimpan di `storage/app/private/evidence` dengan nama acak, dan hanya bisa diunduh oleh pihak yang berwenang.
- **Peninjauan dan tindak lanjut:** pemantau meninjau laporan dan dapat menandainya untuk diteruskan. Koordinator meneruskan lalu menutup laporan.
- **Daftar laporan:** filter status dan sekolah, serta pagination yang mempertahankan filter. Pelapor hanya melihat laporannya sendiri.
- **Rekap:** tiga ringkasan (total, sedang diproses, sudah diteruskan), tabel per sekolah, dan filter rentang tanggal kejadian.
- **Pengelolaan data:** koordinator menambah sekolah dan distribusi makanan.
- **Log perubahan status:** setiap perubahan status dicatat di tabel `report_status_logs` lewat Eloquent Observer.

## Alur Status Laporan

```
submitted → under_review → forwarded → closed
```

- `submitted` → `under_review`: dilakukan pemantau. Laporan dapat ditandai untuk diteruskan.
- `under_review` → `forwarded`: dilakukan koordinator, hanya untuk laporan yang sudah ditandai pemantau.
- `forwarded` → `closed`: dilakukan koordinator.

Status tidak bisa melompat. Perubahan yang melanggar alur ditolak di server.

## Business Rule

- **BR-01:** Pelapor hanya dapat mengedit laporannya saat status masih `submitted`.
- **BR-02:** Distribusi yang dipilih harus milik sekolah yang dipilih.
- **BR-03:** Transisi status harus mengikuti urutan alur dan dilakukan oleh role yang berwenang. Koordinator hanya dapat meneruskan laporan yang sudah ditandai pemantau.

Tambahan:
- Satu sekolah tidak boleh memiliki dua distribusi pada tanggal yang sama.
- Nama sekolah harus unik.

## Access Matrix

| Aktivitas | Pelapor | Pemantau | Koordinator |
|---|:---:|:---:|:---:|
| Login dan logout | ✓ | ✓ | ✓ |
| Membuat laporan | ✓ | ✗ | ✗ |
| Melihat laporan miliknya | ✓ | ✓ | ✓ |
| Melihat semua laporan | ✗ | ✓ | ✓ |
| Mengedit laporan miliknya (saat `submitted`) | ✓ | ✗ | ✗ |
| Mengunduh bukti laporan | Miliknya saja | ✓ | ✓ |
| Meninjau laporan | ✗ | ✓ | ✗ |
| Meneruskan laporan | ✗ | ✗ | ✓ |
| Menutup laporan | ✗ | ✗ | ✓ |
| Melihat rekap | ✗ | ✓ | ✓ |
| Mengelola sekolah dan distribusi | ✗ | ✗ | ✓ |

Pembatasan ini dijaga di server lewat middleware `role` dan `ReportPolicy`. Menyembunyikan tombol di tampilan tidak dianggap sebagai pembatasan.

## Pengujian

Jalankan seluruh test otomatis:

```bash
php artisan test
```

Test memakai SQLite di memori (sesuai `phpunit.xml`), sehingga tidak mengubah data di MariaDB.

Acceptance criteria AC-01 sampai AC-13 diuji lewat test feature dan uji manual sesuai dokumen spesifikasi.

## Struktur Singkat

- `app/Http/Controllers/` — controller auth, laporan, rekap, pengelolaan data, dan distribusi JSON
- `app/Services/ReportStatusService.php` — aturan transisi status (BR-03)
- `app/Policies/ReportPolicy.php` — aturan kepemilikan dan akses laporan
- `app/Observers/ReportObserver.php` — pencatatan perubahan status
- `app/Http/Middleware/EnsureRole.php` — pembatasan berdasarkan role
- `database/migrations/` dan `database/seeders/` — struktur tabel dan data demo
- `resources/views/` — tampilan Blade
- `tests/Feature/` — test otomatis
