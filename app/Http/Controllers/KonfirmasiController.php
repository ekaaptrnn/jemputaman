<?php

namespace App\Http\Controllers;

use App\Models\Penjemputan;
use App\Models\RiwayatStatus;
use App\Services\NotifikasiPenjemputan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Fase 4 — Konfirmasi serah terima oleh Orang Tua.
 *
 * Transisi yang ditangani (lihat docs/JemputAman_Perancangan_RPL_Revisi.md §3.2):
 *   tiba -> selesai      (tombol "Anak Telah Diterima")
 *   tiba -> bermasalah   (tombol "Laporkan Kendala", catatan wajib)
 *
 * Pola sama dengan PenjemputanController (Fase 2): otorisasi lewat scope visibleTo,
 * perubahan status dalam transaksi + lockForUpdate, dan setiap perubahan dicatat di riwayat_status.
 */
class KonfirmasiController extends Controller
{
    /**
     * Halaman konfirmasi: detail perjalanan, foto bukti, dan tombol aksi (hanya saat status "tiba").
     */
    public function show(Request $request, Penjemputan $penjemputan): View
    {
        $this->pastikanBisaAkses($request, $penjemputan);

        $penjemputan->load([
            'anak.sekolah',
            'jadwal',
            'pengemudi.user',
            'riwayatStatus' => fn ($q) => $q->orderBy('waktu')->orderBy('id'),
            'riwayatStatus.pengubah',
        ]);

        return view('konfirmasi.show', ['p' => $penjemputan]);
    }

    /**
     * tiba -> selesai ("Anak Telah Diterima")
     */
    public function terima(Request $request, Penjemputan $penjemputan): RedirectResponse
    {
        $this->pastikanBisaAkses($request, $penjemputan);
        $this->periksaBisaDikonfirmasi($penjemputan);

        $this->terapkan(
            $request,
            $penjemputan,
            Penjemputan::STATUS_SELESAI,
            fn (Penjemputan $terkini) => [
                'waktu_konfirmasi_ortu' => now(),
                'dikonfirmasi_oleh' => $request->user()->id,
            ],
            'Orang tua mengonfirmasi anak telah diterima dengan aman.',
        );

        return $this->kembali($penjemputan, 'Terima kasih, penerimaan anak sudah dikonfirmasi.');
    }

    /**
     * tiba -> bermasalah ("Laporkan Kendala"). Catatan wajib diisi.
     */
    public function laporkan(Request $request, Penjemputan $penjemputan): RedirectResponse
    {
        $this->pastikanBisaAkses($request, $penjemputan);
        $this->periksaBisaDikonfirmasi($penjemputan);

        $data = $request->validate([
            'catatan' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'catatan.required' => 'Catatan kendala wajib diisi.',
            'catatan.min' => 'Catatan kendala minimal 5 karakter.',
        ]);

        $catatan = trim($data['catatan']);

        $this->terapkan(
            $request,
            $penjemputan,
            Penjemputan::STATUS_BERMASALAH,
            fn (Penjemputan $terkini) => [
                // Catatan lama (dari sistem/driver) dipertahankan, laporan orang tua ditambahkan di bawahnya.
                'catatan' => trim(($terkini->catatan ? $terkini->catatan."\n" : '').'[Laporan orang tua] '.$catatan),
            ],
            'Orang tua melaporkan kendala: '.$catatan,
            $catatan,
        );

        return $this->kembali($penjemputan, 'Laporan kendala sudah dikirim ke pihak sekolah.');
    }

    /**
     * Hanya orang tua pemilik anak (reuse scope visibleTo di model).
     */
    private function pastikanBisaAkses(Request $request, Penjemputan $penjemputan): void
    {
        $boleh = Penjemputan::visibleTo($request->user())
            ->whereKey($penjemputan->getKey())
            ->exists();

        abort_unless($boleh, 403, 'Perjalanan ini bukan milik anak Anda.');
    }

    /**
     * Aturan di sisi server: hanya saat status "tiba" dan pada hari perjalanan.
     */
    private function periksaBisaDikonfirmasi(Penjemputan $penjemputan): void
    {
        if (! $penjemputan->tanggal->isToday()) {
            throw ValidationException::withMessages([
                'status' => 'Perjalanan ini dijadwalkan pada '.$penjemputan->tanggal->format('d-m-Y').', hanya bisa diproses pada harinya.',
            ]);
        }

        if ($penjemputan->status !== Penjemputan::STATUS_TIBA) {
            throw ValidationException::withMessages([
                'status' => "Konfirmasi hanya bisa dilakukan saat status \"tiba\" (status sekarang: \"{$penjemputan->status}\").",
            ]);
        }
    }

    /**
     * Ubah status + catat riwayat + catat notifikasi dalam satu transaksi.
     * Baris dikunci agar klik ganda / dua request bersamaan tidak lolos dua kali.
     *
     * @param  callable(Penjemputan): array<string, mixed>  $atribut  atribut tambahan, dihitung dari data terkini
     */
    private function terapkan(
        Request $request,
        Penjemputan $penjemputan,
        string $statusBaru,
        callable $atribut,
        string $catatanRiwayat,
        ?string $catatanNotifikasi = null,
    ): void {
        DB::transaction(function () use ($request, $penjemputan, $statusBaru, $atribut, $catatanRiwayat, $catatanNotifikasi) {
            $terkini = Penjemputan::whereKey($penjemputan->getKey())->lockForUpdate()->firstOrFail();

            // Cek ulang setelah baris dikunci (kalau request lain sudah mengubah status duluan).
            $this->periksaBisaDikonfirmasi($terkini);

            $terkini->update($atribut($terkini) + ['status' => $statusBaru]);

            RiwayatStatus::create([
                'penjemputan_id' => $terkini->id,
                'diubah_oleh' => $request->user()->id,
                'status' => $statusBaru,
                'waktu' => now(),
                'catatan' => $catatanRiwayat,
            ]);

            NotifikasiPenjemputan::statusBerubah($terkini, $statusBaru, $catatanNotifikasi);
        });
    }

    private function kembali(Penjemputan $penjemputan, string $pesan): RedirectResponse
    {
        return redirect()
            ->route('konfirmasi.show', $penjemputan)
            ->with('success', $pesan);
    }
}
