<?php

namespace Modules\SystemPricing\Entities;

use Unusualify\Modularous\Entities\Traits\Core\ModelHelpers;

class Currency extends \Oobook\Priceable\Models\Currency
{
    use ModelHelpers;

    protected $fillable = [
        'name',
        'symbol',
        'iso_4217',
        'iso_4217_number',
    ];

    public function scopeEnabled($query)
    {
        return $query->whereIn('iso_4217', modularousConfig('enabled_currencies'));
    }
}
