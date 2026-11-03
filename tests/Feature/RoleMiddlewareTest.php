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
            return response('OK', 200);
        });
    }

    /**
     * Admin berhak mengakses route dengan role admin (HTTP 200).
     */
    public function test_admin_menerima_status_200(): void
    {
        $admin = User::where('email', 'admin@jemputaman.test')->first();

        $response = $this->actingAs($admin)->get('/test-role-admin');

        $response->assertStatus(200);
        $response->assertSee('OK');
    }

    /**
     * Orang tua ditolak mengakses route khusus admin (HTTP 403).
     */
    public function test_orang_tua_menerima_status_403(): void
    {
        $ortu = User::where('email', 'ortu1@jemputaman.test')->first();

        $response = $this->actingAs($ortu)->get('/test-role-admin');

        $response->assertStatus(403);
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
