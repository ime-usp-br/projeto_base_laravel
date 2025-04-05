<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Mockery;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

	 protected $seed = true;


    /**
     * Testa se a tela de login pode ser renderizada.
     *
     * @return void
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);

        $response->assertSee('Login com Senha Única USP');
        $response->assertSee('Login com Email/Senha Local');
    }

    /**
     * Testa se usuários podem se autenticar usando a tela de login com senha local.
     *
     * @return void
     */
    public function test_users_can_authenticate_using_the_login_screen_with_local_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);

        $response->assertRedirect('/');
    }

    /**
     * Testa se usuários não podem se autenticar com senha inválida.
     *
     * @return void
     */
    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    /**
     * Testa se usuários não podem se autenticar com e-mail inexistente.
     *
     * @return void
     */
    public function test_users_can_not_authenticate_with_non_existent_email(): void
    {
        $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();

        $this->withViewErrors(['email' => __('auth.failed')]);
    }


    /**
     * Testa se usuários podem fazer logout.
     *
     * @return void
     */
    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    /**
     * Testa se o login via Senha Única redireciona corretamente.
     *
     * @return void
     */
    public function test_senha_unica_login_redirects_correctly(): void
    {
        $providerMock = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $providerMock->shouldReceive('redirect')->andReturn(redirect('http://fake-senhaunica-redirect-url'));

        Socialite::shouldReceive('driver')->with('senhaunica')->andReturn($providerMock);

        $response = $this->get('/socialite/login');

        $response->assertStatus(302);
        $response->assertRedirect('http://fake-senhaunica-redirect-url');
    }

    /**
     * Limpa o Mockery após cada teste.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}