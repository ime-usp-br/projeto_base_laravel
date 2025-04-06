<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $seed = true;

    /**
     * Testa se a tela de login pode ser renderizada corretamente.
     * Verifica a presença de elementos chave.
     *
     * @return void
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                $browser->visit('/login')
                        ->assertPathIs('/login')
                        ->assertSee('E-mail')
                        ->assertSee('Senha')
                        ->assertSee('Entrar')
                        ->assertSeeLink('Login com Senha Única')
                        ->assertSeeLink('Esqueceu sua senha?')
                        ->assertSeeLink('Registrar-se')
                        ->assertSeeLink('Definir senha local USP');
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });
    }

    /**
     * Testa se um usuário pode autenticar com credenciais locais válidas.
     *
     * @return void
     */
    public function test_user_can_login_with_local_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'local@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                    ->assertPathIs('/login');
            try {
                 $browser->type('@text-input-email', $user->email)
                        ->type('@text-input-password', 'password')
                        ->press('@primary-button-entrar')
                        ->waitForLocation('/')
                        ->assertPathIs('/')
                        ->assertAuthenticatedAs($user);
            } catch (\Throwable $e) {
                 $this->captureBrowserHtml($browser, $e);
            }
        });
    }

    /**
     * Testa se um usuário não pode autenticar com senha local inválida.
     *
     * @return void
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
             $browser->visit('/login')
                    ->assertPathIs('/login');
            try {
                $browser->type('@text-input-email', $user->email)
                        ->type('@text-input-password', 'wrong-password')
                        ->press('@primary-button-entrar')
                        ->assertPathIs('/login')
                        ->waitForText(__('auth.failed'), 5)
                        ->assertSee(__('auth.failed'))
                        ->assertGuest();
            } catch (\Throwable $e) {
                 $this->captureBrowserHtml($browser, $e);
            }
        });
    }

    /**
     * Testa se um usuário não pode autenticar com e-mail local inexistente.
     *
     * @return void
     */
    public function test_user_cannot_login_with_non_existent_email(): void
    {
        $this->browse(function (Browser $browser) {
             $browser->visit('/login')
                    ->assertPathIs('/login');
            try {
                 $browser->type('@text-input-email', 'nonexistent@example.com')
                        ->type('@text-input-password', 'password')
                        ->press('@primary-button-entrar')
                        ->waitForText(__('auth.failed'), 5)
                        ->assertPathIs('/login')
                        ->assertSee(__('auth.failed'))
                        ->assertGuest();
            } catch (\Throwable $e) {
                 $this->captureBrowserHtml($browser, $e);
            }
        });
    }

    /**
     * Testa o fluxo de login via Senha Única USP utilizando o serviço Faker.
     *
     * @return void
     */
    public function test_user_can_login_via_senha_unica_faker(): void
    {
        $testNusp = '12345';

        $this->browse(function (Browser $browser) use ($testNusp) {
             $browser->visit('/login')
                    ->assertPathIs('/login');
            try {
                 $browser->waitForLink('Login com Senha Única')
                        ->clickLink('Login com Senha Única')
                        ->waitForLocation('/wsusuario/oauth/authorize', 10)
                        ->assertUrlIs(config('senhaunica.dev').'/authorize')
                        ->assertSee('Utilize os códigos abaixo')
                        ->type('#loginUsuario', $testNusp)
                        ->press('Login')
                        ->waitForLocation('/')
                        ->assertPathIs('/')
                        ->assertAuthenticated();
            } catch (\Throwable $e) {
                 $this->captureBrowserHtml($browser, $e);
            }
        });

        $this->assertDatabaseHas('users', [
            'codpes' => $testNusp,
        ]);
    }

    /**
     * Testa se um usuário autenticado pode fazer logout.
     *
     * @return void
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->browse(function (Browser $browser) use ($user) {
            try {
                $browser->loginAs($user)
                        ->visit('/dashboard')
                        ->assertPathIs('/dashboard')
                        ->click('#usp-theme-logout-link') // Clica no link (o JS deve interceptar e enviar POST)
                        ->waitForLocation('/')
                        ->assertPathIs('/')
                        ->assertGuest();
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });
    }
}