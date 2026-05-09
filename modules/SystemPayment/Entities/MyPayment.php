<?php

namespace Modules\SystemPayment\Entities;

use Unusualify\Modularous\Entities\Traits\Core\ModelHelpers;

class MyPayment extends Payment
{
    use ModelHelpers;

    public static $creatableClass = Payment::class;

    protected $filepondableClass = Payment::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'published',
    ];
}
