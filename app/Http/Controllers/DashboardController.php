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

        // Fase 3 — rekap status perjalanan hari ini (dipakai dashboard admin & guru)
        $rekapStatus = Penjemputan::whereDate('tanggal', $today)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats['dalam_perjalanan'] = (int) ($rekapStatus[Penjemputan::STATUS_DALAM_PERJALANAN] ?? 0);
        $stats['menunggu_konfirmasi'] = (int) ($rekapStatus[Penjemputan::STATUS_TIBA] ?? 0);
        $stats['selesai'] = (int) ($rekapStatus[Penjemputan::STATUS_SELESAI] ?? 0);
        $stats['bermasalah'] = (int) ($rekapStatus[Penjemputan::STATUS_BERMASALAH] ?? 0);

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

        // Dashboard driver: tugas berikutnya = perjalanan hari ini yang belum selesai, urut jam jemput
        $tugasBerikutnya = $user->isPengemudi()
            ? $penjemputanHariIni
                ->load('jadwal')
                ->filter(fn (Penjemputan $p) => ! in_array($p->status, [
                    Penjemputan::STATUS_TIBA,
                    Penjemputan::STATUS_SELESAI,
                    Penjemputan::STATUS_BERMASALAH,
                ], true))
                ->sortBy(fn (Penjemputan $p) => $p->jadwal->jam_jemput)
                ->first()
            : null;

        return view('dashboard', compact('stats', 'penjemputanHariIni', 'anakSaya', 'tugasBerikutnya'));
    }
}
