<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\SendResetPasswordLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

	 protected $seed = true;


    /**
     * Testa se a tela de link de redefinição de senha pode ser renderizada.
     *
     * @return void
     */
    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
    }

    /**
     * Testa se o link de redefinição de senha pode ser solicitado.
     *
     * @return void
     */
    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, SendResetPasswordLink::class);
        $response->assertSessionHas('status');

        $response->assertSessionHas('status', __('passwords.sent'));
    }

    /**
     * Testa se a solicitação de link de redefinição de senha falha para e-mail desconhecido.
     *
     * @return void
     */
    public function test_reset_password_link_request_fails_for_unknown_email(): void
    {
        Notification::fake();

        $response = $this->post('/forgot-password', ['email' => 'unknown@example.com']);

        Notification::assertNothingSent();
        $response->assertSessionHasErrors('email');

        $response->assertSessionHasErrors(['email' => __('passwords.user')]);
    }

    /**
     * Testa se a tela de redefinição de senha pode ser renderizada com um token válido.
     *
     * @return void
     */
    public function test_reset_password_screen_can_be_rendered_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->get('/reset-password/'.$token.'?email='.urlencode($user->email));

        $response->assertStatus(200);
        $response->assertViewIs('auth.reset-password');

        $response->assertSee('<input type="hidden" name="token" value="'.$token.'">', false);

        $response->assertSee('value="'.e(str_replace('+', ' ', $user->email)).'"', false);
    }

    /**
     * Testa se a tela de redefinição de senha falha com um token inválido.
     *
     * @return void
     */
    public function test_reset_password_screen_fails_with_invalid_token(): void
    {
         $user = User::factory()->create();

         $response = $this->get('/reset-password/invalidtoken?email='.urlencode($user->email));

         $response->assertStatus(200);
         $response->assertViewIs('auth.reset-password');

         $response->assertSee('Nova Senha');
         $response->assertSee('<input type="hidden" name="token" value="invalidtoken">', false);
    }


    /**
     * Testa se a senha pode ser redefinida com um token válido.
     *
     * @return void
     */
    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', __(Password::PASSWORD_RESET));

        $this->assertTrue(password_verify('new-password', $user->fresh()->password));

        $this->assertFalse(Password::broker()->tokenExists($user, $token));
    }

    /**
     * Testa se a redefinição de senha falha com um token inválido no POST.
     *
     * @return void
     */
    public function test_password_reset_fails_with_invalid_token_on_post(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionHasErrors(['email' => __(Password::INVALID_TOKEN)]);
    }

    /**
     * Testa se a redefinição de senha falha com um e-mail inválido.
     *
     * @return void
     */
    public function test_password_reset_fails_with_invalid_email(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'wrong-email@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionHasErrors(['email' => __(Password::INVALID_USER)]);
    }

    /**
     * Testa se a redefinição de senha falha com senhas não coincidentes.
     *
     * @return void
     */
    public function test_password_reset_fails_with_password_mismatch(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors('password');
    }
}