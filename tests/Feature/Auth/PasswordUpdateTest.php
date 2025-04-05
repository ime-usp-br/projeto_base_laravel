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


    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile') // Simulate coming from the profile page
            ->put('/password', [
                'current_password' => 'password', // Default factory password
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile'); // Should redirect back to profile

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

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
            ->assertSessionHasErrorsIn('updatePassword', 'current_password') // Check specific error bag
            ->assertRedirect('/profile');

        // Ensure password hasn't changed
        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

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
            ->assertSessionHasErrorsIn('updatePassword', 'password') // Error key is 'password' for confirmation
            ->assertRedirect('/profile');

        // Ensure password hasn't changed
        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }
}