<?php

namespace Unusualify\Modularity\Database\Seeders;

use Illuminate\Database\Seeder;

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
