<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

	 protected $seed = true;


    /**
     * Testa se a página de perfil é exibida.
     *
     * @return void
     */
    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        $response->assertViewIs('profile.edit');
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    /**
     * Testa se as informações do perfil podem ser atualizadas.
     *
     * @return void
     */
    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User Updated',
                'email' => 'test_updated@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User Updated', $user->name);
        $this->assertSame('test_updated@example.com', $user->email);

        $this->assertNull($user->email_verified_at);
    }

    /**
     * Testa se o status de verificação de e-mail permanece inalterado quando o e-mail não é alterado.
     *
     * @return void
     */
    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User Name Change Only',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    /**
     * Testa se a validação da atualização do perfil falha com dados inválidos.
     *
     * @return void
     */
    public function test_profile_update_validation_fails(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', ['name' => '', 'email' => 'test@example.com']);
        $response->assertSessionHasErrors('name');

        $response = $this->actingAs($user)->patch('/profile', ['name' => 'Test', 'email' => 'invalid-email']);
        $response->assertSessionHasErrors('email');

        $otherUser = User::factory()->create(['email' => 'other@example.com']);
        $response = $this->actingAs($user)->patch('/profile', ['name' => 'Test', 'email' => 'other@example.com']);
        $response->assertSessionHasErrors('email');
    }


    /**
     * Testa se o usuário pode excluir sua própria conta.
     *
     * @return void
     */
    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /**
     * Testa se a senha correta deve ser fornecida para excluir a conta.
     *
     * @return void
     */
    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}