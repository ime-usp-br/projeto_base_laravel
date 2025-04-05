<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Config;
use App\Models\User;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    // $seed = true; // Pode manter ou remover, já que o teste chama o seeder explicitamente

    protected function setUp(): void
    {
        parent::setUp();

        // --- CORREÇÃO: Definir conexão 'web' apontando para sqlite ---
        Config::set('database.connections.web', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        // Forçar a conexão padrão também, como antes
        Config::set('database.default', 'sqlite');
        // --- FIM DA CORREÇÃO ---
    }


    public function test_roles_and_permissions_seeder_creates_roles_and_permissions(): void
    {
        // Executar o seeder
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Asserts (como antes)
        $this->assertDatabaseHas('roles', ['name' => 'admin', 'guard_name' => 'web']);
        $this->assertDatabaseHas('roles', ['name' => 'usp_user', 'guard_name' => 'web']);
        $this->assertDatabaseHas('roles', ['name' => 'external_user', 'guard_name' => 'web']);
        $this->assertEquals(3, Role::on('web')->count());

        $senhaunicaGuard = User::$hierarquiaNs;
        $this->assertDatabaseHas('permissions', ['name' => 'admin', 'guard_name' => $senhaunicaGuard]);
        $this->assertDatabaseHas('permissions', ['name' => 'user', 'guard_name' => $senhaunicaGuard]);
        $this->assertDatabaseHas('permissions', ['name' => 'Servidor', 'guard_name' => $senhaunicaGuard]);
        $this->assertDatabaseHas('permissions', ['name' => 'Alunogr', 'guard_name' => $senhaunicaGuard]);
        $this->assertDatabaseHas('permissions', ['name' => 'Outros', 'guard_name' => $senhaunicaGuard]);

        $expectedPermissionCount = count(User::$permissoesHierarquia) + count(User::$permissoesVinculo);
        $this->assertEquals($expectedPermissionCount, Permission::where('guard_name', $senhaunicaGuard)->count());
    }
}