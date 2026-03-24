<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CreatorRecord extends Model
{
    protected $fillable = [
        'id',
        'creator_type',
        'creator_id',
        'guard_name',
        'creatable_type',
        'creatable_id',
    ];

    public $timestamps = false;

    /**
     * get the parent creatable model
     */
    public function creatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): MorphTo
    {
        return $this->morphTo();
    }

    public function getTable()
    {
        return modularityConfig('tables.creator_records', 'um_creator_records');
    }
}
