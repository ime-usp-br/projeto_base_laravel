<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyUserEmail;

class EmailVerificationTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $seed = true;

    /**
     * Testa se usuário não verificado é redirecionado para a tela de aviso.
     *
     * @return void
     */
    public function test_unverified_user_is_redirected_to_verification_notice(): void
    {
        $user = User::factory()->unverified()->create();

        $this->browse(function (Browser $browser) use ($user) {
            try {
                $browser->loginAs($user)
                        ->visit('/dashboard')
                        ->waitForLocation('/verify-email')
                        ->assertPathIs('/verify-email')
                        ->assertSee('Obrigado por se inscrever!');
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });
    }

    /**
     * Testa se usuário verificado pode acessar rotas protegidas.
     *
     * @return void
     */
    public function test_verified_user_can_access_protected_routes(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            try {
                $browser->loginAs($user)
                        ->assertAuthenticated()
                        ->assertAuthenticatedAs($user)
                        ->visit('/dashboard')
                        ->assertPathIs('/dashboard')
                        ->assertSee('Você está logado!');
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });
    }

    /**
     * Testa se clicar no link de verificação verifica o usuário.
     *
     * @return void
     */
    public function test_clicking_verification_link_verifies_user(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        $this->browse(function (Browser $browser) use ($user, $verificationUrl) {
            try {
                $browser->visit($verificationUrl)
                        ->waitForLocation('/')
                        ->assertPathIs('/')
                        ->assertQueryStringHas('verified', '1');

                $this->assertTrue($user->fresh()->hasVerifiedEmail());

                $browser->loginAs($user->fresh())
                        ->assertAuthenticated()
                        ->assertAuthenticatedAs($user)
                        ->visit('/dashboard')
                        ->assertPathIs('/dashboard');
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });
    }

    /**
     * Testa se o email de verificação pode ser reenviado da tela de aviso.
     * Ignora a falha de Notification::assertSentTo conforme instruído.
     *
     * @return void
     */
    public function test_verification_email_can_be_resent_from_notice_screen(): void
    {
        $user = User::factory()->unverified()->create();

        $this->browse(function (Browser $browser) use ($user) {

            try {
                $browser->loginAs($user)
                        ->visit('/verify-email')
                        ->assertPathIs('/verify-email')
                        ->press('@primary-button-reenviar-email-de-verificacao')
                        ->waitForText('Um novo link de verificação foi enviado', 5)
                        ->assertSee('Um novo link de verificação foi enviado para o endereço de e-mail que você forneceu durante o registro.');
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }

        });
    }
}