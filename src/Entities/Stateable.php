<?php

namespace Unusualify\Modularous\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stateable extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'state_id',
        'stateable_id',
        'stateable_type',
    ];

    public $timestamps = true;

    public function state(): BelongsTo
    {
        return $this->belongsTo(modularousConfig('models.state', 'Unusualify\Modularous\Entities\State'));
    }

    public function getTable()
    {
        return modularousConfig('tables.stateables', 'um_stateables');
    }
}
