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


    /**
     * Configura o ambiente de teste antes de cada teste.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', EnsureEmailIsVerifiedGlobally::class])->get('/protected-route', function () {
            return 'Protected Content';
        })->name('protected.route');

        Route::middleware('web')->get('/verify-email', function () {
            return 'Verification Notice';
        })->name('verification.notice');

        Route::middleware('web')->post('/logout', function () {
            auth()->logout();
            return redirect('/');
        })->name('logout');

         Route::middleware(['web', 'signed', 'throttle:6,1'])->get('/verify-email/{id}/{hash}', function () {

             return 'Verification Verify Route';
         })->name('verification.verify');

         Route::middleware(['web', 'auth', 'throttle:6,1'])->post('/email/verification-notification', function () {

             return redirect()->back()->with('status', 'verification-link-sent');
         })->name('verification.send');

    }

    /**
     * Testa se um usuário não verificado é redirecionado de uma rota protegida.
     *
     * @return void
     */
    public function test_unverified_user_is_redirected_from_protected_route(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/protected-route');

        $response->assertRedirect(route('verification.notice'));
    }

    /**
     * Testa se um usuário verificado pode acessar uma rota protegida.
     *
     * @return void
     */
    public function test_verified_user_can_access_protected_route(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/protected-route');

        $response->assertStatus(200);
        $response->assertSee('Protected Content');
    }

    /**
     * Testa se um usuário convidado é redirecionado pelo middleware de autenticação, não pelo de verificação.
     *
     * @return void
     */
    public function test_guest_user_is_redirected_by_auth_middleware_not_verification(): void
    {
        $response = $this->get('/protected-route');

        $response->assertRedirect(route('login'));
    }

    /**
     * Testa se um usuário não verificado pode acessar a rota de aviso de verificação.
     *
     * @return void
     */
    public function test_unverified_user_can_access_verification_notice_route(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);
        $response->assertSee('Verification Notice');
    }

    /**
     * Testa se um usuário não verificado pode acessar a rota de logout.
     *
     * @return void
     */
    public function test_unverified_user_can_access_logout_route(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * Testa se um usuário não verificado pode acessar a rota de verificação (verification.verify).
     *
     * @return void
     */
     public function test_unverified_user_can_access_verification_verify_route(): void
     {

         $user = User::factory()->unverified()->create();

         $dummyVerifyUrl = route('verification.verify', ['id' => $user->id, 'hash' => 'dummyhash']);

         $response = $this->actingAs($user)->get($dummyVerifyUrl);

         $response->assertStatus(403);
     }

    /**
     * Testa se um usuário não verificado pode acessar a rota de reenvio de verificação (verification.send).
     *
     * @return void
     */
     public function test_unverified_user_can_access_verification_send_route(): void
     {
         $user = User::factory()->unverified()->create();

         $response = $this->actingAs($user)->post(route('verification.send'));

         $response->assertRedirect();
         $response->assertSessionHas('status', 'verification-link-sent');
     }
}