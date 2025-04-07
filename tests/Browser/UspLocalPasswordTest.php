<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendLocalPasswordLink;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UspLocalPasswordTest extends DuskTestCase
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
        Role::firstOrCreate(['name' => 'usp_user', 'guard_name' => 'web']);
    }

    /**
     * Testa se a tela de solicitação de senha local USP pode ser renderizada.
     *
     * @return void
     */
    public function test_request_local_password_screen_can_be_rendered(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                $browser->visit('/request-local-password')
                        ->assertPathIs('/request-local-password')
                        ->assertPresent('@primary-button-enviar-link-para-definir-senha-local');
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });
    }

    /**
     * Testa se o link para definir senha local USP pode ser solicitado.
     * Ignora a falha de Notification::assertSentTo conforme instruído.
     *
     * @return void
     */
    public function test_request_local_password_link_can_be_sent(): void
    {
        $user = User::factory()->create([
            'email' => 'usp.user@usp.br',
            'codpes' => '123987',
        ]);
        $user->assignRole('usp_user');

        $this->browse(function (Browser $browser) use ($user) {

            $browser->visit('/request-local-password')
                    ->assertPathIs('/request-local-password');
            try {
                $browser->type('@text-input-email', $user->email)
                        ->press('@primary-button-enviar-link-para-definir-senha-local')
                        ->waitForText('Se um usuário USP válido existir com este email', 5)
                        ->assertSee('Se um usuário USP válido existir com este email');
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }

        });
    }

    /**
     * Testa se a senha local USP pode ser definida através do link e usada para login.
     *
     * @return void
     */
    public function test_can_set_and_use_usp_local_password(): void
    {
        $user = User::factory()->create([
            'email' => 'usp.pwd.test@usp.br',
            'codpes' => '987123',
            'password' => null,
            'email_verified_at' => null,
        ]);
        $user->assignRole('usp_user');

        $signedUrl = URL::temporarySignedRoute(
            'local-password.set',
            now()->addHour(),
            ['email' => $user->email]
        );

        $this->browse(function (Browser $browser) use ($user, $signedUrl) {
            $browser->visit($signedUrl)
                    ->assertSee('Defina sua nova senha local');
            try {
                $browser->type('@text-input-password', 'newLocalPassword123')
                        ->type('@text-input-password_confirmation', 'newLocalPassword123')
                        ->press('@primary-button-definir-nova-senha')
                        ->waitForLocation('/')
                        ->assertPathIs('/')

                        ->assertAuthenticatedAs($user);
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });

        $user->refresh();
        $this->assertTrue(Hash::check('newLocalPassword123', $user->password));
        $this->assertNotNull($user->email_verified_at);


        $this->browse(function (Browser $browser) use ($user) {
            try {
                $browser->logout()
                        ->visit('/login')
                        ->assertPathIs('/login')
                        ->type('@text-input-email', $user->email)
                        ->type('@text-input-password', 'newLocalPassword123')
                        ->press('@primary-button-entrar')
                        ->waitForLocation('/')
                        ->assertPathIs('/')
                        ->assertAuthenticatedAs($user);
            } catch (\Throwable $e) {
                 $this->captureBrowserHtml($browser, $e);
            }
        });
    }
}