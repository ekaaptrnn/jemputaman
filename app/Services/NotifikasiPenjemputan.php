<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\Penjemputan;
use App\Models\User;

/**
 * Fase 4 — Pencatat notifikasi dalam aplikasi (FR-13).
 *
 * Dipanggil setiap kali status perjalanan berubah, dari PenjemputanController
 * (sisi pengemudi) dan KonfirmasiController (sisi orang tua).
 *
 * Penerima:
 *  - orang tua anak yang bersangkutan: setiap perubahan status
 *  - guru dan admin: hanya saat status "bermasalah"
 *
 * Dipanggil DI DALAM transaksi perubahan status, sehingga jika perubahan status
 * dibatalkan maka notifikasinya ikut dibatalkan.
 */
class NotifikasiPenjemputan
{
    public static function statusBerubah(Penjemputan $penjemputan, string $statusBaru, ?string $catatan = null): void
    {
        $penjemputan->loadMissing('anak');
        $anak = $penjemputan->anak;
        $nama = $anak->nama ?? 'siswa';

        // 1. Orang tua anak
        if ($anak && $anak->orang_tua_id) {
            [$judul, $isi] = self::pesanUntukOrangTua($statusBaru, $nama, $catatan);

            if ($judul !== null) {
                self::catat((int) $anak->orang_tua_id, $penjemputan, $judul, $isi);
            }
        }

        // 2. Guru & admin dikabari hanya jika ada kendala
        if ($statusBaru === Penjemputan::STATUS_BERMASALAH) {
            $isi = "Orang tua melaporkan kendala pada penjemputan Ananda {$nama} ({$penjemputan->kode_perjalanan}). "
                .'Catatan: '.($catatan ?: '-');

            User::whereIn('role', ['guru', 'admin'])
                ->pluck('id')
                ->each(fn ($userId) => self::catat((int) $userId, $penjemputan, 'Kendala Penjemputan Dilaporkan', $isi));
        }
    }

    /**
     * @return array{0: ?string, 1: string} [judul, isi]; judul null berarti tidak ada notifikasi.
     */
    private static function pesanUntukOrangTua(string $status, string $nama, ?string $catatan): array
    {
        return match ($status) {
            Penjemputan::STATUS_MENUJU_SEKOLAH => [
                'Armada Menuju Sekolah',
                "Armada penjemputan Ananda {$nama} sedang menuju sekolah.",
            ],
            Penjemputan::STATUS_DISERAHKAN => [
                'Anak Telah Dijemput',
                "Ananda {$nama} sudah diserahkan ke armada di sekolah. Scan QR dan foto serah terima telah tercatat.",
            ],
            Penjemputan::STATUS_DALAM_PERJALANAN => [
                'Perjalanan Pulang Dimulai',
                "Armada meninggalkan sekolah membawa Ananda {$nama}. Pantau posisinya lewat peta live GPS.",
            ],
            Penjemputan::STATUS_TIBA => [
                'Armada Tiba di Rumah',
                "Ananda {$nama} telah tiba di rumah. Silakan periksa foto bukti dan lakukan konfirmasi penerimaan di aplikasi.",
            ],
            Penjemputan::STATUS_SELESAI => [
                'Serah Terima Selesai',
                "Penerimaan Ananda {$nama} telah Anda konfirmasi. Terima kasih.",
            ],
            Penjemputan::STATUS_BERMASALAH => [
                'Laporan Kendala Tercatat',
                "Laporan kendala untuk Ananda {$nama} sudah diteruskan ke pihak sekolah. Catatan Anda: ".($catatan ?: '-'),
            ],
            default => [null, ''],
        };
    }

    private static function catat(int $userId, Penjemputan $penjemputan, string $judul, string $isi): void
    {
        Notifikasi::create([
            'user_id' => $userId,
            'penjemputan_id' => $penjemputan->id,
            'judul' => $judul,
            'isi' => $isi,
            'dibaca_at' => null,
        ]);
    }
}
