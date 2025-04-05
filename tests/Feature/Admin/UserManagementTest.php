<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Uspdev\Replicado\Pessoa; // Import the class to mock
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyUserEmail;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

	 protected $seed = true;


    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Create users
        $this->adminUser = User::factory()->create()->assignRole('admin');
        $this->regularUser = User::factory()->create()->assignRole('usp_user'); // Or external_user
    }

    // --- Index Tests ---

    public function test_admin_can_view_user_list(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
        $response->assertSee($this->adminUser->name);
        $response->assertSee($this->regularUser->name);
    }

    public function test_non_admin_cannot_view_user_list(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.users.index'));
        $response->assertStatus(403); // Expect Forbidden

        $response = $this->get(route('admin.users.index')); // Guest
        $response->assertRedirect(route('login'));
    }

    // --- Create USP User Tests ---

    public function test_admin_can_view_create_usp_user_form(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.users.create.usp'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.users.create_usp');
    }

    public function test_admin_can_create_usp_user_successfully(): void
    {
        $codpes = '1234567';
        $nome = 'Fulano USP da Silva';
        $email = 'fulano.usp@usp.br';

        // Mock the Replicado Pessoa static methods
        $pessoaMock = Mockery::mock('alias:'.Pessoa::class);
        $pessoaMock->shouldReceive('fetch')->once()->with($codpes)->andReturn(['nompes' => $nome]);
        $pessoaMock->shouldReceive('email')->once()->with($codpes)->andReturn($email);

        $response = $this->actingAs($this->adminUser)->post(route('admin.users.store.usp'), [
            'codpes' => $codpes,
        ]);

        $response->assertRedirect(route('admin.dashboard')); // Or wherever it redirects
        $response->assertSessionHas('success'); // Check for success flash message
        $this->assertDatabaseHas('users', [
            'codpes' => $codpes,
            'name' => $nome,
            'email' => $email,
        ]);

        $newUser = User::where('codpes', $codpes)->first();
        $this->assertNotNull($newUser);
        $this->assertTrue($newUser->hasRole('usp_user'));
        $this->assertNotNull($newUser->email_verified_at); // Should be verified automatically
        $this->assertNotNull($newUser->password); // Password should be generated and hashed
    }

    public function test_create_usp_user_fails_if_replicado_fetch_fails(): void
    {
        $codpes = '9999999';
        $pessoaMock = Mockery::mock('alias:'.Pessoa::class);
        $pessoaMock->shouldReceive('fetch')->once()->with($codpes)->andReturn(null); // Simulate user not found

        $response = $this->actingAs($this->adminUser)
                         ->from(route('admin.users.create.usp')) // Simulate coming from the form
                         ->post(route('admin.users.store.usp'), ['codpes' => $codpes]);

        $response->assertRedirect(route('admin.users.create.usp'));
        $response->assertSessionHasErrors('codpes');
        $response->assertSessionHasErrors(['codpes' => 'Número USP não encontrado no Replicado.']);
        $this->assertDatabaseMissing('users', ['codpes' => $codpes]);
    }

    public function test_create_usp_user_fails_if_replicado_email_is_empty(): void
    {
        $codpes = '1234567';
        $nome = 'Fulano USP Sem Email';
        $pessoaMock = Mockery::mock('alias:'.Pessoa::class);
        $pessoaMock->shouldReceive('fetch')->once()->with($codpes)->andReturn(['nompes' => $nome]);
        $pessoaMock->shouldReceive('email')->once()->with($codpes)->andReturn(''); // Simulate no email

        $response = $this->actingAs($this->adminUser)
                         ->from(route('admin.users.create.usp'))
                         ->post(route('admin.users.store.usp'), ['codpes' => $codpes]);

        $response->assertRedirect(route('admin.users.create.usp'));
        $response->assertSessionHasErrors('codpes');
        $response->assertSessionHasErrors(['codpes' => 'Usuário sem email principal cadastrado no Replicado.']);
        $this->assertDatabaseMissing('users', ['codpes' => $codpes]);
    }

    public function test_create_usp_user_fails_if_codpes_already_exists(): void
    {
        $existingUser = User::factory()->create(['codpes' => '1112223']);
        $pessoaMock = Mockery::mock('alias:'.Pessoa::class); // Mock to prevent actual call
        $pessoaMock->shouldReceive('fetch')->never();
        $pessoaMock->shouldReceive('email')->never();

        $response = $this->actingAs($this->adminUser)
                         ->from(route('admin.users.create.usp'))
                         ->post(route('admin.users.store.usp'), ['codpes' => '1112223']);

        $response->assertRedirect(route('admin.users.create.usp'));
        $response->assertSessionHasErrors('codpes'); // Validation rule unique:users,codpes
    }

     public function test_create_usp_user_fails_if_email_already_exists(): void
     {
         $codpes = '7654321';
         $nome = 'Outro Fulano';
         $email = 'existing@usp.br';
         User::factory()->create(['email' => $email]); // Pre-existing email

         $pessoaMock = Mockery::mock('alias:'.Pessoa::class);
         $pessoaMock->shouldReceive('fetch')->once()->with($codpes)->andReturn(['nompes' => $nome]);
         $pessoaMock->shouldReceive('email')->once()->with($codpes)->andReturn($email);

         $response = $this->actingAs($this->adminUser)
                          ->from(route('admin.users.create.usp'))
                          ->post(route('admin.users.store.usp'), ['codpes' => $codpes]);

         $response->assertRedirect(route('admin.users.create.usp'));
         $response->assertSessionHasErrors('codpes'); // Controller checks email existence too
         $response->assertSessionHasErrors(['codpes' => 'Usuário já cadastrado no sistema (por Nº USP ou Email).']);
         $this->assertDatabaseMissing('users', ['codpes' => $codpes]);
     }

    // --- Create Manual User Tests ---

    public function test_admin_can_view_create_manual_user_form(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.users.create.manual'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.users.create_manual');
        $response->assertViewHas('suggestedPassword'); // Check if suggestion is passed
    }

    public function test_admin_can_create_manual_external_user(): void
    {
        Notification::fake(); // VerifyUserEmail might be sent implicitly via Registered event

        $userData = [
            'name' => 'Manual External',
            'email' => 'manual_external@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'codpes' => null, // Explicitly null for external
        ];

        $response = $this->actingAs($this->adminUser)->post(route('admin.users.store.manual'), $userData);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'codpes' => null,
            'email_verified_at' => null, // External users start unverified
        ]);
        $newUser = User::where('email', $userData['email'])->first();
        $this->assertTrue($newUser->hasRole('external_user'));
        $this->assertTrue(Hash::check($userData['password'], $newUser->password));

        // Check if verification email was triggered (via Registered event)
        // Notification::assertSentTo($newUser, VerifyUserEmail::class); // This depends on if the controller explicitly sends it or relies on the event listener
    }

    public function test_admin_can_create_manual_usp_user(): void
    {
         Notification::fake();
         $codpes = '9876543';
         $userData = [
             'name' => 'Manual USP User',
             'email' => 'manual_usp@usp.br',
             'password' => 'password123',
             'password_confirmation' => 'password123',
             'codpes' => $codpes, // Provide codpes
         ];

         $response = $this->actingAs($this->adminUser)->post(route('admin.users.store.manual'), $userData);

         $response->assertRedirect(route('admin.dashboard'));
         $response->assertSessionHas('success');

         $this->assertDatabaseHas('users', [
             'name' => $userData['name'],
             'email' => $userData['email'],
             'codpes' => $codpes,
         ]);
         $newUser = User::where('email', $userData['email'])->first();
         $this->assertTrue($newUser->hasRole('usp_user'));
         $this->assertNotNull($newUser->email_verified_at); // USP users created manually are pre-verified
         $this->assertTrue(Hash::check($userData['password'], $newUser->password));
         Notification::assertNothingSent(); // No verification email for pre-verified USP user
    }

    public function test_create_manual_user_validation_fails(): void
    {
        $response = $this->actingAs($this->adminUser)
                         ->from(route('admin.users.create.manual'))
                         ->post(route('admin.users.store.manual'), []); // Empty data
        $response->assertSessionHasErrors(['name', 'email', 'password']);

        $response = $this->actingAs($this->adminUser)
                         ->from(route('admin.users.create.manual'))
                         ->post(route('admin.users.store.manual'), [
                             'name' => 'Test',
                             'email' => 'invalid-email',
                             'password' => 'short',
                             'password_confirmation' => 'short',
                         ]);
        $response->assertSessionHasErrors(['email', 'password']);

        $response = $this->actingAs($this->adminUser)
                         ->from(route('admin.users.create.manual'))
                         ->post(route('admin.users.store.manual'), [
                             'name' => 'Test',
                             'email' => 'test@example.com',
                             'password' => 'password123',
                             'password_confirmation' => 'password456', // Mismatch
                         ]);
        $response->assertSessionHasErrors('password');
    }

    public function test_create_manual_user_fails_with_existing_email(): void
    {
        User::factory()->create(['email' => 'manual_exists@example.com']);
        $response = $this->actingAs($this->adminUser)
                         ->from(route('admin.users.create.manual'))
                         ->post(route('admin.users.store.manual'), [
                             'name' => 'Manual Exists',
                             'email' => 'manual_exists@example.com',
                             'password' => 'password123',
                             'password_confirmation' => 'password123',
                         ]);
        $response->assertSessionHasErrors('email');
    }

    public function test_create_manual_user_fails_with_existing_codpes(): void
    {
        User::factory()->create(['codpes' => '5554443']);
        $response = $this->actingAs($this->adminUser)
                         ->from(route('admin.users.create.manual'))
                         ->post(route('admin.users.store.manual'), [
                             'name' => 'Manual USP Exists',
                             'email' => 'manual_usp_exists@usp.br',
                             'password' => 'password123',
                             'password_confirmation' => 'password123',
                             'codpes' => '5554443', // Existing codpes
                         ]);
        $response->assertSessionHasErrors('codpes');
    }

    // --- Teardown ---
    protected function tearDown(): void
    {
        Mockery::close(); // Close Mockery expectations
        parent::tearDown();
    }
}