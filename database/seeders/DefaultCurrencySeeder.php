<?php

namespace Unusualify\Modularity\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Modules\SystemPayment\Entities\PaymentCurrency;

class DefaultCurrencySeeder extends Seeder
{
    public function run()
    {
        $table = config('priceable.tables.currencies');

        $seedArray = [
            [
                'name' => 'Euro',
                'symbol' => '€',
                'iso_4217' => 'EUR',
                'iso_4217_number' => 978,
                'default_vat_rates' => [
                    'corporate' => [
                        'vat_rate_id' => 12,
                    ],
                    'personal' => [
                        'vat_rate_id' => 3,
                    ],
                ],
            ],
            [
                'name' => 'US Dollar',
                'symbol' => '$',
                'iso_4217' => 'USD',
                'iso_4217_number' => 840,
                'default_vat_rates' => [
                    'corporate' => [
                        'vat_rate_id' => 12,
                    ],
                    'personal' => [
                        'vat_rate_id' => 3,
                    ],
                ],
            ],
            [
                'name' => 'Turkish Lira',
                'symbol' => '₺',
                'iso_4217' => 'TRY',
                'iso_4217_number' => 949,
                'default_vat_rates' => [
                    'personal' => [
                        'vat_rate_id' => 1,
                    ],
                ],
            ],
            [
                'name' => 'British Pound',
                'symbol' => '£',
                'iso_4217' => 'GBP',
                'iso_4217_number' => 826,
            ],
            [
                'name' => 'Japanese Yen',
                'symbol' => '¥',
                'iso_4217' => 'JPY',
                'iso_4217_number' => 392,
            ],
            [
                'name' => 'Canadian Dollar',
                'symbol' => 'CA$',
                'iso_4217' => 'CAD',
                'iso_4217_number' => 124,
            ],
            [
                'name' => 'Australian Dollar',
                'symbol' => 'A$',
                'iso_4217' => 'AUD',
                'iso_4217_number' => 036,
            ],
            [
                'name' => 'Swiss Franc',
                'symbol' => 'CHF',
                'iso_4217' => 'CHF',
                'iso_4217_number' => 756,
            ],
            [
                'name' => 'Chinese Yuan',
                'symbol' => '¥',
                'iso_4217' => 'CNY',
                'iso_4217_number' => 156,
            ],
            [
                'name' => 'Swedish Krona',
                'symbol' => 'kr',
                'iso_4217' => 'SEK',
                'iso_4217_number' => 752,
            ],
            [
                'name' => 'New Zealand Dollar',
                'symbol' => 'NZ$',
                'iso_4217' => 'NZD',
                'iso_4217_number' => 554,
            ],
            [
                'name' => 'Mexican Peso',
                'symbol' => 'MX$',
                'iso_4217' => 'MXN',
                'iso_4217_number' => 484,
            ],
            [
                'name' => 'Singapore Dollar',
                'symbol' => 'S$',
                'iso_4217' => 'SGD',
                'iso_4217_number' => 702,
            ],
            [
                'name' => 'Hong Kong Dollar',
                'symbol' => 'HK$',
                'iso_4217' => 'HKD',
                'iso_4217_number' => 344,
            ],
            [
                'name' => 'Norwegian Krone',
                'symbol' => 'kr',
                'iso_4217' => 'NOK',
                'iso_4217_number' => 578,
            ],
            [
                'name' => 'South Korean Won',
                'symbol' => '₩',
                'iso_4217' => 'KRW',
                'iso_4217_number' => 410,
            ],
            [
                'name' => 'Brazilian Real',
                'symbol' => 'R$',
                'iso_4217' => 'BRL',
                'iso_4217_number' => 986,
            ],
            [
                'name' => 'Russian Ruble',
                'symbol' => '₽',
                'iso_4217' => 'RUB',
                'iso_4217_number' => 643,
            ],
            [
                'name' => 'Indian Rupee',
                'symbol' => '₹',
                'iso_4217' => 'INR',
                'iso_4217_number' => 356,
            ],
            [
                'name' => 'South African Rand',
                'symbol' => 'R',
                'iso_4217' => 'ZAR',
                'iso_4217_number' => 710,
            ],
        ];

        foreach ($seedArray as $currency) {
            $paymentCurrency = PaymentCurrency::create(Arr::only($currency, ['name', 'symbol', 'iso_4217', 'iso_4217_number']));

            if (isset($currency['default_vat_rates'])) {
                $paymentCurrency->repeaters()->create([
                    'role' => 'default_vat_rates',
                    'locale' => app()->getFallbackLocale(),
                    'content' => $currency['default_vat_rates'],
                ]);
            }
        }
    }
}
