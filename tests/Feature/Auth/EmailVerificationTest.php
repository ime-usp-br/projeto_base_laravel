<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyUserEmail; // Use custom notification
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

	 protected $seed = true;


    public function test_email_verification_prompt_screen_can_be_rendered_for_unverified_user(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
        $response->assertSee(__('Reenviar Email de Verificação'));
    }

    public function test_email_verification_prompt_redirects_verified_user(): void
    {
        $user = User::factory()->create(); // Verified by default

        $response = $this->actingAs($user)->get('/verify-email');

        // Default redirect is to '/' with verified=1 query param
        $response->assertRedirect('/?verified=1');
    }

    public function test_email_verification_link_can_be_resent(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->post('/email/verification-notification');

        Notification::assertSentTo($user, VerifyUserEmail::class);
        $response->assertRedirect();
        $response->assertSessionHas('status', 'verification-link-sent');
    }

    public function test_email_can_be_verified_via_link(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        // Generate the verification URL using the custom logic (sha1 hash)
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        // Access the verification URL *without* acting as the user initially,
        // as the VerifyEmailController handles login after verification.
        $response = $this->get($verificationUrl);

        Event::assertDispatched(Verified::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        // The custom controller redirects to '/' after verification and login
        $response->assertRedirect('/?verified=1');
        $this->assertAuthenticatedAs($user->fresh()); // Check user is logged in
    }

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
        // The custom controller redirects to login with an error
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest(); // User should not be logged in
    }

    public function test_email_verification_fails_with_invalid_user_id(): void
    {
        $user = User::factory()->unverified()->create(); // Need a user to generate a valid hash
        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id + 999, 'hash' => sha1($user->getEmailForVerification())] // Invalid ID
        );

        $response = $this->get($verificationUrl);

        Event::assertNotDispatched(Verified::class);
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_already_verified_user_visiting_link_is_redirected_and_logged_in(): void
    {
        $user = User::factory()->create(); // Already verified
        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        $response = $this->get($verificationUrl);

        Event::assertNotDispatched(Verified::class); // Should not dispatch Verified again
        $response->assertRedirect('/?verified=1&already=1'); // Specific redirect for already verified
        $this->assertAuthenticatedAs($user->fresh());
    }
}