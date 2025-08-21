<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class MartPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all roles
        $roles = Role::all();

        // Mart permissions
        $martPermissions = [
            'marts' => [
                'marts',
                'marts.create',
                'marts.edit',
                'marts.view',
                'marts.delete'
            ]
        ];

        foreach ($roles as $role) {
            foreach ($martPermissions as $permission => $routes) {
                foreach ($routes as $route) {
                    Permission::create([
                        'role_id' => $role->id,
                        'permission' => $permission,
                        'routes' => $route
                    ]);
                }
            }
        }
    }
}

