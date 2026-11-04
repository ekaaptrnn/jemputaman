# JemputAman — Sistem Serah Terima Anak Antar-Jemput

JemputAman adalah aplikasi web pembelajaran berbasis Laravel 13 untuk mencatat jadwal, pihak yang berwenang, alur status perjalanan, dan konfirmasi serah terima antar-jemput anak sekolah dengan kode verifikasi simulatif. Aplikasi ini menggunakan data fiktif (dummy) tanpa data pribadi nyata maupun pelacakan GPS langsung.

---

## 1. Teknologi yang Digunakan

- **Framework:** Laravel 13 (PHP 8.2+)
- **Database:** MySQL (XAMPP / Laragon) atau SQLite
- **Frontend:** Blade Templating, Tailwind CSS
- **Autentikasi:** Autentikasi Manual Laravel (tanpa Breeze/Jetstream) dengan peran berbasis Middleware (`EnsureRole`)
- **Testing:** PHPUnit Feature Test (`php artisan test`)

---

## 2. Cara Instalasi dari Nol

Ikuti langkah-langkah berikut untuk menjalankan proyek di komputer lokal:

### 2.1 Clone Repositori dan Pasang Dependensi
```bash
git clone <url-repositori-github>
cd jemputaman
composer install
```

### 2.2 Konfigurasi Environment
Salin template konfigurasi `.env.example` ke `.env`:
- **Windows (PowerShell / CMD):**
  ```powershell
  copy .env.example .env
  ```
- **Linux / macOS:**
  ```bash
  cp .env.example .env
  ```

Generate application key:
```bash
php artisan key:generate
```

### 2.3 Siapkan Database SQLite
Buat file `database.sqlite` di dalam folder `database/`:
- **Windows (PowerShell):**
  ```powershell
  New-Item -ItemType File -Path database/database.sqlite
  ```
- **Windows (CMD):**
  ```cmd
  type nul > database\database.sqlite
  ```
- **Linux / macOS:**
  ```bash
  touch database/database.sqlite
  ```

### 2.4 Jalankan Migrasi dan Seeder
Eksekusi migrasi tabel dan isi data awal dummy:
```bash
php artisan migrate:fresh --seed
```

### 2.5 Jalankan Server Lokal
```bash
php artisan serve
```
Akses aplikasi melalui peramban web di: `http://127.0.0.1:8000`

---

## 3. Cara Beralih ke Database MySQL

Bila ingin menggunakan MySQL (misalnya melalui Laragon atau XAMPP):
1. Buat database baru di MySQL, misalnya bernama `jemputaman`.
2. Sesuaikan konfigurasi di file `.env`:
   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=jemputaman
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. Jalankan migrasi dan seeder ulang:
   ```bash
   php artisan migrate:fresh --seed
   ```

---

## 4. Tabel Akun Dummy (Seeder)

Seluruh akun dummy menggunakan password seragam: **`password`**

| No | Peran (Role) | Nama | Email | Deskripsi Akses |
|---|---|---|---|---|
| 1 | `admin` | Admin JemputAman | `admin@jemputaman.test` | Akses penuh CRUD dan seluruh data |
| 2 | `orang_tua` | Budi Santoso | `ortu1@jemputaman.test` | Memiliki 2 anak (Kenzo Pratama, Alina Putri) |
| 3 | `orang_tua` | Siti Rahma | `ortu2@jemputaman.test` | Memiliki 2 anak (Rizky Ramadhan, Zahra Aulia) |
| 4 | `pengemudi` | Pak Joko Widodo (Driver 1) | `driver1@jemputaman.test` | Mobil Avanza (B 1234 ABC), jadwal anak ortu1 |
| 5 | `pengemudi` | Pak Bambang (Driver 2) | `driver2@jemputaman.test` | Motor Vario (B 5678 XYZ), jadwal anak ortu2 |
| 6 | `penerima` | Nenek Maryam | `penerima1@jemputaman.test` | Penerima terdaftar untuk anak ortu1 |
| 7 | `penerima` | Paman Dani | `penerima2@jemputaman.test` | Penerima terdaftar untuk anak ortu2 |

---

## 5. Cara Menjalankan Pengujian (Feature Test)

Pengujian otomatis menggunakan database SQLite in-memory (`:memory:`) sehingga tidak mempengaruhi database utama.
Jalankan perintah:
```bash
php artisan test
```

Pengujian mencakup:
- **`AuthTest`:** Pengujian login semua akun peran, penolakan password salah, proteksi tamu, dan proses logout.
- **`RoleMiddlewareTest`:** Pengujian middleware `role:admin` (status 200 untuk admin, 403 untuk selain admin, redirect login untuk tamu).
- **`ScopeVisibleToTest`:** Pembatasan visibilitas data query Eloquent menurut peran masing-masing.

---

## 6. Aturan Penamaan Tabel (Bentuk Tunggal)

Kamus data JemputAman menggunakan penamaan tabel tunggal. Setiap model di `app/Models/` wajib mendefinisikan property `protected $table`:

| Model | Nama Tabel di Database |
|---|---|
| `User` | `users` |
| `Sekolah` | `sekolah` |
| `Anak` | `anak` |
| `Pengemudi` | `pengemudi` |
| `PihakBerwenang` | `pihak_berwenang` |
| `Jadwal` | `jadwal` |
| `Penjemputan` | `penjemputan` |
| `RiwayatStatus` | `riwayat_status` |
| `SerahTerima` | `serah_terima` |
| `Notifikasi` | `notifikasi` |

Untuk menyaring data menurut peran user yang sedang login, gunakan scope `visibleTo()`:
```php
$dataAnak = Anak::visibleTo(auth()->user())->get();
$dataPenjemputan = Penjemputan::visibleTo(auth()->user())->get();
```

Untuk membatasi route berdasarkan peran:
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    // route khusus admin
});
```

---

## 7. Alur Cabang Git (Workflow Tim)

- **`main`**: Cabang produksi stabil.
- **`develop`**: Cabang integrasi utama tim. Semua fitur digabungkan ke cabang ini.
- **`feature/<nama-fitur>`**: Cabang pengerjaan fitur per anggota (contoh: `feature/migration-model`, `feature/auth-role`).
- **`docs/<nama-dokumen>`**: Cabang pengerjaan dokumentasi (contoh: `docs/readme-instalasi`).
