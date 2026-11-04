# JemputAman — Perancangan RPL dan Panduan Pengerjaan

**Sistem Serah Terima Anak Antar-Jemput Berbasis Armada Resmi Sekolah dengan Live Tracking GPS dan Verifikasi QR**  
Laravel 13 · Proyek Kelompok 4 Orang · Berdasarkan Modul Praktikum Pemrograman

---

## 1. Pendahuluan

### 1.1 Latar Belakang

Orang tua membutuhkan kepastian dan transparansi mengenai keselamatan anak saat perjalanan pulang sekolah. Layanan antar-jemput yang diselenggarakan secara resmi oleh **satu sekolah** dengan armada internal membutuhkan sistem informasi terstruktur yang mencatat jadwal, alur penjemputan, pelacakan armada secara langsung (*live tracking*), serta bukti fisik serah terima di titik keberangkatan maupun kedatangan.

JemputAman adalah aplikasi web yang dirancang khusus untuk mengelola armada jemputan resmi sekolah. Sistem ini memastikan penyerahan anak di sekolah divalidasi menggunakan pemindaian **Kode QR siswa dan foto bukti**, pergerakan kendaraan dipantau secara langsung menggunakan **Peta GPS (Leaflet & OpenStreetMap)**, dan penyerahan anak di rumah diverifikasi dengan **foto kedatangan serta tombol konfirmasi resmi oleh Orang Tua**.

### 1.2 Tujuan

1. Mengimplementasikan sistem antar-jemput terpadu berbasis sekolah dengan alur serah terima dua sisi (*two-factor confirmation*).
2. Menerapkan arsitektur **Laravel 13 (PHP 8.2+)** dengan pola MVC, Routes, Blade Templating, reusable components, dan Tailwind CSS.
3. Menyediakan fitur **Live GPS Tracking** menggunakan Geolocation API browser dan Leaflet JS tanpa ketergantungan API pihak ketiga berbayar.
4. Menjamin akses data anak dan rute perjalanan terlindungi secara ketat berdasarkan peran pengguna (*server-side role authorization* dan *Eloquent query scopes*).

### 1.3 Batasan Sistem

1. **Lingkup Sekolah Tunggal:** Aplikasi dikelola dan diterapkan khusus untuk **1 Sekolah Utama** beserta armada dan pengemudi internal resmi sekolah.
2. **Identitas Siswa Tanpa Akun:** Siswa tidak memiliki akun login aplikasi, melainkan dibekali kartu identitas fisik ber-**Kode QR unik** yang dibuat oleh sistem.
3. **Konfirmasi Kedatangan oleh Orang Tua:** Aktor "Penerima di tujuan" ditiadakan sebagai akun terpisah; hak konfirmasi penerimaan anak di rumah dipegang langsung oleh akun **Orang Tua**.
4. **Pelacakan Lokasi (GPS):** Menggunakan Geolocation API pada browser perangkat pengemudi yang mengirim koordinat latitude & longitude secara berkala ke database, kemudian dipetakan ke peta OpenStreetMap pada dashboard orang tua dan guru.
5. **Bukti Fisik Foto:** Driver mengunggah foto saat siswa naik di sekolah dan foto saat siswa tiba di rumah. Foto disimpan di storage privat berotorisasi.
6. **Data Simulatif:** Menggunakan data fiktif (dummy) berbahasa Indonesia untuk keperluan pengujian dan pembelajaran.

### 1.4 Teknologi

| Komponen | Pilihan Teknologi | Keterangan |
|---|---|---|
| **Framework Backend** | Laravel 13 (PHP 8.2 ke atas) | Versi framework stabil terbaru dengan arsitektur modern |
| **Database** | MySQL (XAMPP / Laragon) atau SQLite | Portabel dan terintegrasi langsung |
| **Frontend UI** | Blade Templating & Tailwind CSS | Responsif, layout modular `<x-layout>`, komponen reusable |
| **Peta & Pelacakan** | Leaflet.js & OpenStreetMap | Open-source, ringan, tanpa biaya API / billing |
| **Autentikasi** | Autentikasi Manual Laravel | Menggunakan `Auth::attempt` dan sesi aman tanpa paket eksternal |
| **Pengujian** | PHPUnit Feature Test (`php artisan test`) | Pengujian otomatis in-memory |

---

## 2. Analisis Kebutuhan

### 2.1 Aktor dan Hak Akses

| Aktor | Deskripsi | Akses Utama |
|---|---|---|
| **Admin** | Koordinator armada atau staf tata usaha sekolah | Kelola seluruh master data (sekolah, armada pengemudi, siswa, jadwal), pantau semua perjalanan aktif, kelola akun pengguna |
| **Guru / Sekolah** | Staf pengajar atau piket sekolah | Memantau daftar siswa yang dijemput hari ini, memvalidasi penyerahan anak di gerbang sekolah, melihat riwayat penjemputan |
| **Orang Tua** | Wali murid pemilik data anak | Melihat jadwal antar-jemput anak sendiri, melacak posisi armada pengemudi di peta live secara real-time, melihat foto bukti penjemputan, mengonfirmasi penerimaan anak saat tiba di rumah |
| **Pengemudi (Driver)** | Staf driver armada resmi sekolah | Melihat rute & jadwal penjemputan hari ini, memindai Kode QR siswa di sekolah, mengunggah foto serah terima di sekolah, mengirim koordinat GPS otomatis, mengunggah foto tiba di rumah |
| **Siswa** | Anak didik yang diantar-jemput | *Tidak memiliki akun login*; memegang kartu identitas ber-Kode QR unik |

### 2.2 Kebutuhan Fungsional (Functional Requirements)

- **FR-01 (Autentikasi & Peran):** Login, logout, dan proteksi rute berdasarkan peran (`admin`, `guru`, `orang_tua`, `pengemudi`).
- **FR-02 (CRUD Siswa & Generate QR):** Pencatatan data siswa oleh Admin/Guru serta pembuatan Kode QR unik acak per siswa.
- **FR-03 (CRUD Pengemudi & Armada):** Pengelolaan data driver dan armada kendaraan sekolah (plat nomor, tipe kendaraan).
- **FR-04 (CRUD Jadwal Rutin):** Pengaturan jadwal rutin antar-jemput per siswa (hari, jam jemput, titik tujuan rumah).
- **FR-05 (Manajemen Penjemputan Harian):** Pembuatan catatan penjemputan harian dari jadwal aktif dengan `kode_perjalanan` unik.
- **FR-06 (Alur Status Penjemputan):** Transisi status bertahap: *dijadwalkan &rarr; menuju_sekolah &rarr; diserahkan &rarr; dalam_perjalanan &rarr; tiba &rarr; selesai (atau bermasalah)*.
- **FR-07 (Pemindaian QR & Foto di Sekolah):** Pengemudi memindai QR siswa dan mengunggah foto serah terima siswa di gerbang sekolah.
- **FR-08 (Live Tracking GPS Peta Leaflet):** Pengemudi memancarkan koordinat lokasi setiap 5–10 detik; Orang Tua dan Guru memantau pergerakan armada secara langsung di peta.
- **FR-09 (Foto Kedatangan di Rumah):** Pengemudi tiba di rumah dan mengunggah foto anak telah sampai di lokasi tujuan.
- **FR-10 (Konfirmasi Penerimaan oleh Orang Tua):** Formulir/tombol verifikasi akhir oleh Orang Tua untuk menutup status perjalanan menjadi *Selesai*.
- **FR-11 (Laporan Masalah / Insiden):** Orang tua dapat menandai status *Bermasalah* jika anak belum sampai atau terjadi kejanggalan.
- **FR-12 (Dashboard & Riwayat Perjalanan):** Ringkasan statistik, daftar jemputan hari ini, dan tabel riwayat perjalanan yang terfilter sesuai hak akses pengguna.
- **FR-13 (Notifikasi Dalam Aplikasi):** Pencatatan notifikasi status di database aplikasi untuk Orang Tua dan Guru.

---

## 3. Pemodelan Proses & Alur Serah Terima

### 3.1 Alur Bisnis Inti (State Machine)

```
[Dijadwalkan]
       ↓ (Driver berangkat ke sekolah)
[Menuju Sekolah]
       ↓ (Driver tiba di sekolah, scan QR Siswa + foto serah)
[Diserahkan / Dijemput]
       ↓ (Armada jalan menuju rumah siswa + GPS aktif)
[Dalam Perjalanan]
       ↓ (Armada sampai di rumah + Driver ambil foto tiba)
[Tiba (Menunggu Konfirmasi)]
       ↓ (Orang Tua verifikasi & klik "Anak Diterima")
[Selesai]
       └── (Jika ada kendala: Orang Tua klik "Laporkan Masalah" → [Bermasalah])
```

### 3.2 Aturan Transisi Status

| Dari Status | Ke Status | Dilakukan Oleh | Syarat & Bukti Wajib |
|---|---|---|---|
| `dijadwalkan` | `menuju_sekolah` | Pengemudi / Admin | Armada siap berangkat menuju titik sekolah |
| `menuju_sekolah` | `diserahkan` | Pengemudi | Memindai Kode QR Siswa yang valid + unggah `foto_serah_sekolah` |
| `diserahkan` | `dalam_perjalanan` | Pengemudi | Pengemudi memulai perjalanan; pelacakan koordinat GPS aktif |
| `dalam_perjalanan` | `tiba` | Pengemudi | Armada sampai di titik rumah + unggah `foto_tiba` |
| `tiba` | `selesai` | **Orang Tua** | Orang tua menekan tombol konfirmasi penerimaan di aplikasi |
| `tiba` | `bermasalah` | **Orang Tua** | Orang tua melaporkan ketidaksesuaian atau anak belum diterima |

---

## 4. Perancangan Database

### 4.1 Daftar Tabel

1. **`users`**: Akun pengguna sistem (`id`, `name`, `email`, `password`, `role`, `no_hp`). Nilai role: `admin`, `guru`, `orang_tua`, `pengemudi`.
2. **`sekolah`**: Identitas sekolah utama pengelola armada (`id`, `nama`, `alamat`).
3. **`anak`**: Data siswa (`id`, `orang_tua_id`, `sekolah_id`, `nama`, `kelas`, `kode_qr`).
4. **`pengemudi`**: Data armada pengemudi (`id`, `user_id`, `no_kendaraan`, `jenis_kendaraan`, `aktif`).
5. **`jadwal`**: Pola rutin jemputan (`id`, `anak_id`, `pengemudi_id`, `sekolah_id`, `hari`, `jam_jemput`, `titik_jemput`, `titik_tujuan`, `aktif`).
6. **`penjemputan`**: Transaksi perjalanan harian (`id`, `kode_perjalanan`, `jadwal_id`, `anak_id`, `pengemudi_id`, `tanggal`, `status`, `waktu_dijemput`, `waktu_berangkat`, `waktu_tiba`, `foto_serah_sekolah`, `foto_tiba`, `waktu_konfirmasi_ortu`, `dikonfirmasi_oleh`, `catatan`).
7. **`riwayat_status`**: Audit trail histori status (`id`, `penjemputan_id`, `diubah_oleh`, `status`, `waktu`, `catatan`).
8. **`lokasi_driver`**: Log koordinat GPS perjalanan (`id`, `penjemputan_id`, `latitude`, `longitude`, `dicatat_pada`).
9. **`notifikasi`**: Pesan dalam aplikasi (`id`, `user_id`, `penjemputan_id`, `judul`, `isi`, `dibaca_at`).

### 4.2 Kamus Data Kunci

#### Tabel `anak`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint (PK) | Auto increment |
| `orang_tua_id` | foreignId (users) | Akun orang tua siswa (`role: orang_tua`), cascade on delete |
| `sekolah_id` | foreignId (sekolah) | Id sekolah pengelola armada |
| `nama` | string | Nama lengkap siswa |
| `kelas` | string | Tingkat kelas (misal: 3A, 4B) |
| `kode_qr` | string (unique) | Kode string acak unik untuk barcode/QR kartu siswa |

#### Tabel `penjemputan`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint (PK) | Auto increment |
| `kode_perjalanan` | string (unique) | Format: `JA-YYYYMMDD-NNN`, digunakan sebagai route key URI dinamis |
| `jadwal_id` | foreignId (jadwal) | Relasi ke master jadwal |
| `anak_id` | foreignId (anak) | Relasi ke data siswa |
| `pengemudi_id` | foreignId (pengemudi) | Relasi ke staf driver sekolah |
| `tanggal` | date | Tanggal pelaksanaan penjemputan |
| `status` | string | `dijadwalkan`, `menuju_sekolah`, `diserahkan`, `dalam_perjalanan`, `tiba`, `selesai`, `bermasalah` |
| `foto_serah_sekolah`| string (nullable) | Path file foto serah terima di sekolah (storage privat) |
| `foto_tiba` | string (nullable) | Path file foto kedatangan di rumah (storage privat) |
| `waktu_konfirmasi_ortu`| datetime (nullable)| Waktu orang tua menekan konfirmasi penerimaan |
| `dikonfirmasi_oleh`| foreignId (users, null)| Akun orang tua yang mengonfirmasi penerimaan |

#### Tabel `lokasi_driver`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint (PK) | Auto increment |
| `penjemputan_id` | foreignId (penjemputan)| Terikat pada perjalanan yang sedang berlangsung, cascade on delete |
| `latitude` | decimal(10, 7) | Koordinat garis lintang GPS |
| `longitude` | decimal(10, 7) | Koordinat garis bujur GPS |
| `dicatat_pada` | timestamp | Waktu koordinat direkam oleh perangkat driver |

---

## 5. Perancangan Fitur Live Tracking & Verifikasi

### 5.1 Mekanisme Live Tracking (Leaflet & OpenStreetMap)
1. **Pengiriman Koordinat:** Perangkat pengemudi mengaktifkan *watchPosition* pada Geolocation API browser dan mengirimkan payload `(latitude, longitude)` melalui request AJAX/fetch ke endpoint `POST /penjemputan/{id}/lokasi` setiap 5–10 detik selama status `dalam_perjalanan`.
2. **Penyajian Peta:** Pada halaman pemantauan Orang Tua dan Guru, peta Leaflet.js melakukan polling ringan (interval 5 detik) ke endpoint `GET /penjemputan/{id}/lokasi-terkini` dan memperbarui posisi marker armada secara mulus (*smooth panTo animation*).

### 5.2 Mekanisme Pemindaian QR & Unggah Bukti
1. Kartu siswa memuat QR Code yang merepresentasikan `kode_qr`.
2. Pada aplikasi driver, modul kamera memindai kode QR. Controller memvalidasi apakah kode cocok dengan data anak pada perjalanan tersebut.
3. Driver mengambil foto serah terima di sekolah dan foto tiba di rumah. File disimpan di folder privat `storage/app/private/bukti_penjemputan/` dan hanya dapat diakses melalui route controller berotorisasi.

---

## 6. Pembagian Tugas Kelompok (4 Orang)

| Anggota | Peran Utama | Fokus Tanggung Jawab |
|---|---|---|
| **Anggota 1** | **Ketua, Fondasi, dan Keamanan** | Setup proyek Laravel 13 & MySQL, seluruh migration 10 tabel, model Eloquent, otentikasi manual multi-role (`admin`, `guru`, `orang_tua`, `pengemudi`), middleware `EnsureRole`, query scope `visibleTo`, factory & seeder data dummy, serta PHPUnit Feature Test dasar. |
| **Anggota 2** | **Data Master & Identitas QR** | Controller, rute, dan antarmuka CRUD Data Siswa (lengkap dengan fitur generate dan cetak kartu Kode QR), CRUD Pengemudi & Armada, CRUD Jadwal Jemputan, dan Pengaturan Profil Sekolah. Validasi input dan eager loading relasi. |
| **Anggota 3** | **Alur Operasional Penjemputan** | Controller alur penjemputan harian, modul pemindaian Kode QR di sisi driver, logika perubahan alur status, upload dan proteksi storage foto bukti (di sekolah & tiba), modul konfirmasi serah terima Orang Tua, serta pelaporan masalah insiden. |
| **Anggota 4** | **Antarmuka, Integrasi Peta GPS, QA** | Pembangunan kerangka layout Blade `<x-layout>`, integrasi peta Leaflet.js & OpenStreetMap untuk Live Tracking GPS, dashboard dinamis per peran, tabel riwayat perjalanan dengan filter pencarian, notifikasi dalam aplikasi, serta pengujian terpadu dan dokumentasi laporan. |

---

## 7. Rencana Pengujian Otomatis (Feature Test)

1. **`T-01`**: Login berhasil untuk keempat peran (`admin`, `guru`, `orang_tua`, `pengemudi`) dan dialihkan ke dashboard yang sesuai.
2. **`T-02`**: Proteksi rute dengan middleware `role` menolak aktor yang tidak berhak (HTTP 403) dan tamu dialihkan ke `/login`.
3. **`T-03`**: Siswa memiliki `kode_qr` unik yang tidak terduplikasi.
4. **`T-04`**: Pengemudi dapat memperbarui status perjalanan dari `dijadwalkan` ke `diserahkan` setelah scan QR siswa valid.
5. **`T-05`**: Endpoint lokasi mencatat koordinat latitude dan longitude pengemudi ke tabel `lokasi_driver`.
6. **`T-06`**: Orang tua hanya dapat melihat data anak dan perjalanan milik anaknya sendiri (`Scope visibleTo`).
7. **`T-07`**: Pengemudi dapat mengunggah foto kedatangan di rumah saat tiba di lokasi.
8. **`T-08`**: Orang tua dapat mengonfirmasi serah terima penerimaan anak dan menutup status menjadi `selesai`.
9. **`T-09`**: Orang tua lain dilarang mengonfirmasi penjemputan anak yang bukan miliknya.
10. **`T-10`**: Alur status dilarang melompat atau mundur di luar urutan transisi yang sah.
