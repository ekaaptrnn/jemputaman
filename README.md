# JemputAman — Sistem Serah Terima Anak Antar-Jemput Sekolah

**Sistem Pengelolaan Armada Resmi Sekolah Tunggal dengan Live Tracking GPS & Verifikasi QR Siswa**  
*Laravel 13 · Proyek Kelompok Praktikum Pemrograman*

---

## 1. Ikhtisar & Arsitektur Konsep

JemputAman adalah aplikasi web terpadu yang dirancang khusus untuk mengelola operasional antar-jemput anak pada **satu sekolah** (*SD JemputAman*) dengan armada dan pengemudi internal resmi sekolah.

Sistem menerapkan validasi serah terima dua arah (*two-factor confirmation*):
1. **Titik Penjemputan di Sekolah:** Pengemudi memindai **Kode QR unik pada kartu siswa** dan mengunggah **foto serah terima** di gerbang/lobi sekolah.
2. **Dalam Perjalanan:** Pergerakan kendaraan dipantau secara langsung (*Live Tracking GPS*) menggunakan **Leaflet.js & OpenStreetMap** yang memperbarui koordinat latitude/longitude secara berkala tanpa API berbayar.
3. **Titik Kedatangan di Rumah:** Pengemudi mengunggah **foto kedatangan**, dan perjalanan diselesaikan secara sah melalui **tombol konfirmasi penerimaan oleh Orang Tua** di aplikasi.

---

## 2. Teknologi yang Digunakan

- **Framework Backend:** Laravel 13 (PHP 8.2 ke atas)
- **Database:** MySQL (XAMPP / MariaDB) dengan database `jemputaman`
- **Frontend & Peta:** Blade Templating, Tailwind CSS, Leaflet.js & OpenStreetMap
- **Autentikasi & Otorisasi:** Autentikasi Manual Laravel (`Auth::attempt`), Middleware `EnsureRole`, Query Scope `visibleTo`
- **Pengujian Otomatis:** PHPUnit Feature Test (`php artisan test`)

---

## 3. Cara Instalasi & Menjalankan Proyek di Lokal

### 3.1 Clone Repositori dan Pasang Dependensi
```bash
git clone https://github.com/ekaaptrnn/jemputaman.git
cd jemputaman
composer install
```

### 3.2 Konfigurasi Environment (`.env`)
Salin konfigurasi template:
- **Windows (PowerShell):**
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

### 3.3 Konfigurasi Database MySQL (XAMPP)
Pastikan modul **Apache** dan **MySQL** di XAMPP Control Panel telah aktif (berwarna hijau).
Buat database baru di MySQL bernama `jemputaman` (melalui phpMyAdmin di `http://localhost/phpmyadmin` atau CLI).

Pastikan pengaturan database di `.env` sudah sesuai:
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jemputaman
DB_USERNAME=root
DB_PASSWORD=
DB_COLLATION=utf8mb4_unicode_ci
```

### 3.4 Jalankan Migrasi dan Pengisian Data Dummy (Seeder)
```bash
php artisan migrate:fresh --seed
```

### 3.5 Jalankan Server Lokal
```bash
php artisan serve
```
Akses aplikasi melalui peramban: `http://127.0.0.1:8000`

---

## 4. Akun Pengguna Dummy (Hasil Seeder)

Semua akun dummy menggunakan password seragam: **`password`**

| No | Peran (*Role*) | Nama | Email | Deskripsi Akses |
|---|---|---|---|---|
| 1 | `admin` | Admin Armada Sekolah | `admin@jemputaman.test` | Kelola master data armada, rute, siswa, dan pantau seluruh perjalanan aktif |
| 2 | `guru` | Ibu Ratna Dewi, S.Pd (Piket) | `guru@jemputaman.test` | Pantau daftar siswa jemputan hari ini & verifikasi serah terima di sekolah |
| 3 | `orang_tua` | Budi Santoso | `ortu1@jemputaman.test` | Wali murid dari **Rayyan** (QR-RAYYAN-001) & **Alesha** (QR-ALESHA-002) |
| 4 | `orang_tua` | Siti Rahma | `ortu2@jemputaman.test` | Wali murid dari **Kenzo** (QR-KENZO-003) & **Nadhira** (QR-NADHIRA-004) |
| 5 | `pengemudi` | Pak Joko Widodo (Driver 1) | `driver1@jemputaman.test` | Driver armada Elf Sekolah (AD 1234 JA), scan QR & kirim GPS |
| 6 | `pengemudi` | Pak Bambang Hermanto (Driver 2) | `driver2@jemputaman.test` | Driver armada HiAce Sekolah (AD 5678 JA), scan QR & kirim GPS |

---

## 5. Struktur Tabel Database (Bentuk Tunggal)

Kamus data JemputAman menggunakan penamaan tabel tunggal sesuai panduan proyek:

| Model | Nama Tabel | Peruntukan Data |
|---|---|---|
| `User` | `users` | Akun pengguna (`admin`, `guru`, `orang_tua`, `pengemudi`) |
| `Sekolah` | `sekolah` | Profil sekolah utama (*SD JemputAman Surakarta*) |
| `Anak` | `anak` | Data siswa, relasi wali murid, dan `kode_qr` unik |
| `Pengemudi` | `pengemudi` | Data armada kendaraan dan pengemudi sekolah |
| `Jadwal` | `jadwal` | Pola jadwal jemputan rutin per siswa dan titik rute |
| `Penjemputan` | `penjemputan` | Transaksi perjalanan harian, status, foto bukti sekolah & tiba, konfirmasi ortu |
| `RiwayatStatus` | `riwayat_status` | Audit trail transisi alur status perjalanan |
| `LokasiDriver` | `lokasi_driver` | Catatan log koordinat latitude & longitude GPS untuk peta Leaflet |
| `Notifikasi` | `notifikasi` | Notifikasi dalam aplikasi bagi Orang Tua dan Guru |

---

## 6. Pengamanan Akses Data (Authorization & Scope)

### 6.1 Query Scope `visibleTo`
Seluruh model data operasional dilindungi dengan scope `visibleTo(User $user)`:
```php
// Orang tua hanya melihat anak miliknya; Guru & Admin melihat semua anak sekolah
$dataAnak = Anak::visibleTo(auth()->user())->get();

// Driver hanya melihat jemputan miliknya; Orang tua hanya melihat jemputan anaknya
$dataPenjemputan = Penjemputan::visibleTo(auth()->user())->get();
```

### 6.2 Middleware Rute `EnsureRole`
Membatasi akses rute berdasarkan peran akun yang sedang login:
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    // rute kelola master data
});

Route::middleware(['auth', 'role:guru,admin'])->group(function () {
    // rute pemantauan piket sekolah
});

Route::middleware(['auth', 'role:pengemudi'])->group(function () {
    // rute operasional driver (scan QR & GPS)
});

Route::middleware(['auth', 'role:orang_tua'])->group(function () {
    // rute live tracking & konfirmasi serah terima anak
});
```

---

## 7. Cara Menjalankan Pengujian Otomatis (Feature Test)

Jalankan perintah berikut di terminal:
```bash
php artisan test
```

Rangkaian uji menguji 16 skenario pengujian (77 assertions) yang lulus 100%:
- **`AuthTest`:** Login 4 variasi peran, kegagalan kredensial salah, proteksi tamu, dan proses logout.
- **`RoleMiddlewareTest`:** Proteksi hak akses rute per peran (status 200, 403 Forbidden, dan redirect login).
- **`ScopeVisibleToTest`:** Validasi pembatasan query data anak, penjemputan, keunikan Kode QR siswa, serta integritas log GPS dan konfirmasi orang tua.

---

## 8. Panduan Alur Git Tim

- **`main`:** Cabang produksi stabil.
- **`develop`:** Cabang integrasi tim utama.
- **`feature/<nama-fitur>`:** Cabang pengerjaan fitur per anggota kelompok.
