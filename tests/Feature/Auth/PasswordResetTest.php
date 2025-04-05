<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\SendResetPasswordLink; // Use custom notification
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

	 protected $seed = true;


    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, SendResetPasswordLink::class); // Check custom notification
        $response->assertSessionHas('status'); // Check for success message
        // Assert using the language key for robustness
        $response->assertSessionHas('status', __('passwords.sent'));
    }

    public function test_reset_password_link_request_fails_for_unknown_email(): void
    {
        Notification::fake();

        $response = $this->post('/forgot-password', ['email' => 'unknown@example.com']);

        Notification::assertNothingSent();
        $response->assertSessionHasErrors('email');
        // Check for the specific error message in Portuguese
        $response->assertSessionHasErrors(['email' => __('passwords.user')]);
    }

    public function test_reset_password_screen_can_be_rendered_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->get('/reset-password/'.$token.'?email='.urlencode($user->email)); // Ensure email is urlencoded

        $response->assertStatus(200);
        $response->assertViewIs('auth.reset-password');
        // Assert the hidden token input field exists and has the correct value
        $response->assertSee('<input type="hidden" name="token" value="'.$token.'">', false);
        // Assert the email input field has the correct value
        $response->assertSee('value="'.e(str_replace('+', ' ', $user->email)).'"', false); // Handle potential '+' in email
    }

    public function test_reset_password_screen_fails_with_invalid_token(): void
    {
         $user = User::factory()->create();
         // No token generation, just use an invalid one
         $response = $this->get('/reset-password/invalidtoken?email='.urlencode($user->email));

         // The controller renders the view regardless of token validity on GET
         $response->assertStatus(200);
         $response->assertViewIs('auth.reset-password');
         // We can assert the form is present, but the token is invalid
         $response->assertSee('Nova Senha');
         $response->assertSee('<input type="hidden" name="token" value="invalidtoken">', false);
    }


    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake(); // Fake notifications to prevent actual email sending

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
        $response->assertSessionHas('status', __(Password::PASSWORD_RESET)); // Check for success status

        // Verify password was actually changed (optional but good)
        $this->assertTrue(password_verify('new-password', $user->fresh()->password));
        // Verify the token was deleted/invalidated
        $this->assertFalse(Password::broker()->tokenExists($user, $token));
    }

    public function test_password_reset_fails_with_invalid_token_on_post(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors('email'); // Laravel puts token errors under 'email' key
        $response->assertSessionHasErrors(['email' => __(Password::INVALID_TOKEN)]);
    }

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

        $response->assertSessionHasErrors('password'); // Error key is 'password' for confirmation mismatch
    }
}