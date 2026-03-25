<?php

namespace Modules\SystemPayment\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use Modules\SystemPricing\Entities\Currency;
use Modules\SystemPricing\Entities\VatRate;
use Unusualify\Modularity\Entities\Traits\HasRepeaters;
use Unusualify\Modularity\Entities\Traits\HasSpreadable;

class PaymentCurrency extends Currency
{
    use HasSpreadable, HasRepeaters;

    protected $fillable = [
        'payment_service_id',
        'name',
        'symbol',
        'iso_4217',
        'iso_4217_number',
        'vat_rate_id',
    ];

    protected $with = [
        // 'repeaters'
    ];

    protected $appends = [
        'has_credit_card_payment_service',
        'corporate_vat_rate_name_with_rate',
        'personal_vat_rate_name_with_rate',
    ];

    /**
     * The paymentServices that belong to the Currency.
     */
    public function paymentServices(): BelongsToMany
    {
        return $this->belongsToMany(PaymentService::class);
    }

    public function paymentService(): BelongsTo
    {
        return $this->belongsTo(PaymentService::class, 'payment_service_id', 'id');
    }

    protected function hasCreditCardPaymentService(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->relationLoaded('paymentService') && $this->paymentService
                ? $this->paymentService->published && $this->paymentService->is_internal
                : $this->paymentService()->published()->isInternal()->count() > 0,
        );
    }

    protected function hasBuiltInForm(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->paymentService ? $this->paymentService->hasBuiltInForm : false,
        );
    }

    protected function companyVatRates(): Attribute
    {
        $locale = app()->getFallbackLocale();

        return Attribute::make(
            get: function ($value) use ($locale) {
                $company_vat_rates = $this->getRepeaterField('default_vat_rates', $locale, default: []);
                $companyVatRates = collect();
                foreach ($company_vat_rates as $company_type => $object) {
                    if (isset($object['vat_rate_id']) && ($vatRate = VatRate::find($object['vat_rate_id']))) {
                        $companyVatRates->push((object) [
                            'company_type' => $company_type,
                            'vat_rate_id' => $vatRate->id,
                            'vatRate' => $vatRate,
                        ]);
                    }
                }

                return $companyVatRates;
            },
        );
    }

    protected function corporateVatRate(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                return $this->companyVatRates->firstWhere('company_type', 'corporate')?->vatRate ?? null;
            },
        );
    }

    protected function corporateVatRateNameWithRate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->corporateVatRate ? $this->corporateVatRate->name_with_rate : '',
        );
    }

    protected function personalVatRate(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                return $this->companyVatRates->firstWhere('company_type', 'personal')?->vatRate ?? null;
            },
        );
    }

    protected function personalVatRateNameWithRate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->personalVatRate ? $this->personalVatRate->name_with_rate : '',
        );
    }

    /**
     * Check if the currency has a corporate VAT rate.
     *
     * @return bool
     */
    public function hasUserCorporateVatRate()
    {
        $currencyVatRates = get_user_currency_vat_rates();
        $currencyVatRate = $currencyVatRates->firstWhere('payment_currency_id', $this->id);

        return $currencyVatRate ? true : false;
    }

    public function isUserCorporateVatRate()
    {
        return $this->hasUserCorporateVatRate() && ($user = Auth::guard('modularity')->user()) && $user->isClient() && ($user->validCompany) && $user->company->isCorporateCompany;
    }

    /**
     * Check if the currency has a company VAT rate for personal companies.
     *
     * @return bool
     */
    public function hasCompanyVatRate()
    {
        return $this->hasUserCorporateVatRate() || ((bool) $this->personalVatRate ? true : false);
    }

    /**
     * Get the corporate VAT rate for the currency for corporate companies.
     *
     * @return VatRate|null
     */
    public function getUserCorporateVatRate()
    {
        $currencyVatRates = get_user_currency_vat_rates();
        $currencyVatRate = $currencyVatRates->firstWhere('payment_currency_id', $this->id);

        return $currencyVatRate ? $currencyVatRate?->vatRate : null;
    }

    /**
     * Set the corporate VAT rate for the currency.
     *
     * @return $this
     */
    public function setCorporateVatRate()
    {
        $corporateVatRate = $this->getUserCorporateVatRate();

        if (! $corporateVatRate) {
            $corporateVatRate = $this->corporateVatRate;
        }

        $this->companyVatRate = $corporateVatRate
            ? $corporateVatRate
            : ($this->companyVatRate ?? null);

        return $this;
    }

    /**
     * Set the company VAT rate for the currency.
     *
     * @return $this
     */
    public function setCompanyVatRate()
    {
        if (Auth::guard('modularity')->check() && ($user = Auth::guard('modularity')->user()) && $user->is_client && ($user->validCompany)) {
            if ($user->company->isCorporateCompany) {
                $this->setCorporateVatRate();
            } elseif ($user->company->isPersonalCompany) {
                $this->companyVatRate = $this->personalVatRate;
            }
        } else {
            $this->companyVatRate = null;
        }

        return $this;
    }

    public function scopeDefaultCorporatePaymentCurrency($query): Builder
    {
        return $query->whereHas('repeaters', function ($query) {
            $query->whereRole('default_vat_rates')->whereJsonContainsKey('content->corporate');
        });
    }

    public function scopeDefaultPersonalPaymentCurrency($query): Builder
    {
        return $query->whereHas('repeaters', function ($query) {
            $query->whereRole('default_vat_rates')->whereJsonContainsKey('content->personal');
        });
    }

    public function scopeHasStandartPaymentService($query)
    {
        return $query->whereHas('paymentServices');
    }

    public function scopeHasCreditCardPaymentService($query)
    {
        return $query->whereHas('paymentService');
    }

    public function scopeHasAnyPaymentService($query)
    {
        return $query->whereHas('paymentServices')->orWhereHas('paymentService');
    }
}
