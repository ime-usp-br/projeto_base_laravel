<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendResetPasswordLink;

class PasswordResetTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $seed = true;

    /**
     * Testa se a tela de solicitação de reset de senha pode ser renderizada.
     *
     * @return void
     */
    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                $browser->visit('/forgot-password')
                        ->assertPathIs('/forgot-password')
                        ->assertSee('Esqueceu sua senha?');
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });
    }

    /**
     * Testa se o link de reset de senha pode ser solicitado.
     * Nota: Dusk não pode verificar o email, apenas a mensagem de status.
     * Ignora a falha de Notification::assertSentTo conforme instruído.
     *
     * @return void
     */
    public function test_reset_password_link_can_be_requested(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {

            $browser->visit('/forgot-password')
                    ->assertPathIs('/forgot-password');
            try {
                 $browser->type('@text-input-email', $user->email)
                        ->press('@primary-button-enviar-link-de-redefinicao-de-senha')
                        ->waitForText(__('passwords.sent'), 5)
                        ->assertSee(__('passwords.sent'));
            } catch (\Throwable $e) {
                 $this->captureBrowserHtml($browser, $e);
            }

        });
    }

    /**
     * Testa se a tela de reset de senha pode ser renderizada (sem validação de token no Dusk).
     *
     * @return void
     */
    public function test_reset_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->browse(function (Browser $browser) use ($user, $token) {
            try {

                $emailParam = urlencode($user->email);
                $browser->visit('/reset-password/' . $token . '?email=' . $emailParam)
                        ->assertSee('E-mail')
                        ->assertSee('Nova Senha')
                        ->assertSee('Confirmar Nova Senha')
                        ->assertValue('@text-input-email', $user->email)
                        ->assertPresent('input[name="token"][value="' . $token . '"]');
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });
    }

}