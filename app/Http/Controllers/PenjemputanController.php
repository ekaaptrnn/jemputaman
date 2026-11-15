<?php

namespace App\Http\Controllers;

use App\Models\Penjemputan;
use App\Models\RiwayatStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Fase 2 — Alur operasional penjemputan (sisi pengemudi).
 *
 * Transisi yang ditangani (lihat docs/JemputAman_Perancangan_RPL_Revisi.md §3.2):
 *   dijadwalkan -> menuju_sekolah -> diserahkan -> dalam_perjalanan -> tiba
 * Transisi tiba -> selesai / bermasalah adalah jatah Orang Tua (Fase 4).
 */
class PenjemputanController extends Controller
{
    /** Disk privat (bukan 'public'): file hanya bisa diakses lewat route foto() di bawah. */
    private const DISK = 'local';

    private const FOLDER = 'bukti_penjemputan';

    /** Satu-satunya transisi yang sah untuk pengemudi: status sekarang => status berikutnya. */
    private const TRANSISI = [
        Penjemputan::STATUS_DIJADWALKAN => Penjemputan::STATUS_MENUJU_SEKOLAH,
        Penjemputan::STATUS_MENUJU_SEKOLAH => Penjemputan::STATUS_DISERAHKAN,
        Penjemputan::STATUS_DISERAHKAN => Penjemputan::STATUS_DALAM_PERJALANAN,
        Penjemputan::STATUS_DALAM_PERJALANAN => Penjemputan::STATUS_TIBA,
    ];

    /**
     * Daftar tugas jemputan hari ini milik pengemudi yang login.
     */
    public function index(Request $request): View
    {
        $tugas = Penjemputan::visibleTo($request->user())
            ->whereDate('tanggal', today())
            ->with(['anak', 'jadwal'])
            ->get()
            ->sortBy(fn (Penjemputan $p) => $p->jadwal->jam_jemput)
            ->values();

        return view('driver.index', compact('tugas'));
    }

    /**
     * Detail satu perjalanan: tombol aksi sesuai status, scan QR, upload foto, riwayat.
     */
    public function show(Request $request, Penjemputan $penjemputan): View
    {
        $this->pastikanBisaAkses($request, $penjemputan);

        $penjemputan->load([
            'anak.sekolah',
            'jadwal',
            'pengemudi',
            'riwayatStatus' => fn ($q) => $q->orderBy('waktu')->orderBy('id'),
            'riwayatStatus.pengubah',
        ]);

        return view('driver.show', ['p' => $penjemputan]);
    }

    /**
     * dijadwalkan -> menuju_sekolah
     */
    public function berangkat(Request $request, Penjemputan $penjemputan): RedirectResponse
    {
        $this->pastikanBisaAkses($request, $penjemputan);
        $this->periksaTransisi($penjemputan, Penjemputan::STATUS_MENUJU_SEKOLAH);

        $this->terapkanTransisi(
            $request,
            $penjemputan,
            Penjemputan::STATUS_MENUJU_SEKOLAH,
            [],
            'Armada berangkat menuju sekolah.'
        );

        return $this->kembali($penjemputan, 'Status diperbarui: menuju sekolah.');
    }

    /**
     * menuju_sekolah -> diserahkan
     * Syarat: kode QR cocok dengan siswa pada perjalanan ini + foto serah terima.
     */
    public function serahTerima(Request $request, Penjemputan $penjemputan): RedirectResponse
    {
        $this->pastikanBisaAkses($request, $penjemputan);
        $this->periksaTransisi($penjemputan, Penjemputan::STATUS_DISERAHKAN);

        $data = $request->validate([
            'kode_qr' => ['required', 'string', 'max:255'],
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        if (! hash_equals((string) $penjemputan->anak->kode_qr, trim($data['kode_qr']))) {
            throw ValidationException::withMessages([
                'kode_qr' => 'Kode QR tidak cocok dengan siswa pada perjalanan ini.',
            ]);
        }

        $path = $request->file('foto')->store(self::FOLDER, self::DISK);

        $this->terapkanTransisi(
            $request,
            $penjemputan,
            Penjemputan::STATUS_DISERAHKAN,
            ['waktu_dijemput' => now(), 'foto_serah_sekolah' => $path],
            'Scan QR valid dan foto serah terima diunggah.',
            $path
        );

        return $this->kembali($penjemputan, 'Serah terima di sekolah tercatat.');
    }

    /**
     * diserahkan -> dalam_perjalanan
     */
    public function mulaiPerjalanan(Request $request, Penjemputan $penjemputan): RedirectResponse
    {
        $this->pastikanBisaAkses($request, $penjemputan);
        $this->periksaTransisi($penjemputan, Penjemputan::STATUS_DALAM_PERJALANAN);

        $this->terapkanTransisi(
            $request,
            $penjemputan,
            Penjemputan::STATUS_DALAM_PERJALANAN,
            ['waktu_berangkat' => now()],
            'Armada meninggalkan sekolah menuju rumah siswa.'
        );

        return $this->kembali($penjemputan, 'Perjalanan dimulai.');
    }

    /**
     * dalam_perjalanan -> tiba
     * Syarat: foto kedatangan di rumah.
     */
    public function tiba(Request $request, Penjemputan $penjemputan): RedirectResponse
    {
        $this->pastikanBisaAkses($request, $penjemputan);
        $this->periksaTransisi($penjemputan, Penjemputan::STATUS_TIBA);

        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $path = $request->file('foto')->store(self::FOLDER, self::DISK);

        $this->terapkanTransisi(
            $request,
            $penjemputan,
            Penjemputan::STATUS_TIBA,
            ['waktu_tiba' => now(), 'foto_tiba' => $path],
            'Armada tiba di rumah siswa, foto kedatangan diunggah.',
            $path
        );

        return $this->kembali($penjemputan, 'Kedatangan tercatat. Menunggu konfirmasi orang tua.');
    }

    /**
     * Sajikan foto bukti dari storage privat dengan otorisasi (scope visibleTo).
     * Dipakai juga oleh halaman Orang Tua / Guru di fase berikutnya.
     */
    public function foto(Request $request, Penjemputan $penjemputan, string $jenis): StreamedResponse
    {
        $this->pastikanBisaAkses($request, $penjemputan);

        $path = match ($jenis) {
            'serah' => $penjemputan->foto_serah_sekolah,
            'tiba' => $penjemputan->foto_tiba,
            default => null,
        };

        abort_if(! $path || ! Storage::disk(self::DISK)->exists($path), 404);

        return Storage::disk(self::DISK)->response($path);
    }

    /**
     * Hanya pihak yang berhak melihat perjalanan ini (reuse scope visibleTo di model).
     */
    private function pastikanBisaAkses(Request $request, Penjemputan $penjemputan): void
    {
        $boleh = Penjemputan::visibleTo($request->user())
            ->whereKey($penjemputan->getKey())
            ->exists();

        abort_unless($boleh, 403, 'Perjalanan ini bukan tugas Anda.');
    }

    /**
     * Validasi di sisi server: perjalanan harus hari ini dan urutan status tidak boleh
     * melompat atau mundur (T-10).
     */
    private function periksaTransisi(Penjemputan $penjemputan, string $statusBaru): void
    {
        if (! $penjemputan->tanggal->isToday()) {
            throw ValidationException::withMessages([
                'status' => 'Perjalanan ini dijadwalkan pada '.$penjemputan->tanggal->format('d-m-Y').', hanya bisa diproses pada harinya.',
            ]);
        }

        if ((self::TRANSISI[$penjemputan->status] ?? null) !== $statusBaru) {
            throw ValidationException::withMessages([
                'status' => "Status \"{$penjemputan->status}\" tidak dapat langsung berubah menjadi \"{$statusBaru}\".",
            ]);
        }
    }

    /**
     * Ubah status + catat riwayat dalam satu transaksi. Baris dikunci agar klik ganda /
     * dua request bersamaan tidak lolos dua kali. Jika gagal, file yang baru diunggah dihapus.
     */
    private function terapkanTransisi(
        Request $request,
        Penjemputan $penjemputan,
        string $statusBaru,
        array $atribut,
        string $catatan,
        ?string $fileBaru = null,
    ): void {
        try {
            DB::transaction(function () use ($request, $penjemputan, $statusBaru, $atribut, $catatan) {
                $terkini = Penjemputan::whereKey($penjemputan->getKey())->lockForUpdate()->firstOrFail();

                $this->periksaTransisi($terkini, $statusBaru);

                $terkini->update($atribut + ['status' => $statusBaru]);

                RiwayatStatus::create([
                    'penjemputan_id' => $terkini->id,
                    'diubah_oleh' => $request->user()->id,
                    'status' => $statusBaru,
                    'waktu' => now(),
                    'catatan' => $catatan,
                ]);
            });
        } catch (Throwable $e) {
            if ($fileBaru) {
                Storage::disk(self::DISK)->delete($fileBaru);
            }

            throw $e;
        }
    }

    private function kembali(Penjemputan $penjemputan, string $pesan): RedirectResponse
    {
        return redirect()
            ->route('driver.penjemputan.show', $penjemputan)
            ->with('success', $pesan);
    }
}
