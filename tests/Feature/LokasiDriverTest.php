<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\Jadwal;
use App\Models\LokasiDriver;
use App\Models\Pengemudi;
use App\Models\Penjemputan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 3 — Live GPS Tracking.
 * T-05: endpoint lokasi mencatat koordinat; plus penolakan (status salah, driver lain, ortu, validasi, throttle)
 * dan akses endpoint lokasi-terkini / halaman lacak / dashboard.
 */
class LokasiDriverTest extends TestCase
{
    use RefreshDatabase;

    private User $driverUser;
    private Pengemudi $pengemudi;
    private User $ortu;
    private Anak $anak;
    private Jadwal $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driverUser = User::factory()->pengemudi()->create();
        $this->pengemudi = Pengemudi::factory()->create(['user_id' => $this->driverUser->id]);
        $this->ortu = User::factory()->orangTua()->create();
        $this->anak = Anak::factory()->create(['orang_tua_id' => $this->ortu->id]);
        $this->jadwal = Jadwal::factory()->create([
            'anak_id' => $this->anak->id,
            'pengemudi_id' => $this->pengemudi->id,
            'sekolah_id' => $this->anak->sekolah_id,
        ]);
    }

    private function buatPenjemputan(string $status = 'dalam_perjalanan'): Penjemputan
    {
        return Penjemputan::factory()->create([
            'jadwal_id' => $this->jadwal->id,
            'anak_id' => $this->anak->id,
            'pengemudi_id' => $this->pengemudi->id,
            'tanggal' => today()->toDateString(),
            'status' => $status,
        ]);
    }

    private function koordinat(array $override = []): array
    {
        return array_merge(['latitude' => -7.5666, 'longitude' => 110.8167], $override);
    }

    // ------------------------------------------------------------------ POST lokasi

    /** T-05 */
    public function test_pengemudi_pemilik_perjalanan_mencatat_koordinat(): void
    {
        $p = $this->buatPenjemputan();

        $this->actingAs($this->driverUser)
            ->postJson(route('penjemputan.lokasi.store', $p), $this->koordinat())
            ->assertCreated()
            ->assertJsonPath('latitude', -7.5666)
            ->assertJsonPath('longitude', 110.8167);

        $this->assertDatabaseCount('lokasi_driver', 1);
        $this->assertDatabaseHas('lokasi_driver', ['penjemputan_id' => $p->id]);

        $tersimpan = LokasiDriver::first();
        $this->assertEqualsWithDelta(-7.5666, $tersimpan->latitude, 0.00001);
        $this->assertEqualsWithDelta(110.8167, $tersimpan->longitude, 0.00001);
        $this->assertNotNull($tersimpan->dicatat_pada);
    }

    public function test_koordinat_ditolak_jika_status_bukan_dalam_perjalanan(): void
    {
        foreach (['dijadwalkan', 'menuju_sekolah', 'diserahkan', 'tiba', 'selesai', 'bermasalah'] as $status) {
            $p = $this->buatPenjemputan($status);

            $this->actingAs($this->driverUser)
                ->postJson(route('penjemputan.lokasi.store', $p), $this->koordinat())
                ->assertStatus(422);
        }

        $this->assertDatabaseCount('lokasi_driver', 0);
    }

    public function test_pengemudi_lain_tidak_boleh_mengirim_koordinat(): void
    {
        $p = $this->buatPenjemputan();

        $driverLain = User::factory()->pengemudi()->create();
        Pengemudi::factory()->create(['user_id' => $driverLain->id]);

        $this->actingAs($driverLain)
            ->postJson(route('penjemputan.lokasi.store', $p), $this->koordinat())
            ->assertForbidden();

        $this->assertDatabaseCount('lokasi_driver', 0);
    }

    public function test_orang_tua_tidak_boleh_mengirim_koordinat(): void
    {
        $p = $this->buatPenjemputan();

        $this->actingAs($this->ortu)
            ->postJson(route('penjemputan.lokasi.store', $p), $this->koordinat())
            ->assertForbidden();

        $this->assertDatabaseCount('lokasi_driver', 0);
    }

    public function test_tamu_tidak_boleh_mengirim_koordinat(): void
    {
        $p = $this->buatPenjemputan();

        $this->postJson(route('penjemputan.lokasi.store', $p), $this->koordinat())
            ->assertUnauthorized();
    }

    public function test_validasi_rentang_koordinat(): void
    {
        $p = $this->buatPenjemputan();

        $kasus = [
            ['latitude' => 90.1],
            ['latitude' => -90.1],
            ['longitude' => 180.1],
            ['longitude' => -180.1],
            ['latitude' => 'abc'],
            ['longitude' => null],
        ];

        foreach ($kasus as $override) {
            $kolom = array_key_first($override);

            $this->actingAs($this->driverUser)
                ->postJson(route('penjemputan.lokasi.store', $p), $this->koordinat($override))
                ->assertStatus(422)
                ->assertJsonValidationErrors($kolom);
        }

        $this->assertDatabaseCount('lokasi_driver', 0);
    }

    public function test_koordinat_batas_sah_diterima(): void
    {
        $p = $this->buatPenjemputan();

        $this->actingAs($this->driverUser)
            ->postJson(route('penjemputan.lokasi.store', $p), ['latitude' => 90, 'longitude' => -180])
            ->assertCreated();
    }

    public function test_pengiriman_koordinat_dibatasi_throttle(): void
    {
        $p = $this->buatPenjemputan();
        $this->actingAs($this->driverUser);

        for ($i = 0; $i < 20; $i++) {
            $this->postJson(route('penjemputan.lokasi.store', $p), $this->koordinat())->assertCreated();
        }

        $this->postJson(route('penjemputan.lokasi.store', $p), $this->koordinat())->assertStatus(429);
        $this->assertDatabaseCount('lokasi_driver', 20);
    }

    // ------------------------------------------------------------------ GET lokasi-terkini

    public function test_orang_tua_pemilik_mendapat_koordinat_terakhir(): void
    {
        $p = $this->buatPenjemputan();
        LokasiDriver::factory()->create(['penjemputan_id' => $p->id, 'latitude' => -7.5600, 'longitude' => 110.8200, 'dicatat_pada' => now()->subMinutes(2)]);
        LokasiDriver::factory()->create(['penjemputan_id' => $p->id, 'latitude' => -7.5700, 'longitude' => 110.8300, 'dicatat_pada' => now()->subMinute()]);

        $this->actingAs($this->ortu)
            ->getJson(route('penjemputan.lokasi.terkini', $p))
            ->assertOk()
            ->assertJsonPath('aktif', true)
            ->assertJsonPath('status', 'dalam_perjalanan')
            ->assertJsonPath('lokasi.latitude', -7.57)
            ->assertJsonPath('lokasi.longitude', 110.83);
    }

    public function test_lokasi_terkini_null_jika_belum_ada_titik(): void
    {
        $p = $this->buatPenjemputan('diserahkan');

        $this->actingAs($this->ortu)
            ->getJson(route('penjemputan.lokasi.terkini', $p))
            ->assertOk()
            ->assertJsonPath('aktif', false)
            ->assertJsonPath('lokasi', null);
    }

    public function test_lokasi_terkini_bisa_membawa_jejak(): void
    {
        $p = $this->buatPenjemputan();
        LokasiDriver::factory()->count(3)->create(['penjemputan_id' => $p->id]);

        $this->actingAs($this->ortu)
            ->getJson(route('penjemputan.lokasi.terkini', [$p, 'jejak' => 1]))
            ->assertOk()
            ->assertJsonCount(3, 'jejak');
    }

    public function test_orang_tua_lain_tidak_boleh_melihat_lokasi_terkini(): void
    {
        $p = $this->buatPenjemputan();
        LokasiDriver::factory()->create(['penjemputan_id' => $p->id]);

        $ortuLain = User::factory()->orangTua()->create();
        Anak::factory()->create(['orang_tua_id' => $ortuLain->id]);

        $this->actingAs($ortuLain)
            ->getJson(route('penjemputan.lokasi.terkini', $p))
            ->assertForbidden();
    }

    public function test_guru_dan_admin_boleh_melihat_lokasi_terkini(): void
    {
        $p = $this->buatPenjemputan();

        $this->actingAs(User::factory()->guru()->create())
            ->getJson(route('penjemputan.lokasi.terkini', $p))
            ->assertOk();

        $this->actingAs(User::factory()->admin()->create())
            ->getJson(route('penjemputan.lokasi.terkini', $p))
            ->assertOk();
    }

    public function test_tamu_tidak_boleh_melihat_lokasi_terkini(): void
    {
        $p = $this->buatPenjemputan();

        $this->getJson(route('penjemputan.lokasi.terkini', $p))->assertUnauthorized();
    }

    // ------------------------------------------------------------------ Halaman lacak

    public function test_orang_tua_pemilik_bisa_membuka_halaman_lacak(): void
    {
        $p = $this->buatPenjemputan();

        $this->actingAs($this->ortu)
            ->get(route('penjemputan.lacak', $p))
            ->assertOk()
            ->assertSee($p->kode_perjalanan)
            ->assertSee('leaflet');
    }

    public function test_guru_bisa_membuka_halaman_lacak(): void
    {
        $p = $this->buatPenjemputan();

        $this->actingAs(User::factory()->guru()->create())
            ->get(route('penjemputan.lacak', $p))
            ->assertOk();
    }

    public function test_orang_tua_lain_dan_pengemudi_tidak_boleh_membuka_halaman_lacak(): void
    {
        $p = $this->buatPenjemputan();

        $this->actingAs(User::factory()->orangTua()->create())
            ->get(route('penjemputan.lacak', $p))
            ->assertForbidden();

        $this->actingAs($this->driverUser)
            ->get(route('penjemputan.lacak', $p))
            ->assertForbidden();
    }

    // ------------------------------------------------------------------ Dashboard per peran

    public function test_dashboard_orang_tua_menampilkan_tombol_lacak_saat_dalam_perjalanan(): void
    {
        $p = $this->buatPenjemputan('dalam_perjalanan');

        $this->actingAs($this->ortu)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('penjemputan.lacak', $p), false);
    }

    public function test_dashboard_orang_tua_tanpa_tombol_lacak_jika_belum_berangkat(): void
    {
        $p = $this->buatPenjemputan('diserahkan');

        $this->actingAs($this->ortu)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('penjemputan.lacak', $p), false);
    }

    public function test_dashboard_driver_menampilkan_tugas_berikutnya(): void
    {
        $p = $this->buatPenjemputan('menuju_sekolah');

        $this->actingAs($this->driverUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Tugas Harian Anda')
            ->assertSee(route('driver.penjemputan.show', $p), false);
    }

    public function test_dashboard_admin_dan_guru_menampilkan_rekap_perjalanan(): void
    {
        $this->buatPenjemputan('dalam_perjalanan');

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Sedang Dalam Perjalanan');

        $this->actingAs(User::factory()->guru()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pemantauan Gerbang Piket');
    }
}
