<?php

use App\Http\Controllers\AuthController;
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

// Dashboard sementara
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard')->middleware('auth');

// Contoh grup route berdasarkan peran untuk anggota lain:
// Route::middleware(['auth', 'role:admin'])->group(function () {
//     Route::resource('/sekolah', SekolahController::class);
//     Route::resource('/pengemudi', PengemudiController::class);
// });
