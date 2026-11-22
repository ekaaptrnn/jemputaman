<?php

use App\Http\Controllers\AnakController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\KonfirmasiController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PenjemputanController;
use App\Http\Controllers\PengemudiController;
use App\Http\Controllers\SekolahController;
use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard
Route::get('/', function () {
    return redirect('/dashboard');
});

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Dashboard Dinamis (Semua Peran Login)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');

// Rute Guru & Admin (Daftar Siswa & Cetak Kartu QR)
Route::middleware(['auth', 'role:guru,admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/anak', [AnakController::class, 'index'])->name('anak.index');
    Route::get('/anak/{anak}/kartu', [AnakController::class, 'kartu'])->name('anak.kartu');
});

// Rute Master Data Khusus Admin (Fase 1)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // CRUD Siswa (Create, Store, Edit, Update, Destroy)
    Route::get('/anak/create', [AnakController::class, 'create'])->name('anak.create');
    Route::post('/anak', [AnakController::class, 'store'])->name('anak.store');
    Route::get('/anak/{anak}/edit', [AnakController::class, 'edit'])->name('anak.edit');
    Route::put('/anak/{anak}', [AnakController::class, 'update'])->name('anak.update');
    Route::delete('/anak/{anak}', [AnakController::class, 'destroy'])->name('anak.destroy');

    // CRUD Pengemudi & Armada
    Route::resource('pengemudi', PengemudiController::class)->except(['show']);

    // CRUD Jadwal Rutin Penjemputan
    Route::resource('jadwal', JadwalController::class)->except(['show']);

    // Pengaturan Profil Sekolah
    Route::get('/sekolah', [SekolahController::class, 'edit'])->name('sekolah.edit');
    Route::put('/sekolah', [SekolahController::class, 'update'])->name('sekolah.update');
});

// Fase 2 — Operasional Pengemudi (jemputan hari ini, scan QR, upload foto bukti)
Route::middleware(['auth', 'role:pengemudi'])->prefix('driver')->name('driver.')->group(function () {
    Route::get('/penjemputan', [PenjemputanController::class, 'index'])->name('penjemputan.index');
    Route::get('/penjemputan/{penjemputan}', [PenjemputanController::class, 'show'])->name('penjemputan.show');
    Route::post('/penjemputan/{penjemputan}/berangkat', [PenjemputanController::class, 'berangkat'])->name('penjemputan.berangkat');
    Route::post('/penjemputan/{penjemputan}/serah-terima', [PenjemputanController::class, 'serahTerima'])->name('penjemputan.serah-terima');
    Route::post('/penjemputan/{penjemputan}/mulai', [PenjemputanController::class, 'mulaiPerjalanan'])->name('penjemputan.mulai');
    Route::post('/penjemputan/{penjemputan}/tiba', [PenjemputanController::class, 'tiba'])->name('penjemputan.tiba');
});

// Foto bukti dari storage privat, disajikan dengan otorisasi (scope visibleTo)
Route::get('/penjemputan/{penjemputan}/foto/{jenis}', [PenjemputanController::class, 'foto'])
    ->whereIn('jenis', ['serah', 'tiba'])
    ->middleware('auth')
    ->name('penjemputan.foto');

// Fase 3 — Live GPS Tracking (Leaflet + OpenStreetMap)
// Kirim koordinat: hanya pengemudi, dibatasi throttle 'lokasi' (lihat AppServiceProvider).
Route::post('/penjemputan/{penjemputan}/lokasi', [LokasiController::class, 'store'])
    ->middleware(['auth', 'role:pengemudi', 'throttle:lokasi'])
    ->name('penjemputan.lokasi.store');

// Koordinat terakhir (JSON) untuk polling peta; akses disaring scope visibleTo di controller.
Route::get('/penjemputan/{penjemputan}/lokasi-terkini', [LokasiController::class, 'terkini'])
    ->middleware('auth')
    ->name('penjemputan.lokasi.terkini');

// Halaman peta pemantauan: orang tua, guru, admin.
Route::get('/penjemputan/{penjemputan}/lacak', [LokasiController::class, 'lacak'])
    ->middleware(['auth', 'role:orang_tua,guru,admin'])
    ->name('penjemputan.lacak');

// Fase 4 — Konfirmasi serah terima oleh Orang Tua (tiba -> selesai / bermasalah)
// Role dibatasi orang_tua; kepemilikan anak diperiksa di controller (scope visibleTo) -> T-09.
Route::middleware(['auth', 'role:orang_tua'])->prefix('konfirmasi')->name('konfirmasi.')->group(function () {
    Route::get('/{penjemputan}', [KonfirmasiController::class, 'show'])->name('show');
    Route::post('/{penjemputan}/terima', [KonfirmasiController::class, 'terima'])->name('terima');
    Route::post('/{penjemputan}/laporkan', [KonfirmasiController::class, 'laporkan'])->name('laporkan');
});

// Fase 4 — Notifikasi dalam aplikasi (semua peran, hanya milik sendiri)
Route::middleware('auth')->prefix('notifikasi')->name('notifikasi.')->group(function () {
    Route::get('/', [NotifikasiController::class, 'index'])->name('index');
    Route::post('/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('baca-semua');
    Route::post('/{notifikasi}/baca', [NotifikasiController::class, 'baca'])->name('baca');
});
