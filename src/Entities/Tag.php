<?php

namespace Unusualify\Modularity\Entities;

use Cartalyst\Tags\IlluminateTag;

class Tag extends IlluminateTag
{
    protected static $taggedModel = Tagged::class;

    /**
     * Override fillable to include locale for localized tags.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'count',
        'namespace',
        'locale',
    ];

    public function getTable()
    {
        return modularityConfig('tables.tags', parent::getTable());
    }
}
