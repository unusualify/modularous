<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\SystemPayment\Entities\PaymentCountry;
use Modules\SystemUtility\Entities\Country;
use Unusualify\Modularity\Database\Factories\CompanyFactory;
use Unusualify\Modularity\Entities\Traits\HasSpreadable;

class Company extends Model
{
    use HasFactory,
        HasSpreadable;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return CompanyFactory::new();
    }

    protected $fillable = [
        'id',
        'name',
        'address',
        'city',
        'state',
        'country_id',
        'zip_code',
        'phone',
        'vat_number',
        'tax_id',
    ];

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }

    public function country(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function paymentCountry(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PaymentCountry::class, 'country_id');
    }

    protected function countryName(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->country ? $this->country->name : null,
        );
    }

    protected function companyType(): Attribute
    {
        return new Attribute(
            get: function ($value) {
                $companyType = 'corporate';

                try {
                    $companyType = $this->is_personal ? 'personal' : $companyType;
                } catch (\Exception $e) {

                }

                return $companyType;
            },
        );
    }

    protected function isPersonalCompany(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->companyType == 'personal',
        );
    }

    protected function isCorporateCompany(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->companyType == 'corporate',
        );
    }

    protected function isValid(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->isPersonalCompany
                ? ! (! $this->address
                || ! $this->city
                || ! $this->state
                || ! $this->zip_code
                || ! $this->country_id
                )
                : ! (! $this->name
                    || ! $this->tax_id
                    || ! $this->email
                    || ! $this->address
                    || ! $this->country_id
                    || ! $this->city
                    || ! $this->state
                    || ! $this->zip_code
                )
        );
    }

    public function getTable()
    {
        return modularityConfig('tables.companies', parent::getTable());
    }
}
