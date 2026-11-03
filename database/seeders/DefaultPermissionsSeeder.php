<?php

namespace Unusualify\Modularous\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Unusualify\Modularous\Facades\Modularous;

class DefaultPermissionsSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $table = config('permission.table_names.permissions');

        Schema::disableForeignKeyConstraints();

        DB::table($table)->truncate();

        Schema::enableForeignKeyConstraints();

        $modularousAuthGuardName = Modularous::getAuthGuardName();
        DB::table($table)->insert([
            [
                'name' => 'dashboard',
                'guard_name' => $modularousAuthGuardName,
            ],
            [
                'name' => 'mediaLibrary',
                'guard_name' => $modularousAuthGuardName,
            ],
            ...permissionRecordsFromRoutes([
                'SystemUser' => ['User', 'Role', 'Permission', 'Company'],
                'SystemPricing' => ['VatRate', 'Currency', 'PriceType'],
                'SystemPayment' => ['Payment'],
                'SystemNotification' => ['Notification', 'MyNotification'],
                'SystemUtility' => ['Country'],
            ], $modularousAuthGuardName),
        ]);

        $roleInstances = Role::all()->keyBy('name');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Assign permissions to roles
        // Admin gets most permissions except some sensitive ones
        $roleInstances['admin']->givePermissionTo(Permission::all()->except([
            'system_notification::my-notification_view', 'system_notification::my-notification_edit', 'system_notification::my-notification_delete', 'system_notification::my-notification_bulkDelete', 'system_notification::my-notification_forceDelete', 'system_notification::my-notification_bulkForceDelete',
            'system_user::user_forceDelete', 'system_user::user_bulkForceDelete',
            'system_user::role_forceDelete', 'system_user::role_bulkForceDelete',
            'system_user::company_forceDelete', 'system_user::company_bulkForceDelete',
            // 'permission_forceDelete', 'permission_bulkForceDelete',
            'system_pricing::vat-rate_forceDelete', 'system_pricing::vat-rate_bulkForceDelete',
            'system_pricing::currency_forceDelete', 'system_pricing::currency_bulkForceDelete',
            'system_pricing::price-type_forceDelete', 'system_pricing::price-type_bulkForceDelete',
            'system_payment::payment_forceDelete', 'system_payment::payment_bulkForceDelete',
            'system_utility::country_forceDelete', 'system_utility::country_bulkForceDelete',
        ]));

        // Manager gets operational permissions
        $roleInstances['manager']->givePermissionTo([
            'dashboard', 'mediaLibrary',
            'system_notification::my-notification_view', 'system_notification::my-notification_edit', 'system_notification::my-notification_delete', 'system_notification::my-notification_bulkDelete', 'system_notification::my-notification_forceDelete', 'system_notification::my-notification_bulkForceDelete',
            'system_user::user_view', 'system_user::user_create', 'system_user::user_edit',
            'system_pricing::vat-rate_view', 'system_pricing::vat-rate_create', 'system_pricing::vat-rate_edit',
            'system_pricing::currency_view', 'system_pricing::currency_create', 'system_pricing::currency_edit',
            'system_pricing::price-type_view', 'system_pricing::price-type_create', 'system_pricing::price-type_edit',
            'system_payment::payment_view', 'system_payment::payment_create', 'system_payment::payment_edit',
            'system_utility::country_view', 'system_utility::country_create', 'system_utility::country_edit',
        ]);

        // Editor gets content-related permissions
        $roleInstances['editor']->givePermissionTo([
            'dashboard', 'mediaLibrary',
            'system_notification::my-notification_view', 'system_notification::my-notification_edit', 'system_notification::my-notification_delete', 'system_notification::my-notification_bulkDelete', 'system_notification::my-notification_forceDelete', 'system_notification::my-notification_bulkForceDelete',
            'system_utility::country_view',
        ]);

        // Reporter gets view-only permissions
        $roleInstances['reporter']->givePermissionTo([
            'dashboard', 'mediaLibrary',
            'system_notification::my-notification_view', 'system_notification::my-notification_edit', 'system_notification::my-notification_delete', 'system_notification::my-notification_bulkDelete', 'system_notification::my-notification_forceDelete', 'system_notification::my-notification_bulkForceDelete',
            'system_utility::country_view',
        ]);

        // Client Manager gets client-specific permissions
        $roleInstances['client-manager']->givePermissionTo([
            'dashboard',
            'system_notification::my-notification_view', 'system_notification::my-notification_edit', 'system_notification::my-notification_delete', 'system_notification::my-notification_bulkDelete', 'system_notification::my-notification_forceDelete', 'system_notification::my-notification_bulkForceDelete',
            'system_payment::payment_view', 'system_payment::payment_create',
            'system_utility::country_view',
        ]);

        // Client Assistant gets limited client permissions
        $roleInstances['client-assistant']->givePermissionTo([
            'dashboard',
            'system_notification::my-notification_view', 'system_notification::my-notification_edit', 'system_notification::my-notification_delete', 'system_notification::my-notification_bulkDelete', 'system_notification::my-notification_forceDelete', 'system_notification::my-notification_bulkForceDelete',
            'system_payment::payment_view',
            'system_utility::country_view',
        ]);

    }
}
