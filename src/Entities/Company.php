<?php

namespace Unusualify\Modularous\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\SystemPayment\Entities\PaymentCountry;
use Modules\SystemUtility\Entities\Country;
use Unusualify\Modularous\Database\Factories\CompanyFactory;
use Unusualify\Modularous\Entities\Traits\HasSpreadable;

class Company extends Model
{
    use HasFactory,
        HasSpreadable;

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

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return CompanyFactory::new();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function paymentCountry(): BelongsTo
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
            get: fn ($value) => $this->company_type == 'personal',
        );
    }

    protected function isCorporateCompany(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->company_type == 'corporate',
        );
    }

    protected function isValid(): Attribute
    {
        return new Attribute(
            get: function ($value) {
                return $this->isPersonalCompany
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
                    );
            }
        );
    }

    protected function isValidFormatted(): Attribute
    {
        return new Attribute(
            get: function ($value) {
                $label = $this->is_valid ? 'Yes' : 'No';
                $color = $this->is_valid ? 'success' : 'error';
                $icon = $this->is_valid ? 'mdi-check' : 'mdi-close';

                return "<v-chip color='{$color}' prepend-icon='{$icon}' variant='text' >{$label}</v-chip>";
            },
        );
    }

    public function getTable()
    {
        return modularousConfig('tables.companies', parent::getTable());
    }
}
