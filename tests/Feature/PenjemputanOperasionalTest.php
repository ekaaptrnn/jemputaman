<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\Jadwal;
use App\Models\Pengemudi;
use App\Models\Penjemputan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Fase 2 — alur operasional pengemudi (T-04, T-07, T-10) + proteksi foto bukti.
 * Catatan: UploadedFile::fake()->image() membutuhkan ekstensi PHP GD.
 */
class PenjemputanOperasionalTest extends TestCase
{
    use RefreshDatabase;

    private User $driverUser;
    private Pengemudi $pengemudi;
    private Anak $anak;
    private Jadwal $jadwal;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->driverUser = User::factory()->pengemudi()->create();
        $this->pengemudi = Pengemudi::factory()->create(['user_id' => $this->driverUser->id]);
        $this->anak = Anak::factory()->create(['kode_qr' => 'QR-TEST-0001']);
        $this->jadwal = Jadwal::factory()->create([
            'anak_id' => $this->anak->id,
            'pengemudi_id' => $this->pengemudi->id,
            'sekolah_id' => $this->anak->sekolah_id,
        ]);
    }

    private function buatPenjemputan(string $status = 'dijadwalkan', ?string $tanggal = null, array $extra = []): Penjemputan
    {
        return Penjemputan::factory()->create(array_merge([
            'jadwal_id' => $this->jadwal->id,
            'anak_id' => $this->anak->id,
            'pengemudi_id' => $this->pengemudi->id,
            'tanggal' => $tanggal ?? today()->toDateString(),
            'status' => $status,
        ], $extra));
    }

    private function foto(): UploadedFile
    {
        return UploadedFile::fake()->image('bukti.jpg', 400, 300);
    }

    public function test_daftar_tugas_hanya_menampilkan_jemputan_hari_ini_milik_driver_sendiri(): void
    {
        $milikSaya = $this->buatPenjemputan();

        $driverLain = Pengemudi::factory()->create();
        $anakLain = Anak::factory()->create(['nama' => 'Anak Driver Lain']);
        $jadwalLain = Jadwal::factory()->create([
            'anak_id' => $anakLain->id,
            'pengemudi_id' => $driverLain->id,
            'sekolah_id' => $anakLain->sekolah_id,
        ]);
        Penjemputan::factory()->create([
            'jadwal_id' => $jadwalLain->id,
            'anak_id' => $anakLain->id,
            'pengemudi_id' => $driverLain->id,
            'tanggal' => today()->toDateString(),
        ]);

        $this->actingAs($this->driverUser)
            ->get(route('driver.penjemputan.index'))
            ->assertOk()
            ->assertSee($milikSaya->kode_perjalanan)
            ->assertDontSee('Anak Driver Lain');
    }

    public function test_driver_berangkat_ke_sekolah_dan_riwayat_tercatat(): void
    {
        $p = $this->buatPenjemputan('dijadwalkan');

        $this->actingAs($this->driverUser)
            ->post(route('driver.penjemputan.berangkat', $p))
            ->assertRedirect(route('driver.penjemputan.show', $p));

        $this->assertSame('menuju_sekolah', $p->fresh()->status);
        $this->assertDatabaseHas('riwayat_status', [
            'penjemputan_id' => $p->id,
            'diubah_oleh' => $this->driverUser->id,
            'status' => 'menuju_sekolah',
        ]);
    }

    /** T-04 */
    public function test_serah_terima_dengan_qr_valid_dan_foto_mengubah_status_ke_diserahkan(): void
    {
        $p = $this->buatPenjemputan('menuju_sekolah');

        $this->actingAs($this->driverUser)
            ->post(route('driver.penjemputan.serah-terima', $p), [
                'kode_qr' => 'QR-TEST-0001',
                'foto' => $this->foto(),
            ])
            ->assertRedirect(route('driver.penjemputan.show', $p))
            ->assertSessionHasNoErrors();

        $p->refresh();
        $this->assertSame('diserahkan', $p->status);
        $this->assertNotNull($p->waktu_dijemput);
        $this->assertNotNull($p->foto_serah_sekolah);
        Storage::disk('local')->assertExists($p->foto_serah_sekolah);
        $this->assertDatabaseHas('riwayat_status', ['penjemputan_id' => $p->id, 'status' => 'diserahkan']);
    }

    public function test_qr_yang_salah_ditolak_dan_tidak_ada_file_tersimpan(): void
    {
        $p = $this->buatPenjemputan('menuju_sekolah');

        $this->actingAs($this->driverUser)
            ->post(route('driver.penjemputan.serah-terima', $p), [
                'kode_qr' => 'QR-ANAK-LAIN',
                'foto' => $this->foto(),
            ])
            ->assertSessionHasErrors('kode_qr');

        $p->refresh();
        $this->assertSame('menuju_sekolah', $p->status);
        $this->assertNull($p->foto_serah_sekolah);
        $this->assertSame([], Storage::disk('local')->allFiles('bukti_penjemputan'));
    }

    public function test_serah_terima_tanpa_foto_ditolak(): void
    {
        $p = $this->buatPenjemputan('menuju_sekolah');

        $this->actingAs($this->driverUser)
            ->post(route('driver.penjemputan.serah-terima', $p), ['kode_qr' => 'QR-TEST-0001'])
            ->assertSessionHasErrors('foto');

        $this->assertSame('menuju_sekolah', $p->fresh()->status);
    }

    public function test_serah_terima_menolak_file_yang_bukan_gambar(): void
    {
        $p = $this->buatPenjemputan('menuju_sekolah');

        $this->actingAs($this->driverUser)
            ->post(route('driver.penjemputan.serah-terima', $p), [
                'kode_qr' => 'QR-TEST-0001',
                'foto' => UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors('foto');

        $this->assertSame('menuju_sekolah', $p->fresh()->status);
    }

    public function test_driver_memulai_perjalanan_ke_rumah(): void
    {
        $p = $this->buatPenjemputan('diserahkan');

        $this->actingAs($this->driverUser)
            ->post(route('driver.penjemputan.mulai', $p))
            ->assertRedirect(route('driver.penjemputan.show', $p));

        $p->refresh();
        $this->assertSame('dalam_perjalanan', $p->status);
        $this->assertNotNull($p->waktu_berangkat);
    }

    /** T-07 */
    public function test_driver_mengunggah_foto_tiba_dan_status_menjadi_tiba(): void
    {
        $p = $this->buatPenjemputan('dalam_perjalanan');

        $this->actingAs($this->driverUser)
            ->post(route('driver.penjemputan.tiba', $p), ['foto' => $this->foto()])
            ->assertRedirect(route('driver.penjemputan.show', $p))
            ->assertSessionHasNoErrors();

        $p->refresh();
        $this->assertSame('tiba', $p->status);
        $this->assertNotNull($p->waktu_tiba);
        Storage::disk('local')->assertExists($p->foto_tiba);
        $this->assertDatabaseHas('riwayat_status', ['penjemputan_id' => $p->id, 'status' => 'tiba']);
    }

    /** T-10 */
    public function test_status_tidak_boleh_melompat_atau_mundur(): void
    {
        $this->actingAs($this->driverUser);

        // Melompat: dijadwalkan -> tiba
        $a = $this->buatPenjemputan('dijadwalkan');
        $this->post(route('driver.penjemputan.tiba', $a), ['foto' => $this->foto()])
            ->assertSessionHasErrors('status');
        $this->assertSame('dijadwalkan', $a->fresh()->status);

        // Melompat: dijadwalkan -> diserahkan
        $this->post(route('driver.penjemputan.serah-terima', $a), [
            'kode_qr' => 'QR-TEST-0001',
            'foto' => $this->foto(),
        ])->assertSessionHasErrors('status');
        $this->assertSame('dijadwalkan', $a->fresh()->status);

        // Mundur: diserahkan -> menuju_sekolah
        $b = $this->buatPenjemputan('diserahkan', null, ['kode_perjalanan' => 'JA-TEST-B']);
        $this->post(route('driver.penjemputan.berangkat', $b))->assertSessionHasErrors('status');
        $this->assertSame('diserahkan', $b->fresh()->status);

        // Tidak boleh mengubah perjalanan yang sudah ditutup
        $c = $this->buatPenjemputan('selesai', null, ['kode_perjalanan' => 'JA-TEST-C']);
        $this->post(route('driver.penjemputan.mulai', $c))->assertSessionHasErrors('status');
        $this->assertSame('selesai', $c->fresh()->status);

        $this->assertSame(0, \App\Models\RiwayatStatus::count());
        $this->assertSame([], Storage::disk('local')->allFiles('bukti_penjemputan'));
    }

    public function test_perjalanan_bukan_hari_ini_tidak_bisa_diproses(): void
    {
        $p = $this->buatPenjemputan('dijadwalkan', today()->addDay()->toDateString());

        $this->actingAs($this->driverUser)
            ->post(route('driver.penjemputan.berangkat', $p))
            ->assertSessionHasErrors('status');

        $this->assertSame('dijadwalkan', $p->fresh()->status);
    }

    public function test_driver_lain_tidak_boleh_mengubah_atau_melihat_jemputan_ini(): void
    {
        $p = $this->buatPenjemputan('dijadwalkan');
        $driverLain = User::factory()->pengemudi()->create();
        Pengemudi::factory()->create(['user_id' => $driverLain->id]);

        $this->actingAs($driverLain)->post(route('driver.penjemputan.berangkat', $p))->assertForbidden();
        $this->actingAs($driverLain)->get(route('driver.penjemputan.show', $p))->assertForbidden();

        $this->assertSame('dijadwalkan', $p->fresh()->status);
    }

    public function test_non_pengemudi_dan_tamu_ditolak_di_route_driver(): void
    {
        $p = $this->buatPenjemputan();
        $ortu = User::factory()->orangTua()->create();

        $this->actingAs($ortu)->get(route('driver.penjemputan.index'))->assertForbidden();
        $this->actingAs($ortu)->post(route('driver.penjemputan.berangkat', $p))->assertForbidden();

        auth()->logout();
        $this->get(route('driver.penjemputan.index'))->assertRedirect('/login');
    }

    public function test_foto_bukti_hanya_bisa_dibuka_pihak_berwenang(): void
    {
        Storage::disk('local')->put('bukti_penjemputan/contoh.jpg', 'isi-foto');
        $p = $this->buatPenjemputan('tiba', null, ['foto_serah_sekolah' => 'bukti_penjemputan/contoh.jpg']);

        $ortuPemilik = $this->anak->orangTua;
        $ortuLain = User::factory()->orangTua()->create();
        $url = route('penjemputan.foto', [$p, 'serah']);

        $this->actingAs($this->driverUser)->get($url)->assertOk();
        $this->actingAs($ortuPemilik)->get($url)->assertOk();
        $this->actingAs($ortuLain)->get($url)->assertForbidden();

        auth()->logout();
        $this->get($url)->assertRedirect('/login');
    }

    public function test_foto_yang_belum_ada_mengembalikan_404(): void
    {
        $p = $this->buatPenjemputan('menuju_sekolah');

        $this->actingAs($this->driverUser)
            ->get(route('penjemputan.foto', [$p, 'tiba']))
            ->assertNotFound();
    }
}
