<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\Penjemputan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScopeVisibleToTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * T-06: Ortu1 hanya melihat anak miliknya lewat scope visibleTo.
     */
    public function test_ortu1_hanya_melihat_anak_miliknya(): void
    {
        $ortu1 = User::where('email', 'ortu1@jemputaman.test')->first();

        $anakList = Anak::visibleTo($ortu1)->get();

        $this->assertCount(2, $anakList);
        foreach ($anakList as $anak) {
            $this->assertEquals($ortu1->id, $anak->orang_tua_id);
        }
    }

    /**
     * Ortu2 hanya melihat anak miliknya lewat scope visibleTo.
     */
    public function test_ortu2_hanya_melihat_anak_miliknya(): void
    {
        $ortu2 = User::where('email', 'ortu2@jemputaman.test')->first();

        $anakList = Anak::visibleTo($ortu2)->get();

        $this->assertCount(2, $anakList);
        foreach ($anakList as $anak) {
            $this->assertEquals($ortu2->id, $anak->orang_tua_id);
        }
    }

    /**
     * Admin dan Guru dapat melihat seluruh data anak sekolah lewat scope visibleTo.
     */
    public function test_admin_dan_guru_melihat_semua_anak(): void
    {
        $admin = User::where('email', 'admin@jemputaman.test')->first();
        $guru = User::where('email', 'guru@jemputaman.test')->first();

        $anakAdmin = Anak::visibleTo($admin)->get();
        $anakGuru = Anak::visibleTo($guru)->get();

        $this->assertCount(4, $anakAdmin);
        $this->assertCount(4, $anakGuru);
    }

    /**
     * Pengemudi hanya melihat data penjemputan yang ditugaskan kepadanya.
     */
    public function test_pengemudi_hanya_melihat_penjemputan_miliknya(): void
    {
        $driverUser1 = User::where('email', 'driver1@jemputaman.test')->first();
        $driver1 = $driverUser1->pengemudi;

        $penjemputanList = Penjemputan::visibleTo($driverUser1)->get();

        $this->assertNotEmpty($penjemputanList);
        foreach ($penjemputanList as $penjemputan) {
            $this->assertEquals($driver1->id, $penjemputan->pengemudi_id);
        }
    }

    /**
     * Orang tua hanya melihat penjemputan anak mereka sendiri.
     */
    public function test_orang_tua_hanya_melihat_penjemputan_anaknya(): void
    {
        $ortu1 = User::where('email', 'ortu1@jemputaman.test')->first();

        $penjemputanList = Penjemputan::visibleTo($ortu1)->get();

        $this->assertNotEmpty($penjemputanList);
        foreach ($penjemputanList as $penjemputan) {
            $this->assertEquals($ortu1->id, $penjemputan->anak->orang_tua_id);
        }
    }

    /**
     * T-03: Seluruh siswa memiliki kode QR unik.
     */
    public function test_seluruh_siswa_memiliki_kode_qr_unik(): void
    {
        $semuaAnak = Anak::all();

        $this->assertCount(4, $semuaAnak);
        $qrCodes = $semuaAnak->pluck('kode_qr')->all();

        // Tidak boleh ada QR code kosong
        foreach ($qrCodes as $qr) {
            $this->assertNotEmpty($qr);
            $this->assertStringStartsWith('QR-', $qr);
        }

        // Harus unik
        $this->assertCount(count($qrCodes), array_unique($qrCodes));
    }

    /**
     * T-05 & T-08: Penjemputan selesai memiliki catatan log GPS dan konfirmasi orang tua.
     */
    public function test_penjemputan_selesai_memiliki_log_gps_dan_konfirmasi_ortu(): void
    {
        $selesai = Penjemputan::where('status', Penjemputan::STATUS_SELESAI)->first();

        $this->assertNotNull($selesai);
        $this->assertNotNull($selesai->foto_serah_sekolah);
        $this->assertNotNull($selesai->foto_tiba);
        $this->assertNotNull($selesai->waktu_konfirmasi_ortu);
        $this->assertNotNull($selesai->dikonfirmasi_oleh);

        // Memastikan relasi ke log koordinat GPS
        $this->assertGreaterThanOrEqual(1, $selesai->lokasiDriver()->count());
    }
}
