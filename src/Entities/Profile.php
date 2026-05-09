<?php

namespace Unusualify\Modularous\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'name',
        'surname',
        'phone',
        'country',
        'language',
        'timezone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }
}
