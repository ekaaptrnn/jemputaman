<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\Jadwal;
use App\Models\Pengemudi;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Admin dapat melihat daftar siswa dan pratinjau kartu QR.
     */
    public function test_admin_dapat_melihat_daftar_siswa_dan_halaman_cetak_kartu_qr(): void
    {
        $admin = User::where('email', 'admin@jemputaman.test')->first();
        $anak = Anak::first();

        $response = $this->actingAs($admin)->get(route('admin.anak.index'));
        $response->assertStatus(200);
        $response->assertSee($anak->nama);

        $kartuResponse = $this->actingAs($admin)->get(route('admin.anak.kartu', $anak));
        $kartuResponse->assertStatus(200);
        $kartuResponse->assertSee($anak->kode_qr);
    }

    /**
     * Admin dapat menambahkan siswa baru dengan kode QR unik.
     */
    public function test_admin_dapat_menambahkan_siswa_baru(): void
    {
        $admin = User::where('email', 'admin@jemputaman.test')->first();
        $ortu = User::where('role', 'orang_tua')->first();
        $sekolah = Sekolah::first();

        $response = $this->actingAs($admin)->post(route('admin.anak.store'), [
            'nama' => 'Santoso Pratama',
            'kelas' => '2B',
            'orang_tua_id' => $ortu->id,
            'sekolah_id' => $sekolah->id,
            'kode_qr' => 'QR-SANTOSO-999',
        ]);

        $response->assertRedirect(route('admin.anak.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('anak', [
            'nama' => 'Santoso Pratama',
            'kode_qr' => 'QR-SANTOSO-999',
        ]);
    }

    /**
     * Admin dapat mengubah data siswa.
     */
    public function test_admin_dapat_mengubah_data_siswa(): void
    {
        $admin = User::where('email', 'admin@jemputaman.test')->first();
        $anak = Anak::first();

        $response = $this->actingAs($admin)->put(route('admin.anak.update', $anak), [
            'nama' => 'Nama Siswa Terupdate',
            'kelas' => '6A',
            'orang_tua_id' => $anak->orang_tua_id,
            'sekolah_id' => $anak->sekolah_id,
            'kode_qr' => $anak->kode_qr,
        ]);

        $response->assertRedirect(route('admin.anak.index'));
        $this->assertDatabaseHas('anak', [
            'id' => $anak->id,
            'nama' => 'Nama Siswa Terupdate',
            'kelas' => '6A',
        ]);
    }

    /**
     * Admin dapat menghapus data siswa.
     */
    public function test_admin_dapat_menghapus_siswa(): void
    {
        $admin = User::where('email', 'admin@jemputaman.test')->first();
        $ortu = User::where('role', 'orang_tua')->first();
        $sekolah = Sekolah::first();

        $anak = Anak::create([
            'nama' => 'Siswa Dihapus',
            'kelas' => '1A',
            'orang_tua_id' => $ortu->id,
            'sekolah_id' => $sekolah->id,
            'kode_qr' => 'QR-DELETE-ME',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.anak.destroy', $anak));
        $response->assertRedirect(route('admin.anak.index'));

        $this->assertDatabaseMissing('anak', [
            'id' => $anak->id,
        ]);
    }

    /**
     * Admin dapat menambah pengemudi dan armada baru.
     */
    public function test_admin_dapat_menambah_pengemudi_dan_armada(): void
    {
        $admin = User::where('email', 'admin@jemputaman.test')->first();

        $response = $this->actingAs($admin)->post(route('admin.pengemudi.store'), [
            'name' => 'Driver Baru Santoso',
            'email' => 'driverbaru@jemputaman.test',
            'password' => 'password123',
            'no_hp' => '089999999999',
            'no_kendaraan' => 'AD 9999 ZZ',
            'jenis_kendaraan' => 'Suzuki APV Sekolah',
            'aktif' => '1',
        ]);

        $response->assertRedirect(route('admin.pengemudi.index'));
        $this->assertDatabaseHas('users', ['email' => 'driverbaru@jemputaman.test']);
        $this->assertDatabaseHas('pengemudi', ['no_kendaraan' => 'AD 9999 ZZ']);
    }

    /**
     * Admin dapat menambah jadwal rutin.
     */
    public function test_admin_dapat_menambah_jadwal_rutin(): void
    {
        $admin = User::where('email', 'admin@jemputaman.test')->first();
        $anak = Anak::first();
        $pengemudi = Pengemudi::first();
        $sekolah = Sekolah::first();

        $response = $this->actingAs($admin)->post(route('admin.jadwal.store'), [
            'anak_id' => $anak->id,
            'pengemudi_id' => $pengemudi->id,
            'sekolah_id' => $sekolah->id,
            'hari' => 'Senin - Kamis',
            'jam_jemput' => '14:00',
            'titik_jemput' => 'Lobi Selatan',
            'titik_tujuan' => 'Jl. Veteran No. 10',
            'aktif' => '1',
        ]);

        $response->assertRedirect(route('admin.jadwal.index'));
        $this->assertDatabaseHas('jadwal', [
            'anak_id' => $anak->id,
            'jam_jemput' => '14:00',
        ]);
    }

    /**
     * Non-admin ditolak mengakses rute CRUD admin (HTTP 403).
     */
    public function test_non_admin_ditolak_mengakses_crud_admin(): void
    {
        $ortu = User::where('email', 'ortu1@jemputaman.test')->first();
        $driver = User::where('email', 'driver1@jemputaman.test')->first();

        $this->actingAs($ortu)->get(route('admin.anak.create'))->assertStatus(403);
        $this->actingAs($ortu)->get(route('admin.pengemudi.index'))->assertStatus(403);
        $this->actingAs($driver)->get(route('admin.jadwal.create'))->assertStatus(403);
    }
}
