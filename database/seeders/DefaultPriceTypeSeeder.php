<?php

namespace Unusualify\Modularity\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\SystemPricing\Entities\PriceType;

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
            PriceType::updateOrCreate(
                ['slug' => $priceType['slug']],
                $priceType,
            );
        }
    }
}
