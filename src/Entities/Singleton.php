<?php

namespace Unusualify\Modularous\Entities;

use Unusualify\Modularous\Facades\Modularous;

class Singleton extends Model
{
    public $fillable = [
        'id',
        'singleton_type',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function getTable()
    {
        return Modularous::config('tables.singletons', 'modularous_singletons');
    }
}
