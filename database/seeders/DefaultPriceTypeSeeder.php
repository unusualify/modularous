<?php

namespace Unusualify\Modularity\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultPriceTypeSeeder extends Seeder
{
    public function run()
    {
        $priceTypes = [
            [
                'name' => 'Default Price Type',
                'slug' => 'default-price-type',
            ],
        ];

        foreach ($priceTypes as $priceType) {
            \Modules\SystemPricing\Entities\PriceType::updateOrCreate(
                ['slug' => $priceType['slug']],
                $priceType,
            );
        }
    }
}
