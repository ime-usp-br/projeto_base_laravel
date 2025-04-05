<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User; // Import User model to access trait properties

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions before seeding
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- Create Roles for 'web' guard (as before) ---
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('usp_user', 'web');
        Role::findOrCreate('external_user', 'web');

        // --- CORRECTION: Create Permissions for 'senhaunica' guard ---
        $senhaunicaGuardHierarquia = User::$hierarquiaNs; // Default 'senhaunica'
        $senhaunicaGuardVinculo = User::$vinculoNs;     // Default 'senhaunica'

        // Create Hierarchical Permissions from HasSenhaunica trait
        foreach (User::$permissoesHierarquia as $permissionName) {
            Permission::findOrCreate($permissionName, $senhaunicaGuardHierarquia);
        }

        // Create Vinculo Permissions from HasSenhaunica trait
        foreach (User::$permissoesVinculo as $permissionName) {
            Permission::findOrCreate($permissionName, $senhaunicaGuardVinculo);
            // Note: This doesn't create the dynamic '.setor' or '.codare' permissions,
            // as those depend on specific user data not available during seeding.
            // Tests requiring these specific dynamic permissions might need
            // to create them on-the-fly if not handled by the application logic itself.
        }

        // --- Reset Cache Again (Optional but safe) ---
        // It's generally best practice to reset the cache *after* all
        // roles and permissions are defined within the seeder run.
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}