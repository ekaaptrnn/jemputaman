<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\Jadwal;
use App\Models\Pengemudi;
use App\Models\Penjemputan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard dinamis sesuai peran pengguna.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $today = Carbon::today();

        $stats = [
            'total_anak' => Anak::count(),
            'total_pengemudi' => Pengemudi::count(),
            'total_jadwal' => Jadwal::where('aktif', true)->count(),
            'penjemputan_hari_ini' => Penjemputan::whereDate('tanggal', $today)->count(),
        ];

        // Ambil data penjemputan yang relevan dengan hak akses peran (Scope visibleTo)
        $penjemputanHariIni = Penjemputan::visibleTo($user)
            ->whereDate('tanggal', $today)
            ->with(['anak', 'pengemudi.user'])
            ->latest()
            ->get();

        // Data anak untuk orang tua
        $anakSaya = $user->isOrangTua()
            ? Anak::visibleTo($user)->with(['jadwal.pengemudi.user'])->get()
            : collect();

        return view('dashboard', compact('stats', 'penjemputanHariIni', 'anakSaya'));
    }
}
