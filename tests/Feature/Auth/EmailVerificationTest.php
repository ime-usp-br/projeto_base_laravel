<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyUserEmail;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

	 protected $seed = true;


    /**
     * Testa se a tela de aviso de verificação de e-mail pode ser renderizada para usuário não verificado.
     *
     * @return void
     */
    public function test_email_verification_prompt_screen_can_be_rendered_for_unverified_user(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
        $response->assertSee(__('Reenviar Email de Verificação'));
    }

    /**
     * Testa se o aviso de verificação de e-mail redireciona um usuário já verificado.
     *
     * @return void
     */
    public function test_email_verification_prompt_redirects_verified_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertRedirect('/?verified=1');
    }

    /**
     * Testa se o link de verificação de e-mail pode ser reenviado.
     *
     * @return void
     */
    public function test_email_verification_link_can_be_resent(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->post('/email/verification-notification');

        Notification::assertSentTo($user, VerifyUserEmail::class);
        $response->assertRedirect();
        $response->assertSessionHas('status', 'verification-link-sent');
    }

    /**
     * Testa se o e-mail pode ser verificado através do link.
     *
     * @return void
     */
    public function test_email_can_be_verified_via_link(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        $response = $this->get($verificationUrl);

        Event::assertDispatched(Verified::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });

        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        $response->assertRedirect('/?verified=1');
        $this->assertAuthenticatedAs($user->fresh());
    }

    /**
     * Testa se o e-mail não é verificado com um hash inválido.
     *
     * @return void
     */
    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();
        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $response = $this->get($verificationUrl);

        Event::assertNotDispatched(Verified::class);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Testa se a verificação de e-mail falha com um ID de usuário inválido.
     *
     * @return void
     */
    public function test_email_verification_fails_with_invalid_user_id(): void
    {
        $user = User::factory()->unverified()->create();
        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id + 999, 'hash' => sha1($user->getEmailForVerification())]
        );

        $response = $this->get($verificationUrl);

        Event::assertNotDispatched(Verified::class);
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Testa se um usuário já verificado que visita o link é redirecionado e logado.
     *
     * @return void
     */
    public function test_already_verified_user_visiting_link_is_redirected_and_logged_in(): void
    {
        $user = User::factory()->create();
        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        $response = $this->get($verificationUrl);

        Event::assertNotDispatched(Verified::class);
        $response->assertRedirect('/?verified=1&already=1');
        $this->assertAuthenticatedAs($user->fresh());
    }
}