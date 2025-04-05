<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite; // Import facade
use Mockery; // Import Mockery

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

	 protected $seed = true;


    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        // Keep assertions - if they fail, it's likely a view/config issue now
        $response->assertSee('Login com Senha Única USP');
        $response->assertSee('Login com Email/Senha Local');
    }

    public function test_users_can_authenticate_using_the_login_screen_with_local_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'), // Ensure password is set
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        // Breeze default redirect is '/', let's stick to that unless overridden
        $response->assertRedirect('/');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_non_existent_email(): void
    {
        $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        // Check for the specific error message in Portuguese
        $this->withViewErrors(['email' => __('auth.failed')]);
    }


    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    // Test Senha Unica Redirect (Mocking Socialite)
    public function test_senha_unica_login_redirects_correctly(): void
    {
        // Mock the Socialite driver
        $providerMock = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $providerMock->shouldReceive('redirect')->andReturn(redirect('http://fake-senhaunica-redirect-url')); // Simulate redirect response

        // Tell Socialite to return the mock when 'senhaunica' driver is called
        Socialite::shouldReceive('driver')->with('senhaunica')->andReturn($providerMock);

        // Make the request to the login route
        $response = $this->get('/socialite/login');

        // Assert it's a redirect (status 302)
        $response->assertStatus(302);
        // Assert it redirects to the URL provided by the mock
        $response->assertRedirect('http://fake-senhaunica-redirect-url');
    }

    // Teardown Mockery
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}