<?php

use App\Http\Controllers\AnakController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JadwalController;
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
