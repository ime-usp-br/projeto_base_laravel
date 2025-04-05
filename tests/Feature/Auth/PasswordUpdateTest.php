<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

	 protected $seed = true;


    /**
     * Testa se a senha pode ser atualizada.
     *
     * @return void
     */
    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    /**
     * Testa se a senha correta deve ser fornecida para atualizar a senha.
     *
     * @return void
     */
    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'current_password')
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    /**
     * Testa se a confirmação da nova senha deve coincidir.
     *
     * @return void
     */
    public function test_new_password_confirmation_must_match(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'different-new-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'password')
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }
}