<?php

namespace Unusualify\Modularous\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Spread extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'spreadable_id',
        'spreadable_type',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function spreadable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getTable()
    {
        return modularousConfig('tables.spreads', 'um_spreads');
    }
}
