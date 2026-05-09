<?php

namespace Unusualify\Modularous\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\SystemPayment\Database\Seeders\SystemPaymentDatabaseSeeder;

class DefaultDatabaseUtilitySeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DefaultCurrencySeeder::class,
            DefaultVatRateSeeder::class,
            DefaultPriceTypeSeeder::class,
            DefaultCountrySeeder::class,
            SystemPaymentDatabaseSeeder::class,
        ]);
    }
}
