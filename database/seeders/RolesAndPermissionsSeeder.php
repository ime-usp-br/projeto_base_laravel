<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Executa os seeders do banco de dados para papéis e permissões.
     *
     * @return void
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('usp_user', 'web');
        Role::findOrCreate('external_user', 'web');

        $senhaunicaGuardHierarquia = User::$hierarquiaNs;
        $senhaunicaGuardVinculo = User::$vinculoNs;

        foreach (User::$permissoesHierarquia as $permissionName) {
            Permission::findOrCreate($permissionName, $senhaunicaGuardHierarquia);
        }

        foreach (User::$permissoesVinculo as $permissionName) {
            Permission::findOrCreate($permissionName, $senhaunicaGuardVinculo);

        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}