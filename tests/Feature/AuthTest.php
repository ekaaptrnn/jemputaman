<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * T-01: Login berhasil untuk tiap peran dan diarahkan ke /dashboard.
     */
    public function test_login_berhasil_untuk_tiap_peran_dan_diarahkan_ke_dashboard(): void
    {
        $accounts = [
            'admin@jemputaman.test',
            'ortu1@jemputaman.test',
            'ortu2@jemputaman.test',
            'driver1@jemputaman.test',
            'driver2@jemputaman.test',
            'penerima1@jemputaman.test',
            'penerima2@jemputaman.test',
        ];

        foreach ($accounts as $email) {
            $response = $this->post('/login', [
                'email' => $email,
                'password' => 'password',
            ]);

            $response->assertRedirect('/dashboard');
            $this->assertAuthenticated();

            $this->post('/logout');
            $this->assertGuest();
        }
    }

    /**
     * Login gagal jika password salah.
     */
    public function test_login_gagal_dengan_password_salah(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'admin@jemputaman.test',
            'password' => 'passwordsalah',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Tamu yang membuka /dashboard diarahkan ke /login.
     */
    public function test_tamu_yang_membuka_dashboard_diarahkan_ke_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * Logout mengakhiri sesi.
     */
    public function test_logout_mengakhiri_sesi(): void
    {
        $user = User::where('email', 'admin@jemputaman.test')->first();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
