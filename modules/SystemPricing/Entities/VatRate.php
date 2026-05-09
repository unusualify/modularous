<?php

namespace Modules\SystemPricing\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Unusualify\Modularous\Entities\Traits\Core\ModelHelpers;

class VatRate extends \Oobook\Priceable\Models\VatRate
{
    use ModelHelpers;

    public $fillable = [
        'name',
        'slug',
        'rate',
    ];

    protected $appends = [
        'name_with_rate',
        'vat_multiplier',
        'vat_percentage',
    ];

    protected function nameWithRate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->name . ' (' . $this->rate . '%)',
        );
    }

    protected function vatMultiplier(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->rate / 100,
        );
    }

    protected function vatPercentage(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->rate,
        );
    }
}
