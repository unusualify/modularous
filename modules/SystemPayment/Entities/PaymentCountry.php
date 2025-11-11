<?php

namespace Modules\SystemPayment\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Modules\SystemPricing\Entities\VatRate;
use Unusualify\Modularity\Entities\Traits\HasRepeaters;
use Unusualify\Modularity\Entities\Traits\HasSpreadable;

class PaymentCountry extends \Modules\SystemUtility\Entities\Country
{
    use HasSpreadable, HasRepeaters;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'published',
        'code',
        'phone_code',
    ];

    protected $translationModel = \Modules\SystemUtility\Entities\Translations\CountryTranslation::class;

    protected $translationForeignKey = 'country_id';

    protected $appends = [
        'currency_vat_rates',
    ];

    protected function hasCurrencyRates(): Attribute
    {
        $locale = app()->getFallbackLocale();

        return Attribute::make(
            get: fn ($value) => $this->hasRepeaterValue('currency_vat_rates', $locale),
        );
    }

    protected function currencyVatRates(): Attribute
    {
        $locale = app()->getFallbackLocale();

        return Attribute::make(
            get: function ($value) use ($locale) {
                $currency_vat_rate = $this->getRepeaterField('currency_vat_rate', $locale, default: []);
                $currencyVatRates = collect();
                foreach ($currency_vat_rate as $currencyIso4217 => $object) {
                    if (isset($object['vat_rate_id']) && ($paymentCurrency = PaymentCurrency::where('iso_4217', $currencyIso4217)->first()) && ($vatRate = VatRate::find($object['vat_rate_id']))) {
                        $currencyVatRates->push((object) [
                            'iso_4217' => $currencyIso4217,
                            'payment_currency_id' => $paymentCurrency->id,
                            'paymentCurrency' => $paymentCurrency,
                            'vat_rate_id' => $vatRate->id,
                            'vatRate' => $vatRate,
                        ]);
                    }
                }

                return $currencyVatRates;
            },
        );
    }

    protected function EURVatRate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->currencyVatRates->firstWhere('iso_4217', 'EUR')?->vatRate?->name_with_rate ?? null,
        );
    }

    protected function USDVatRate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->currencyVatRates->firstWhere('iso_4217', 'USD')?->vatRate?->name_with_rate ?? null,
        );
    }

    protected function TRYVatRate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->currencyVatRates->firstWhere('iso_4217', 'TRY')?->vatRate?->name_with_rate ?? null,
        );
    }
}
