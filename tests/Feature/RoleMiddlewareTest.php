<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        Route::middleware(['web', 'auth', 'role:admin'])->get('/test-role-admin', function () {
            return response('OK Admin', 200);
        });

        Route::middleware(['web', 'auth', 'role:guru,admin'])->get('/test-role-guru', function () {
            return response('OK Guru', 200);
        });

        Route::middleware(['web', 'auth', 'role:orang_tua'])->get('/test-role-ortu', function () {
            return response('OK Ortu', 200);
        });

        Route::middleware(['web', 'auth', 'role:pengemudi'])->get('/test-role-driver', function () {
            return response('OK Pengemudi', 200);
        });
    }

    /**
     * T-02: Admin berhak mengakses route role:admin (HTTP 200).
     */
    public function test_admin_menerima_status_200(): void
    {
        $admin = User::where('email', 'admin@jemputaman.test')->first();

        $response = $this->actingAs($admin)->get('/test-role-admin');

        $response->assertStatus(200);
        $response->assertSee('OK Admin');
    }

    /**
     * Guru dan Admin berhak mengakses route role:guru,admin (HTTP 200).
     */
    public function test_guru_dan_admin_dapat_akses_route_guru(): void
    {
        $guru = User::where('email', 'guru@jemputaman.test')->first();
        $admin = User::where('email', 'admin@jemputaman.test')->first();

        $this->actingAs($guru)->get('/test-role-guru')->assertStatus(200)->assertSee('OK Guru');
        $this->actingAs($admin)->get('/test-role-guru')->assertStatus(200)->assertSee('OK Guru');
    }

    /**
     * Orang tua ditolak mengakses route khusus admin (HTTP 403).
     */
    public function test_orang_tua_ditolak_akses_route_admin(): void
    {
        $ortu = User::where('email', 'ortu1@jemputaman.test')->first();

        $response = $this->actingAs($ortu)->get('/test-role-admin');

        $response->assertStatus(403);
    }

    /**
     * Pengemudi dapat mengakses route khusus pengemudi, ditolak di route orang tua.
     */
    public function test_pengemudi_akses_route_driver(): void
    {
        $driver = User::where('email', 'driver1@jemputaman.test')->first();

        $this->actingAs($driver)->get('/test-role-driver')->assertStatus(200)->assertSee('OK Pengemudi');
        $this->actingAs($driver)->get('/test-role-ortu')->assertStatus(403);
    }

    /**
     * Tamu diarahkan ke /login jika mengakses route yang dilindungi.
     */
    public function test_tamu_diarahkan_ke_login(): void
    {
        $response = $this->get('/test-role-admin');

        $response->assertRedirect('/login');
    }
}
