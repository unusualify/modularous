<?php

namespace Unusualify\Modularous\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\SystemUser\Entities\Role;
use Unusualify\Modularous\Facades\Modularous;

class DefaultRolesSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $modularousAuthGuardName = Modularous::getAuthGuardName();

        $roles = [
            [
                'title' => 'Super Admin',
                'name' => 'superadmin',
                'guard_name' => $modularousAuthGuardName,
            ],
            [
                'title' => 'Admin',
                'name' => 'admin',
                'guard_name' => $modularousAuthGuardName,
            ],
            [
                'title' => 'Account Manager',
                'name' => 'manager',
                'guard_name' => $modularousAuthGuardName,
            ],
            [
                'title' => 'Editor',
                'name' => 'editor',
                'guard_name' => $modularousAuthGuardName,
            ],
            [
                'title' => 'Reporter',
                'name' => 'reporter',
                'guard_name' => $modularousAuthGuardName,
            ],
            [
                'title' => 'Client Manager',
                'name' => 'client-manager',
                'guard_name' => $modularousAuthGuardName,
            ],
            [
                'title' => 'Client Assistant',
                'name' => 'client-assistant',
                'guard_name' => $modularousAuthGuardName,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate([
                'name' => $role['name'],
            ], $role);
        }
    }
}
