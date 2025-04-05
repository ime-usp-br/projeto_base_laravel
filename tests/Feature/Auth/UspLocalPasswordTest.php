<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendLocalPasswordLink;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission as SpatiePermission; // Alias to avoid conflict

class UspLocalPasswordTest extends TestCase
{
    use RefreshDatabase;

	 protected $seed = true;


    protected function setUp(): void
    {
        parent::setUp();
        // Ensure roles and potentially the 'senhaunica' permission exist if checked by ->can()
        Role::firstOrCreate(['name' => 'usp_user', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'external_user', 'guard_name' => 'web']); // Ensure external_user role exists
        // Assuming the controller checks for the 'senhaunica' permission on the 'user' model/guard
        // If it checks for a role instead, adjust accordingly.
        SpatiePermission::firstOrCreate(['name' => 'senhaunica', 'guard_name' => 'web']);
    }

    public function test_request_local_password_form_can_be_rendered(): void
    {
        $response = $this->get(route('local-password.request'));
        $response->assertStatus(200);
        $response->assertViewIs('auth.request-local-password');
    }

    public function test_local_password_link_can_be_requested_for_valid_usp_user(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@usp.br',
            'codpes' => '1234567',
        ]);
        $user->assignRole('usp_user');
        // Grant the permission if the controller checks for it explicitly
        $user->givePermissionTo('senhaunica');

        $response = $this->post(route('local-password.request'), ['email' => $user->email]);

        // Assuming the code error in controller's can() check is fixed, this should pass
        Notification::assertSentTo($user, SendLocalPasswordLink::class);
        $response->assertRedirect();
        $response->assertSessionHas('status');
        // Check for the specific message in Portuguese
        $response->assertSessionHas('status', __('Se um usuário USP válido existir com este email, um link para definir senha local será enviado!'));
    }

    public function test_local_password_link_request_fails_for_non_usp_email(): void
    {
        Notification::fake();

        $response = $this->post(route('local-password.request'), ['email' => 'test@example.com']);

        Notification::assertNothingSent();
        $response->assertSessionHasErrors('email');
        // Check for specific validation message
        $response->assertSessionHasErrors(['email' => 'Por favor, forneça um email usp.br válido.']);
    }

    public function test_local_password_link_request_fails_for_non_existent_user(): void
    {
        Notification::fake();

        $response = $this->post(route('local-password.request'), ['email' => 'nonexistent@usp.br']);

        Notification::assertNothingSent();
        $response->assertSessionHasErrors('email');
        // Check for specific validation message
        $response->assertSessionHasErrors(['email' => 'Nenhum usuário USP encontrado com este email.']);
    }

    public function test_local_password_link_request_fails_for_user_without_codpes(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'nocodpes@usp.br',
            'codpes' => null, // No codpes
        ]);
        $user->assignRole('external_user'); // Assign the now existing external_user role

        $response = $this->post(route('local-password.request'), ['email' => $user->email]);

        Notification::assertNothingSent();
        $response->assertSessionHasErrors('email');
        // Check for the controller's specific error message
        $response->assertSessionHasErrors(['email' => 'Este email não pertence a um usuário USP válido no sistema.']);
    }

    // Add test for user without 'senhaunica' permission if applicable

    public function test_set_local_password_form_can_be_rendered_with_valid_signature(): void
    {
        $user = User::factory()->create(['email' => 'test@usp.br', 'codpes' => '123']);

        $signedUrl = URL::temporarySignedRoute(
            'local-password.set',
            now()->addHour(),
            ['email' => $user->email]
        );

        $response = $this->get($signedUrl);

        $response->assertStatus(200);
        $response->assertViewIs('auth.set-local-password');
        $response->assertViewHas('email', $user->email);
        $response->assertSee('Defina sua nova senha local');
    }

    public function test_set_local_password_form_fails_with_invalid_signature(): void
    {
        $user = User::factory()->create(['email' => 'test@usp.br', 'codpes' => '123']);
        $signedUrl = URL::temporarySignedRoute(
            'local-password.set',
            now()->addHour(),
            ['email' => $user->email]
        );
        $invalidUrl = str_replace('signature=', 'signature=invalid', $signedUrl);

        $response = $this->get($invalidUrl);

        $response->assertStatus(403); // Expect Forbidden due to invalid signature middleware
    }

    public function test_local_password_can_be_set_with_valid_data(): void
    {
        $user = User::factory()->unverified()->create([ // Start unverified to test verification
            'email' => 'test@usp.br',
            'codpes' => '1234567',
            'password' => null, // No initial password
        ]);
         $user->assignRole('usp_user');
         $user->givePermissionTo('senhaunica');

        $signedUrl = URL::temporarySignedRoute(
            'local-password.set',
            now()->addHour(),
            ['email' => $user->email]
        );
        // Extract parameters from the signed URL to simulate form submission
        parse_str(parse_url($signedUrl, PHP_URL_QUERY), $queryParams);

        $response = $this->post(route('local-password.set'), [
            'email' => $user->email,
            'expires' => $queryParams['expires'],
            'signature' => $queryParams['signature'],
            'password' => 'new-local-password',
            'password_confirmation' => 'new-local-password',
        ]);

        // Assuming the PermissionDoesNotExist error during redirect is fixed elsewhere
        $response->assertRedirect('/');
        $response->assertSessionHas('status', 'Senha local definida com sucesso!');
        $response->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertTrue(Hash::check('new-local-password', $user->password));
        $this->assertNotNull($user->email_verified_at); // Should be verified now
        $this->assertAuthenticatedAs($user);
    }

    public function test_set_local_password_fails_with_password_mismatch(): void
    {
        $user = User::factory()->create([
            'email' => 'test@usp.br',
            'codpes' => '1234567',
            'password' => Hash::make('old-password'), // Give it an initial password
        ]);
        $user->assignRole('usp_user');
        $user->givePermissionTo('senhaunica');

        $signedUrl = URL::temporarySignedRoute(
            'local-password.set',
            now()->addHour(),
            ['email' => $user->email]
        );
        parse_str(parse_url($signedUrl, PHP_URL_QUERY), $queryParams);

        $response = $this->from(route('local-password.set', $queryParams))->post(route('local-password.set'), [
            'email' => $user->email,
            'expires' => $queryParams['expires'],
            'signature' => $queryParams['signature'],
            'password' => 'new-local-password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertRedirect(route('local-password.set', $queryParams)); // Should redirect back
        $response->assertSessionHasErrors('password');
        $this->assertGuest(); // Should not be logged in

        // Assert password hasn't changed from the initial one
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

     public function test_set_local_password_fails_for_non_usp_user_on_post(): void
     {
         $user = User::factory()->create([
             'email' => 'external@example.com', // Non-USP email
             'codpes' => null,
         ]);
         $user->assignRole('external_user'); // Use the now existing role

         // Generate a signed URL anyway to simulate the form post attempt
         $signedUrl = URL::temporarySignedRoute(
             'local-password.set',
             now()->addHour(),
             ['email' => $user->email]
         );
         parse_str(parse_url($signedUrl, PHP_URL_QUERY), $queryParams);

         $response = $this->post(route('local-password.set'), [
             'email' => $user->email,
             'expires' => $queryParams['expires'],
             'signature' => $queryParams['signature'],
             'password' => 'new-local-password',
             'password_confirmation' => 'new-local-password',
         ]);

         // The controller should reject this before setting the password
         $response->assertSessionHasErrors('email');
         $response->assertSessionHasErrors(['email' => 'Usuário não encontrado ou inválido.']);
         $this->assertGuest();
         $this->assertNull($user->fresh()->password);
     }
}