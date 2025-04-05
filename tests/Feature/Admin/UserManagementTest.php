<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Uspdev\Replicado\Pessoa;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyUserEmail;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

	 protected $seed = true;


    protected User $adminUser;
    protected User $regularUser;

    /**
     * Configura o ambiente de teste antes de cada teste.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->adminUser = User::factory()->create()->assignRole('admin');
        $this->regularUser = User::factory()->create()->assignRole('usp_user');
    }

    /**
     * Testa se um administrador pode visualizar a lista de usuários.
     *
     * @return void
     */
    public function test_admin_can_view_user_list(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
        $response->assertSee($this->adminUser->name);
        $response->assertSee($this->regularUser->name);
    }

    /**
     * Testa se um usuário não administrador não pode visualizar a lista de usuários.
     *
     * @return void
     */
    public function test_non_admin_cannot_view_user_list(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.users.index'));
        $response->assertStatus(403);

        $response = $this->get(route('admin.users.index'));
        $response->assertStatus(403);
    }

    /**
     * Testa se um administrador pode visualizar o formulário de criação de usuário USP.
     *
     * @return void
     */
    public function test_admin_can_view_create_usp_user_form(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.users.create.usp'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.users.create_usp');
    }

    /**
     * Testa se um administrador pode criar um usuário USP com sucesso.
     *
     * @return void
     */
    public function test_admin_can_create_usp_user_successfully(): void
    {
        $codpes = '1234567';
        $nome = 'Fulano USP da Silva';
        $email = 'fulano.usp@usp.br';

        $pessoaMock = Mockery::mock('alias:'.Pessoa::class);
        $pessoaMock->shouldReceive('fetch')->once()->with($codpes)->andReturn(['nompes' => $nome]);
        $pessoaMock->shouldReceive('email')->once()->with($codpes)->andReturn($email);

        $response = $this->actingAs($this->adminUser)->post(route('admin.users.store.usp'), [
            'codpes' => $codpes,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'codpes' => $codpes,
            'name' => $nome,
            'email' => $email,
        ]);

        $newUser = User::where('codpes', $codpes)->first();
        $this->assertNotNull($newUser);
        $this->assertTrue($newUser->hasRole('usp_user'));
        $this->assertNotNull($newUser->email_verified_at);
        $this->assertNotNull($newUser->password);
    }

    /**
     * Testa se a criação de usuário USP falha se a busca no Replicado falhar.
     *
     * @return void
     */
    public function test_create_usp_user_fails_if_replicado_fetch_fails(): void
    {
        $codpes = '9999999';
        $pessoaMock = Mockery::mock('alias:'.Pessoa::class);
        $pessoaMock->shouldReceive('fetch')->once()->with($codpes)->andReturn(null);

        $response = $this->actingAs($this->adminUser)
                         ->from(route('admin.users.create.usp'))
                         ->post(route('admin.users.store.usp'), ['codpes' => $codpes]);

        $response->assertRedirect(route('admin.users.create.usp'));
        $response->assertSessionHasErrors('codpes');
        $response->assertSessionHasErrors(['codpes' => 'Número USP não encontrado no Replicado.']);
        $this->assertDatabaseMissing('users', ['codpes' => $codpes]);
    }

    /**
     * Testa se a criação de usuário USP falha se o e-mail do Replicado estiver vazio.
     *
     * @return void
     */
    public function test_create_usp_user_fails_if_replicado_email_is_empty(): void
    {
        $codpes = '1234567';
        $nome = 'Fulano USP Sem Email';
        $pessoaMock = Mockery::mock('alias:'.Pessoa::class);
        $pessoaMock->shouldReceive('fetch')->once()->with($codpes)->andReturn(['nompes' => $nome]);
        $pessoaMock->shouldReceive('email')->once()->with($codpes)->andReturn('');

        $response = $this->actingAs($this->adminUser)
                         ->from(route('admin.users.create.usp'))
                         ->post(route('admin.users.store.usp'), ['codpes' => $codpes]);

        $response->assertRedirect(route('admin.users.create.usp'));
        $response->assertSessionHasErrors('codpes');
        $response->assertSessionHasErrors(['codpes' => 'Usuário sem email principal cadastrado no Replicado.']);
        $this->assertDatabaseMissing('users', ['codpes' => $codpes]);
    }

    /**
     * Testa se a criação de usuário USP falha se o CodPes já existir.
     *
     * @return void
     */
    public function test_create_usp_user_fails_if_codpes_already_exists(): void
    {
        $existingUser = User::factory()->create(['codpes' => '1112223']);
        $pessoaMock = Mockery::mock('alias:'.Pessoa::class);
        $pessoaMock->shouldReceive('fetch')->never();
        $pessoaMock->shouldReceive('email')->never();

        $response = $this->actingAs($this->adminUser)
                         ->from(route('admin.users.create.usp'))
                         ->post(route('admin.users.store.usp'), ['codpes' => '1112223']);

        $response->assertRedirect(route('admin.users.create.usp'));
        $response->assertSessionHasErrors('codpes');
    }

    /**
     * Testa se a criação de usuário USP falha se o e-mail já existir.
     *
     * @return void
     */
     public function test_create_usp_user_fails_if_email_already_exists(): void
     {
         $codpes = '7654321';
         $nome = 'Outro Fulano';
         $email = 'existing@usp.br';
         User::factory()->create(['email' => $email]);

         $pessoaMock = Mockery::mock('alias:'.Pessoa::class);
         $pessoaMock->shouldReceive('fetch')->once()->with($codpes)->andReturn(['nompes' => $nome]);
         $pessoaMock->shouldReceive('email')->once()->with($codpes)->andReturn($email);

         $response = $this->actingAs($this->adminUser)
                          ->from(route('admin.users.create.usp'))
                          ->post(route('admin.users.store.usp'), ['codpes' => $codpes]);

         $response->assertRedirect(route('admin.users.create.usp'));
         $response->assertSessionHasErrors('codpes');
         $response->assertSessionHasErrors(['codpes' => 'Usuário já cadastrado no sistema (por Nº USP ou Email).']);
         $this->assertDatabaseMissing('users', ['codpes' => $codpes]);
     }

    /**
     * Testa se um administrador pode visualizar o formulário de criação manual de usuário.
     *
     * @return void
     */
    public function test_admin_can_view_create_manual_user_form(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.users.create.manual'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.users.create_manual');
        $response->assertViewHas('suggestedPassword');
    }

    /**
     * Testa se um administrador pode criar um usuário externo manualmente.
     *
     * @return void
     */
    public function test_admin_can_create_manual_external_user(): void
    {
        Notification::fake();

        $userData = [
            'name' => 'Manual External',
            'email' => 'manual_external@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'codpes' => null,
        ];

        $response = $this->actingAs($this->adminUser)->post(route('admin.users.store.manual'), $userData);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'codpes' => null,
            'email_verified_at' => null,
        ]);
        $newUser = User::where('email', $userData['email'])->first();
        $this->assertTrue($newUser->hasRole('external_user'));
        $this->assertTrue(Hash::check($userData['password'], $newUser->password));

    }

    /**
     * Testa se um administrador pode criar um usuário USP manualmente.
     *
     * @return void
     */
    public function test_admin_can_create_manual_usp_user(): void
    {
         Notification::fake();
         $codpes = '9876543';
         $userData = [
             'name' => 'Manual USP User',
             'email' => 'manual_usp@usp.br',
             'password' => 'password123',
             'password_confirmation' => 'password123',
             'codpes' => $codpes,
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
         $this->assertNotNull($newUser->email_verified_at);
         $this->assertTrue(Hash::check($userData['password'], $newUser->password));
         Notification::assertNothingSent();
    }

    /**
     * Testa se a validação da criação manual de usuário falha com dados inválidos.
     *
     * @return void
     */
    public function test_create_manual_user_validation_fails(): void
    {
        $response = $this->actingAs($this->adminUser)
                         ->from(route('admin.users.create.manual'))
                         ->post(route('admin.users.store.manual'), []);
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
                             'password_confirmation' => 'password456',
                         ]);
        $response->assertSessionHasErrors('password');
    }

    /**
     * Testa se a criação manual de usuário falha com um e-mail existente.
     *
     * @return void
     */
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

    /**
     * Testa se a criação manual de usuário falha com um CodPes existente.
     *
     * @return void
     */
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
                             'codpes' => '5554443',
                         ]);
        $response->assertSessionHasErrors('codpes');
    }

    /**
     * Limpa o ambiente de teste após cada teste.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}