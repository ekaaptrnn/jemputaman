<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\Jadwal;
use App\Models\Notifikasi;
use App\Models\Pengemudi;
use App\Models\Penjemputan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 4 — konfirmasi serah terima orang tua (T-08, T-09) dan notifikasi dalam aplikasi (FR-13).
 */
class KonfirmasiNotifikasiTest extends TestCase
{
    use RefreshDatabase;

    private User $ortu;
    private User $guru;
    private User $admin;
    private User $driverUser;
    private Pengemudi $pengemudi;
    private Anak $anak;
    private Jadwal $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ortu = User::factory()->orangTua()->create();
        $this->guru = User::factory()->guru()->create();
        $this->admin = User::factory()->admin()->create();

        $this->driverUser = User::factory()->pengemudi()->create();
        $this->pengemudi = Pengemudi::factory()->create(['user_id' => $this->driverUser->id]);

        $this->anak = Anak::factory()->create(['orang_tua_id' => $this->ortu->id, 'nama' => 'Anak Uji']);
        $this->jadwal = Jadwal::factory()->create([
            'anak_id' => $this->anak->id,
            'pengemudi_id' => $this->pengemudi->id,
            'sekolah_id' => $this->anak->sekolah_id,
        ]);
    }

    private function buatPenjemputan(string $status = 'tiba', ?string $tanggal = null): Penjemputan
    {
        return Penjemputan::factory()->create([
            'jadwal_id' => $this->jadwal->id,
            'anak_id' => $this->anak->id,
            'pengemudi_id' => $this->pengemudi->id,
            'tanggal' => $tanggal ?? today()->toDateString(),
            'status' => $status,
        ]);
    }

    // ------------------------------------------------------------------
    // Konfirmasi orang tua
    // ------------------------------------------------------------------

    /** T-08 */
    public function test_orang_tua_mengonfirmasi_penerimaan_dan_status_menjadi_selesai(): void
    {
        $p = $this->buatPenjemputan('tiba');

        $this->actingAs($this->ortu)
            ->post(route('konfirmasi.terima', $p))
            ->assertRedirect(route('konfirmasi.show', $p))
            ->assertSessionHasNoErrors();

        $p->refresh();
        $this->assertSame('selesai', $p->status);
        $this->assertNotNull($p->waktu_konfirmasi_ortu);
        $this->assertEquals($this->ortu->id, $p->dikonfirmasi_oleh);
        $this->assertDatabaseHas('riwayat_status', [
            'penjemputan_id' => $p->id,
            'diubah_oleh' => $this->ortu->id,
            'status' => 'selesai',
        ]);
    }

    public function test_orang_tua_melaporkan_kendala_dan_status_menjadi_bermasalah(): void
    {
        $p = $this->buatPenjemputan('tiba');

        $this->actingAs($this->ortu)
            ->post(route('konfirmasi.laporkan', $p), ['catatan' => 'Anak belum diterima di rumah.'])
            ->assertRedirect(route('konfirmasi.show', $p))
            ->assertSessionHasNoErrors();

        $p->refresh();
        $this->assertSame('bermasalah', $p->status);
        $this->assertStringContainsString('Anak belum diterima di rumah.', $p->catatan);
        $this->assertNull($p->waktu_konfirmasi_ortu);
        $this->assertDatabaseHas('riwayat_status', [
            'penjemputan_id' => $p->id,
            'diubah_oleh' => $this->ortu->id,
            'status' => 'bermasalah',
        ]);
    }

    public function test_laporan_kendala_tanpa_catatan_ditolak(): void
    {
        $p = $this->buatPenjemputan('tiba');

        $this->actingAs($this->ortu)
            ->post(route('konfirmasi.laporkan', $p), ['catatan' => ''])
            ->assertSessionHasErrors('catatan');

        $this->assertSame('tiba', $p->fresh()->status);
        $this->assertDatabaseMissing('riwayat_status', ['penjemputan_id' => $p->id, 'status' => 'bermasalah']);
        $this->assertSame(0, Notifikasi::count());
    }

    /** T-09 */
    public function test_orang_tua_lain_tidak_boleh_mengonfirmasi_anak_yang_bukan_miliknya(): void
    {
        $p = $this->buatPenjemputan('tiba');
        $ortuLain = User::factory()->orangTua()->create();

        $this->actingAs($ortuLain)->get(route('konfirmasi.show', $p))->assertForbidden();
        $this->actingAs($ortuLain)->post(route('konfirmasi.terima', $p))->assertForbidden();
        $this->actingAs($ortuLain)
            ->post(route('konfirmasi.laporkan', $p), ['catatan' => 'Mencoba melapor anak orang lain.'])
            ->assertForbidden();

        $this->assertSame('tiba', $p->fresh()->status);
        $this->assertSame(0, Notifikasi::count());
    }

    public function test_peran_selain_orang_tua_tidak_boleh_mengonfirmasi(): void
    {
        $p = $this->buatPenjemputan('tiba');

        foreach ([$this->driverUser, $this->guru, $this->admin] as $user) {
            $this->actingAs($user)->post(route('konfirmasi.terima', $p))->assertForbidden();
        }

        $this->assertSame('tiba', $p->fresh()->status);
    }

    public function test_konfirmasi_ditolak_jika_status_bukan_tiba(): void
    {
        $p = $this->buatPenjemputan('dalam_perjalanan');

        $this->actingAs($this->ortu)
            ->post(route('konfirmasi.terima', $p))
            ->assertSessionHasErrors('status');

        $this->actingAs($this->ortu)
            ->post(route('konfirmasi.laporkan', $p), ['catatan' => 'Belum waktunya melapor.'])
            ->assertSessionHasErrors('status');

        $this->assertSame('dalam_perjalanan', $p->fresh()->status);
        $this->assertSame(0, Notifikasi::count());
    }

    public function test_konfirmasi_dua_kali_tidak_diproses_ulang(): void
    {
        $p = $this->buatPenjemputan('tiba');

        $this->actingAs($this->ortu)->post(route('konfirmasi.terima', $p))->assertSessionHasNoErrors();
        $this->actingAs($this->ortu)->post(route('konfirmasi.terima', $p))->assertSessionHasErrors('status');

        $this->assertSame(1, $p->riwayatStatus()->where('status', 'selesai')->count());
        $this->assertSame(1, Notifikasi::where('judul', 'Serah Terima Selesai')->count());
    }

    public function test_konfirmasi_ditolak_jika_bukan_hari_perjalanan(): void
    {
        $p = $this->buatPenjemputan('tiba', today()->subDay()->toDateString());

        $this->actingAs($this->ortu)
            ->post(route('konfirmasi.terima', $p))
            ->assertSessionHasErrors('status');

        $this->assertSame('tiba', $p->fresh()->status);
    }

    public function test_halaman_konfirmasi_menampilkan_tombol_saat_status_tiba(): void
    {
        $p = $this->buatPenjemputan('tiba');

        $this->actingAs($this->ortu)
            ->get(route('konfirmasi.show', $p))
            ->assertOk()
            ->assertSee('Anak Telah Diterima')
            ->assertSee('Laporkan Kendala');
    }

    public function test_dashboard_orang_tua_menampilkan_tautan_konfirmasi_dan_badge_notifikasi(): void
    {
        $p = $this->buatPenjemputan('tiba');
        Notifikasi::factory()->count(2)->create(['user_id' => $this->ortu->id, 'penjemputan_id' => $p->id]);

        $this->actingAs($this->ortu)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('konfirmasi.show', $p))
            ->assertSee('data-notif-badge', false);
    }

    // ------------------------------------------------------------------
    // Notifikasi tercatat saat status berubah
    // ------------------------------------------------------------------

    public function test_notifikasi_tercatat_untuk_orang_tua_saat_driver_mengubah_status(): void
    {
        $p = $this->buatPenjemputan('dijadwalkan');

        $this->actingAs($this->driverUser)->post(route('driver.penjemputan.berangkat', $p))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('notifikasi', [
            'user_id' => $this->ortu->id,
            'penjemputan_id' => $p->id,
            'judul' => 'Armada Menuju Sekolah',
            'dibaca_at' => null,
        ]);

        $p->update(['status' => 'diserahkan']);
        $this->actingAs($this->driverUser)->post(route('driver.penjemputan.mulai', $p))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('notifikasi', [
            'user_id' => $this->ortu->id,
            'penjemputan_id' => $p->id,
            'judul' => 'Perjalanan Pulang Dimulai',
        ]);
    }

    public function test_notifikasi_selesai_hanya_untuk_orang_tua_anak(): void
    {
        $p = $this->buatPenjemputan('tiba');
        $ortuLain = User::factory()->orangTua()->create();

        $this->actingAs($this->ortu)->post(route('konfirmasi.terima', $p));

        $this->assertDatabaseHas('notifikasi', ['user_id' => $this->ortu->id, 'judul' => 'Serah Terima Selesai']);
        $this->assertSame(0, Notifikasi::where('user_id', $ortuLain->id)->count());
        $this->assertSame(0, Notifikasi::where('user_id', $this->guru->id)->count());
    }

    public function test_kendala_dikabarkan_ke_guru_dan_admin(): void
    {
        $p = $this->buatPenjemputan('tiba');
        $ortuLain = User::factory()->orangTua()->create();

        $this->actingAs($this->ortu)
            ->post(route('konfirmasi.laporkan', $p), ['catatan' => 'Anak belum diterima di rumah.']);

        foreach ([$this->guru, $this->admin] as $penerima) {
            $this->assertDatabaseHas('notifikasi', [
                'user_id' => $penerima->id,
                'penjemputan_id' => $p->id,
                'judul' => 'Kendala Penjemputan Dilaporkan',
            ]);
        }

        $this->assertDatabaseHas('notifikasi', ['user_id' => $this->ortu->id, 'judul' => 'Laporan Kendala Tercatat']);
        $this->assertSame(0, Notifikasi::where('user_id', $ortuLain->id)->count());
    }

    // ------------------------------------------------------------------
    // Halaman & aksi notifikasi
    // ------------------------------------------------------------------

    public function test_tamu_tidak_bisa_membuka_notifikasi(): void
    {
        $this->get(route('notifikasi.index'))->assertRedirect(route('login'));
    }

    public function test_daftar_notifikasi_hanya_menampilkan_milik_sendiri(): void
    {
        $ortuLain = User::factory()->orangTua()->create();
        Notifikasi::factory()->create(['user_id' => $this->ortu->id, 'judul' => 'Judul Milik Sendiri']);
        Notifikasi::factory()->create(['user_id' => $ortuLain->id, 'judul' => 'Judul Milik Orang Lain']);

        $this->actingAs($this->ortu)
            ->get(route('notifikasi.index'))
            ->assertOk()
            ->assertSee('Judul Milik Sendiri')
            ->assertDontSee('Judul Milik Orang Lain');
    }

    public function test_notifikasi_bisa_ditandai_dibaca(): void
    {
        $n = Notifikasi::factory()->create(['user_id' => $this->ortu->id, 'dibaca_at' => null]);

        $this->actingAs($this->ortu)
            ->post(route('notifikasi.baca', $n))
            ->assertRedirect(route('notifikasi.index'));

        $this->assertNotNull($n->fresh()->dibaca_at);
    }

    public function test_notifikasi_orang_lain_tidak_bisa_ditandai_dibaca(): void
    {
        $ortuLain = User::factory()->orangTua()->create();
        $n = Notifikasi::factory()->create(['user_id' => $ortuLain->id, 'dibaca_at' => null]);

        $this->actingAs($this->ortu)
            ->post(route('notifikasi.baca', $n))
            ->assertForbidden();

        $this->assertNull($n->fresh()->dibaca_at);
    }

    public function test_tandai_semua_dibaca_hanya_berlaku_untuk_milik_sendiri(): void
    {
        $ortuLain = User::factory()->orangTua()->create();
        Notifikasi::factory()->count(3)->create(['user_id' => $this->ortu->id, 'dibaca_at' => null]);
        Notifikasi::factory()->count(2)->create(['user_id' => $ortuLain->id, 'dibaca_at' => null]);

        $this->actingAs($this->ortu)
            ->post(route('notifikasi.baca-semua'))
            ->assertRedirect(route('notifikasi.index'));

        $this->assertSame(0, Notifikasi::where('user_id', $this->ortu->id)->whereNull('dibaca_at')->count());
        $this->assertSame(2, Notifikasi::where('user_id', $ortuLain->id)->whereNull('dibaca_at')->count());
    }
}
