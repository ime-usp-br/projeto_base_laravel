<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

	 protected $seed = true;


    /**
     * Testa se a tela de confirmação de senha pode ser renderizada.
     *
     * @return void
     */
    public function test_confirm_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/confirm-password');

        $response->assertStatus(200);
    }

    /**
     * Testa se a senha pode ser confirmada.
     *
     * @return void
     */
    public function test_password_can_be_confirmed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $response->assertSessionHasNoErrors();
        $this->assertNotNull(session('auth.password_confirmed_at'));
    }

    /**
     * Testa se a senha não é confirmada com uma senha inválida.
     *
     * @return void
     */
    public function test_password_is_not_confirmed_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertNull(session('auth.password_confirmed_at'));
    }
}