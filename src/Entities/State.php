<?php

namespace Unusualify\Modularous\Entities;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularous\Entities\Traits\HasTranslation;

class State extends Model
{
    use HasTranslation;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'published',
        'code',
        'icon',
        'color',
    ];

    protected $with = [
        'translations',
    ];

    /**
     * The translated attributes that are assignable for hasTranslation Trait.
     *
     * @var array<int, string>
     */
    public $translatedAttributes = [
        'name',
        'active',
    ];

    public function getTable()
    {
        return modularousConfig('tables.states', 'um_states');
    }
}
