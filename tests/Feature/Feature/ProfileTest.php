<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered; // Needed for email change verification check

class ProfileTest extends TestCase
{
    use RefreshDatabase;

	 protected $seed = true;


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
            ->assertRedirect('/profile'); // Redirects back to profile page

        $user->refresh();

        $this->assertSame('Test User Updated', $user->name);
        $this->assertSame('test_updated@example.com', $user->email);
        // Email verification should be reset when email changes
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create(); // Verified by default

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User Name Change Only',
                'email' => $user->email, // Email remains the same
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        // Email verification status should NOT be null
        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_profile_update_validation_fails(): void
    {
        $user = User::factory()->create();

        // Test missing name
        $response = $this->actingAs($user)->patch('/profile', ['name' => '', 'email' => 'test@example.com']);
        $response->assertSessionHasErrors('name');

        // Test invalid email
        $response = $this->actingAs($user)->patch('/profile', ['name' => 'Test', 'email' => 'invalid-email']);
        $response->assertSessionHasErrors('email');

        // Test email already taken by another user
        $otherUser = User::factory()->create(['email' => 'other@example.com']);
        $response = $this->actingAs($user)->patch('/profile', ['name' => 'Test', 'email' => 'other@example.com']);
        $response->assertSessionHasErrors('email');
    }


    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password', // Default factory password
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/'); // Redirects to home after deletion

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile') // Simulate coming from profile page
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password') // Check specific error bag
            ->assertRedirect('/profile'); // Should redirect back

        $this->assertNotNull($user->fresh()); // User should still exist
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}