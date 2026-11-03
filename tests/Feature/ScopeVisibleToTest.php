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
     * Ortu1 hanya melihat anak miliknya lewat scope visibleTo.
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
     * Admin dapat melihat seluruh data anak lewat scope visibleTo.
     */
    public function test_admin_melihat_semua_anak(): void
    {
        $admin = User::where('email', 'admin@jemputaman.test')->first();

        $anakList = Anak::visibleTo($admin)->get();

        $this->assertCount(4, $anakList);
    }

    /**
     * Pengemudi hanya melihat data penjemputan miliknya sendiri.
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
     * Penerima hanya melihat penjemputan yang ditujukan kepada dirinya.
     */
    public function test_penerima_hanya_melihat_penjemputan_yang_ditujukan_ke_dirinya(): void
    {
        $penerima1 = User::where('email', 'penerima1@jemputaman.test')->first();

        $penjemputanList = Penjemputan::visibleTo($penerima1)->get();

        $this->assertNotEmpty($penjemputanList);
        foreach ($penjemputanList as $penjemputan) {
            $this->assertEquals($penerima1->id, $penjemputan->penerima->user_id);
        }
    }
}
