<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;

class RegistrationTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $seed = true;

    /**
     * Prepara o ambiente de teste antes de cada método.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'external_user', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'usp_user', 'guard_name' => 'web']);
    }

    /**
     * Testa se a tela de registro pode ser renderizada.
     *
     * @return void
     */
    public function test_registration_screen_can_be_rendered(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                $browser->visit('/register')
                        ->assertPathIs('/register')
                        ->assertPresent('@primary-button-registrar')
                        ->assertSee('Nome Completo')

                        ->assertSee('E-mail')
                        ->assertSee('Tipo de Usuário')
                        ->assertSee('Externo')
                        ->assertSee('Comunidade USP')
                        ->assertSee('Senha')
                        ->assertSee('Confirmar Senha');
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });
    }

    /**
     * Testa se um novo usuário externo pode se registrar com sucesso.
     *
     * @return void
     */
    public function test_new_external_user_can_register(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->assertPathIs('/register');
            try {
                $browser->type('@text-input-name', 'External User Test')
                        ->type('@text-input-email', 'external.test@example.com')
                        ->type('@text-input-password', 'password123')
                        ->type('@text-input-password_confirmation', 'password123')
                        ->radio('user_type', 'external')
                        ->press('@primary-button-registrar')
                        ->waitForLocation('/verify-email')
                        ->assertPathIs('/verify-email');
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });

        $this->assertDatabaseHas('users', [
            'email' => 'external.test@example.com',
            'codpes' => null,
        ]);
        $user = User::where('email', 'external.test@example.com')->first();
        $this->assertTrue($user->hasRole('external_user'));
        $this->assertNull($user->email_verified_at);
    }

    /**
     * Testa se um novo usuário USP pode se registrar com sucesso.
     *
     * @return void
     */
    public function test_new_usp_user_can_register(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->assertPathIs('/register');
            try {
                 $browser->type('@text-input-name', 'USP User Test')
                        ->type('@text-input-email', 'usp.test@usp.br')
                        ->type('@text-input-password', 'password123')
                        ->type('@text-input-password_confirmation', 'password123')
                        ->radio('user_type', 'usp')
                        ->waitFor('@text-input-codpes')
                        ->type('@text-input-codpes', '7654321')
                        ->press('@primary-button-registrar')
                        ->waitForLocation('/verify-email')
                        ->assertPathIs('/verify-email');
            } catch (\Throwable $e) {
                 $this->captureBrowserHtml($browser, $e);
            }
        });

        $this->assertDatabaseHas('users', [
            'email' => 'usp.test@usp.br',
            'codpes' => '7654321',
        ]);
        $user = User::where('email', 'usp.test@usp.br')->first();
        $this->assertTrue($user->hasRole('usp_user'));
        $this->assertNull($user->email_verified_at);
    }

    /**
     * Testa erros de validação no registro (senha não confere).
     *
     * @return void
     */
    public function test_registration_validation_password_mismatch(): void
    {
        $this->browse(function (Browser $browser) {
             $browser->visit('/register')
                    ->assertPathIs('/register');
            try {
                $browser->type('@text-input-name', 'Validation Test')
                        ->type('@text-input-email', 'validation@example.com')
                        ->type('@text-input-password', 'password123')
                        ->type('@text-input-password_confirmation', 'password456')
                        ->radio('user_type', 'external')
                        ->press('@primary-button-registrar')
                        ->assertPathIs('/register')
                        ->waitForText(__('validation.confirmed', ['attribute' => 'Senha']), 5)
                        ->assertSee(__('validation.confirmed', ['attribute' => 'Senha']));
            } catch (\Throwable $e) {
                 $this->captureBrowserHtml($browser, $e);
            }
        });
    }

    /**
     * Testa erros de validação no registro (email inválido para tipo USP).
     *
     * @return void
     */
    public function test_registration_validation_invalid_email_for_usp_type(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->assertPathIs('/register');
            try {
                $browser->type('@text-input-name', 'USP Validation Test')
                        ->type('@text-input-email', 'invalid.usp@example.com')
                        ->type('@text-input-password', 'password123')
                        ->type('@text-input-password_confirmation', 'password123')
                        ->radio('user_type', 'usp')
                        ->waitFor('@text-input-codpes')
                        ->type('@text-input-codpes', '1122334')
                        ->press('@primary-button-registrar')
                        ->waitForText(__('validation.custom.email_must_end_with_usp'), 5)
                        ->assertPathIs('/register')
                        ->assertSee(__('validation.custom.email_must_end_with_usp'));
            } catch (\Throwable $e) {
                 $this->captureBrowserHtml($browser, $e);
            }
        });
    }

    /**
     * Testa erros de validação no registro (CodPes ausente para tipo USP).
     *
     * @return void
     */
    public function test_registration_validation_missing_codpes_for_usp_type(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->assertPathIs('/register');
            try {
                $browser->type('@text-input-name', 'USP Validation Test')
                        ->type('@text-input-email', 'valid.usp@usp.br')
                        ->type('@text-input-password', 'password123')
                        ->type('@text-input-password_confirmation', 'password123')
                        ->radio('user_type', 'usp')
                        ->waitFor('@text-input-codpes')
                        ->type('@text-input-codpes', '')
                        ->press('@primary-button-registrar')

                        ->waitForText(__('validation.custom.codpes_required_for_usp'), 5)
                        ->assertPathIs('/register')
                        ->assertSee(__('validation.custom.codpes_required_for_usp'));
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });
    }
}