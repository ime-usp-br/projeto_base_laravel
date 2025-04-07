<?php

namespace Tests\Browser\Admin;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserManagementTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $seed = true;
    protected User $adminUser;
    protected User $regularUser;

    /**
     * Prepara o ambiente de teste antes de cada método.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'usp_user', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'external_user', 'guard_name' => 'web']);

        $this->adminUser = User::factory()->create(['name' => 'Admin Test User', 'email_verified_at' => now()]);
        $this->adminUser->assignRole('admin');

        $this->regularUser = User::factory()->create(['name' => 'Regular Test User', 'email_verified_at' => now()]);
        $this->regularUser->assignRole('usp_user');
    }

    /**
     * Testa se um administrador pode acessar o dashboard da área administrativa.
     *
     * @return void
     */
    public function test_admin_can_access_admin_dashboard(): void
    {
        $user = $this->adminUser;
        $this->browse(function (Browser $browser) use ($user) {
            try {
                $browser->loginAs($user)
                        ->assertAuthenticated()
                        ->assertAuthenticatedAs($user)
                        ->visit('/admin/dashboard')
                        ->assertPathIs('/admin/dashboard')
                        ->assertSee('Painel Administrativo')
                        ->assertSeeLink('Listar Usuários')
                        ->assertSeeLink('Criar Usuário USP')
                        ->assertSeeLink('Criar Usuário Manual');
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });
    }

    /**
     * Testa se um administrador pode acessar a lista de usuários.
     *
     * @return void
     */
    public function test_admin_can_view_user_list(): void
    {
        $user = $this->adminUser;
        $regular = $this->regularUser;
        $this->browse(function (Browser $browser) use ($user, $regular) {
            try {
                $browser->loginAs($user)
                        ->assertAuthenticated()
                        ->assertAuthenticatedAs($user)
                        ->visit('/admin/users')
                        ->assertPathIs('/admin/users')
                        ->assertSee('Listar Usuários')
                        ->waitForText($user->name)
                        ->assertSee($user->name)
                        ->assertSee($user->email)
                        ->assertSee($regular->name)
                        ->assertSee($regular->email);
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });
    }

    /**
     * Testa se um administrador pode acessar o formulário de criação de usuário USP.
     *
     * @return void
     */
    public function test_admin_can_access_create_usp_user_form(): void
    {
        $user = $this->adminUser;
        $this->browse(function (Browser $browser) use ($user) {
            try {
                $browser->loginAs($user)
                        ->assertAuthenticated()
                        ->assertAuthenticatedAs($user)
                        ->visit('/admin/users/create/usp')
                        ->assertPathIs('/admin/users/create/usp')
                        ->assertSee('Criar Novo Usuário por Número USP')
                        ->assertPresent('input[name="codpes"]');
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });
    }

    /**
     * Testa se um administrador pode acessar o formulário de criação manual de usuário.
     *
     * @return void
     */
    public function test_admin_can_access_create_manual_user_form(): void
    {
        $user = $this->adminUser;
        $this->browse(function (Browser $browser) use ($user) {
            try {
                $browser->loginAs($user)
                        ->assertAuthenticated()
                        ->assertAuthenticatedAs($user)
                        ->visit('/admin/users/create/manual')
                        ->assertPathIs('/admin/users/create/manual')
                        ->assertSee('Criar Novo Usuário Manualmente')
                        ->assertPresent('input[name="name"]')
                        ->assertPresent('input[name="email"]')
                        ->assertPresent('input[name="codpes"]')
                        ->assertPresent('input[name="password"]')
                        ->assertPresent('input[name="password_confirmation"]');
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });
    }

    /**
     * Testa se um usuário não administrador é impedido de acessar a área administrativa.
     * Espera-se a página 403.
     *
     * @return void
     */
    public function test_non_admin_cannot_access_admin_area(): void
    {
        $user = $this->regularUser;
        $this->browse(function (Browser $browser) use ($user) {
            try {
                $browser->loginAs($user)
                        ->assertAuthenticated()
                        ->assertAuthenticatedAs($user)
                        ->visit('/admin/dashboard')
                        ->waitForText('403', 10)
                        ->assertSee('USER DOES NOT HAVE THE RIGHT ROLES.');
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });

        $this->browse(function (Browser $browser) use ($user) {
            try {
                $browser->loginAs($user)
                        ->assertAuthenticated()
                        ->assertAuthenticatedAs($user)
                        ->visit('/admin/users')
                        ->waitForText('403', 10)
                        ->assertSee('USER DOES NOT HAVE THE RIGHT ROLES.');
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });
    }

    /**
     * Testa a submissão básica do formulário de criação de usuário USP (sem mock Replicado).
     * Verifica a resposta a erros de validação.
     *
     * @return void
     */
    public function test_admin_create_usp_user_form_submission_validation(): void
    {
        $user = $this->adminUser;
        $this->browse(function (Browser $browser) use ($user) {
            try {
                $browser->loginAs($user)
                        ->assertAuthenticated()
                        ->assertAuthenticatedAs($user)
                        ->visit('/admin/users/create/usp')
                        ->assertPathIs('/admin/users/create/usp')
                        ->press('Buscar e Criar Usuário')
                        ->waitForText(__('validation.required', ['attribute' => 'Número USP (CodPes)']), 5)
                        ->assertPathIs('/admin/users/create/usp')
                        ->assertSee(__('validation.required', ['attribute' => 'Número USP (CodPes)']));
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });
    }

    /**
     * Testa a submissão básica do formulário de criação manual de usuário (sem mock Replicado).
     * Verifica a resposta a erros de validação.
     *
     * @return void
     */
    public function test_admin_create_manual_user_form_submission_validation(): void
    {
        $user = $this->adminUser;
        $this->browse(function (Browser $browser) use ($user) {
            try {
                $browser->loginAs($user)
                        ->assertAuthenticated()
                        ->assertAuthenticatedAs($user)
                        ->visit('/admin/users/create/manual')
                        ->assertPathIs('/admin/users/create/manual')
                        ->press('Criar Usuário')
                        ->waitForText(__('validation.required', ['attribute' => 'Nome Completo']), 5)
                        ->assertPathIs('/admin/users/create/manual')
                        ->assertSee(__('validation.required', ['attribute' => 'Nome Completo']))
                        ->assertSee(__('validation.required', ['attribute' => 'E-mail']))
                        ->assertSee(__('validation.required', ['attribute' => 'Senha']));
            } catch (\Throwable $e) {
                $this->captureBrowserHtml($browser, $e);
            }
        });
    }

}