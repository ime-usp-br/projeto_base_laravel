<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Models\Role;

class RegistrationTest extends TestCase
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
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    /**
     * Testa se novos usuários externos podem se registrar.
     *
     * @return void
     */
    public function test_new_external_users_can_register(): void
    {
        Event::fake();

        $response = $this->post('/register', [
            'name' => 'Test External User',
            'email' => 'test_external@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'external',
            'codpes' => null,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('auth.confirm-notice'));

        $user = User::where('email', 'test_external@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Test External User', $user->name);
        $this->assertNull($user->codpes);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('external_user'));
        $this->assertFalse($user->hasRole('usp_user'));

        Event::assertDispatched(Registered::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    /**
     * Testa se novos usuários USP podem se registrar com dados válidos.
     *
     * @return void
     */
    public function test_new_usp_users_can_register_with_valid_data(): void
    {
        Event::fake();

        $response = $this->post('/register', [
            'name' => 'Test USP User',
            'email' => 'test@usp.br',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'usp',
            'codpes' => '1234567',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('auth.confirm-notice'));

        $user = User::where('email', 'test@usp.br')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Test USP User', $user->name);
        $this->assertEquals('1234567', $user->codpes);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('usp_user'));
        $this->assertFalse($user->hasRole('external_user'));

        Event::assertDispatched(Registered::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    /**
     * Testa se o registro de usuário USP falha com e-mail não-USP.
     *
     * @return void
     */
    public function test_usp_user_registration_fails_with_non_usp_email(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test USP User Fail',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'usp',
            'codpes' => '1234568',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Testa se o registro de usuário USP falha sem CodPes.
     *
     * @return void
     */
    public function test_usp_user_registration_fails_without_codpes(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test USP User Fail',
            'email' => 'test@usp.br',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'usp',
            'codpes' => null,
        ]);

        $response->assertSessionHasErrors('codpes');
        $this->assertGuest();
    }

    /**
     * Testa se o registro falha com um e-mail existente.
     *
     * @return void
     */
    public function test_registration_fails_with_existing_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'external',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Testa se o registro falha com um CodPes existente.
     *
     * @return void
     */
     public function test_registration_fails_with_existing_codpes(): void
     {
         User::factory()->create(['codpes' => '9876543']);

         $response = $this->post('/register', [
             'name' => 'Test USP User',
             'email' => 'another@usp.br',
             'password' => 'password',
             'password_confirmation' => 'password',
             'user_type' => 'usp',
             'codpes' => '9876543',
         ]);

         $response->assertSessionHasErrors('codpes');
         $this->assertGuest();
     }

    /**
     * Testa se o registro falha com senhas não coincidentes.
     *
     * @return void
     */
    public function test_registration_fails_with_password_mismatch(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
            'user_type' => 'external',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }
}