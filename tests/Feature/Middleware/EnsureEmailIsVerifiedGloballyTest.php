<?php

namespace Tests\Feature\Middleware;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureEmailIsVerifiedGlobally;

class EnsureEmailIsVerifiedGloballyTest extends TestCase
{
    use RefreshDatabase;

	 protected $seed = true;


    protected function setUp(): void
    {
        parent::setUp();

        // Define dummy routes for testing middleware redirection
        Route::middleware(['web', 'auth', EnsureEmailIsVerifiedGlobally::class])->get('/protected-route', function () {
            return 'Protected Content';
        })->name('protected.route'); // Name the route

        // Include necessary auth routes used by the middleware
        Route::middleware('web')->get('/verify-email', function () {
            return 'Verification Notice';
        })->name('verification.notice');

        Route::middleware('web')->post('/logout', function () {
            auth()->logout();
            return redirect('/');
        })->name('logout');

         Route::middleware(['web', 'signed', 'throttle:6,1'])->get('/verify-email/{id}/{hash}', function () {
             // Dummy handler for verification.verify route needed by middleware check
             return 'Verification Verify Route';
         })->name('verification.verify');

         Route::middleware(['web', 'auth', 'throttle:6,1'])->post('/email/verification-notification', function () {
             // Dummy handler for verification.send route needed by middleware check
             return redirect()->back()->with('status', 'verification-link-sent');
         })->name('verification.send');

    }

    public function test_unverified_user_is_redirected_from_protected_route(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/protected-route');

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_verified_user_can_access_protected_route(): void
    {
        $user = User::factory()->create(); // Verified by default

        $response = $this->actingAs($user)->get('/protected-route');

        $response->assertStatus(200);
        $response->assertSee('Protected Content');
    }

    public function test_guest_user_is_redirected_by_auth_middleware_not_verification(): void
    {
        $response = $this->get('/protected-route');

        $response->assertRedirect(route('login')); // Should be redirected by 'auth' middleware first
    }

    public function test_unverified_user_can_access_verification_notice_route(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);
        $response->assertSee('Verification Notice');
    }

    public function test_unverified_user_can_access_logout_route(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

     public function test_unverified_user_can_access_verification_verify_route(): void
     {
         // This route requires a signed URL, testing direct access isn't the primary goal here,
         // but the middleware should *allow* it if the signature is valid.
         // We'll simulate accessing it without a valid signature first to see if middleware blocks.
         $user = User::factory()->unverified()->create();

         // Generate a dummy URL structure similar to the verification link
         $dummyVerifyUrl = route('verification.verify', ['id' => $user->id, 'hash' => 'dummyhash']);

         $response = $this->actingAs($user)->get($dummyVerifyUrl);

         // Expect 403 because the signature is invalid, *not* because the verification middleware blocked it.
         // If it redirected to verification.notice, the middleware logic would be wrong.
         $response->assertStatus(403);
     }

     public function test_unverified_user_can_access_verification_send_route(): void
     {
         $user = User::factory()->unverified()->create();

         $response = $this->actingAs($user)->post(route('verification.send'));

         // Should redirect back (or wherever the dummy route sends it)
         $response->assertRedirect();
         $response->assertSessionHas('status', 'verification-link-sent');
     }
}