<?php

namespace Unusualify\Modularous\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\SystemPayment\Database\Seeders\SystemPaymentDatabaseSeeder;

class DefaultDatabaseSourceSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DefaultRolesSeeder::class,
            DefaultPermissionsSeeder::class,
        ]);
    }
}
