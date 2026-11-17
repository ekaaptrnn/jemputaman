<?php

namespace App\Http\Controllers;

use App\Models\LokasiDriver;
use App\Models\Penjemputan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Fase 3 — Live GPS Tracking.
 *
 *   POST /penjemputan/{penjemputan}/lokasi          (pengemudi)  -> simpan koordinat
 *   GET  /penjemputan/{penjemputan}/lokasi-terkini  (semua peran yang berhak) -> JSON koordinat terakhir
 *   GET  /penjemputan/{penjemputan}/lacak           (orang tua, guru, admin)  -> halaman peta Leaflet
 *
 * Pola otorisasi sama dengan PenjemputanController (Fase 2): scope visibleTo.
 */
class LokasiController extends Controller
{
    /** Jumlah titik jejak yang dikirim saat peta pertama kali dimuat. */
    private const MAKS_JEJAK = 50;

    /**
     * Simpan koordinat terbaru dari HP pengemudi.
     * Syarat: pemilik perjalanan + status dalam_perjalanan.
     */
    public function store(Request $request, Penjemputan $penjemputan): JsonResponse
    {
        $this->pastikanBisaAkses($request, $penjemputan);

        if ($penjemputan->status !== Penjemputan::STATUS_DALAM_PERJALANAN) {
            return response()->json([
                'pesan' => 'Koordinat hanya diterima saat status perjalanan "dalam_perjalanan".',
                'status' => $penjemputan->status,
            ], 422);
        }

        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $lokasi = LokasiDriver::create([
            'penjemputan_id' => $penjemputan->id,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'dicatat_pada' => now(),
        ]);

        return response()->json([
            'pesan' => 'Lokasi tercatat.',
            'id' => $lokasi->id,
            'latitude' => $lokasi->latitude,
            'longitude' => $lokasi->longitude,
            'dicatat_pada' => $lokasi->dicatat_pada->toIso8601String(),
        ], 201);
    }

    /**
     * Koordinat terakhir armada (JSON). Di-polling peta tiap 5 detik.
     * Tambahkan ?jejak=1 untuk ikut mengirim riwayat titik (dipakai sekali saat halaman dibuka).
     */
    public function terkini(Request $request, Penjemputan $penjemputan): JsonResponse
    {
        $this->pastikanBisaAkses($request, $penjemputan);

        $terakhir = $penjemputan->lokasiDriver()
            ->orderByDesc('dicatat_pada')
            ->orderByDesc('id')
            ->first();

        $respons = [
            'status' => $penjemputan->status,
            'status_label' => ucwords(str_replace('_', ' ', $penjemputan->status)),
            'aktif' => $penjemputan->status === Penjemputan::STATUS_DALAM_PERJALANAN,
            'lokasi' => $terakhir ? [
                'latitude' => $terakhir->latitude,
                'longitude' => $terakhir->longitude,
                'dicatat_pada' => $terakhir->dicatat_pada->toIso8601String(),
                'detik_lalu' => max(0, (int) $terakhir->dicatat_pada->diffInSeconds(now(), true)),
            ] : null,
        ];

        if ($request->boolean('jejak')) {
            $respons['jejak'] = $penjemputan->lokasiDriver()
                ->orderByDesc('dicatat_pada')
                ->orderByDesc('id')
                ->limit(self::MAKS_JEJAK)
                ->get(['latitude', 'longitude'])
                ->reverse()
                ->values()
                ->map(fn (LokasiDriver $l) => [$l->latitude, $l->longitude]);
        }

        return response()->json($respons);
    }

    /**
     * Halaman peta pemantauan untuk orang tua / guru / admin.
     */
    public function lacak(Request $request, Penjemputan $penjemputan): View
    {
        $this->pastikanBisaAkses($request, $penjemputan);

        $penjemputan->load(['anak', 'jadwal', 'pengemudi.user']);

        return view('tracking.show', ['p' => $penjemputan]);
    }

    /**
     * Hanya pihak yang berhak melihat perjalanan ini (reuse scope visibleTo, sama seperti Fase 2).
     */
    private function pastikanBisaAkses(Request $request, Penjemputan $penjemputan): void
    {
        $boleh = Penjemputan::visibleTo($request->user())
            ->whereKey($penjemputan->getKey())
            ->exists();

        abort_unless($boleh, 403, 'Perjalanan ini bukan milik Anda.');
    }
}
