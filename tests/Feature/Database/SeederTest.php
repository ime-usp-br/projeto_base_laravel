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



    /**
     * Configura o ambiente de teste antes de cada teste.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();


        Config::set('database.connections.web', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        Config::set('database.default', 'sqlite');

    }


    /**
     * Testa se o seeder de papéis e permissões cria os papéis e permissões esperados.
     *
     * @return void
     */
    public function test_roles_and_permissions_seeder_creates_roles_and_permissions(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

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