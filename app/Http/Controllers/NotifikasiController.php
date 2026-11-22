<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Fase 4 — Notifikasi dalam aplikasi (FR-13).
 *
 * Setiap pengguna HANYA bisa melihat dan menandai notifikasinya sendiri.
 * Daftar selalu disaring lewat user_id milik pengguna yang login; aksi "baca" pada
 * notifikasi milik orang lain ditolak dengan 403.
 */
class NotifikasiController extends Controller
{
    private const PER_HALAMAN = 15;

    /**
     * Daftar notifikasi milik pengguna yang login (terbaru di atas).
     */
    public function index(Request $request): View
    {
        $notifikasi = $request->user()
            ->notifikasi()
            ->with('penjemputan')
            ->latest()
            ->orderByDesc('id')
            ->paginate(self::PER_HALAMAN);

        return view('notifikasi.index', compact('notifikasi'));
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function baca(Request $request, Notifikasi $notifikasi): RedirectResponse
    {
        abort_unless($notifikasi->user_id === $request->user()->id, 403, 'Notifikasi ini bukan milik Anda.');

        if ($notifikasi->dibaca_at === null) {
            $notifikasi->update(['dibaca_at' => now()]);
        }

        return redirect()->route('notifikasi.index');
    }

    /**
     * Tandai semua notifikasi milik pengguna sebagai sudah dibaca.
     */
    public function bacaSemua(Request $request): RedirectResponse
    {
        $request->user()
            ->notifikasi()
            ->whereNull('dibaca_at')
            ->update(['dibaca_at' => now()]);

        return redirect()
            ->route('notifikasi.index')
            ->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
