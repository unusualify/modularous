<?php

namespace Modules\Cms\Entities;

use Unusualify\Modularity\Entities\Model;

class Redirect extends Model
{
    protected $fillable = [
        'from_path',
        'to_path',
        'locale',
        'status_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'status_code' => 'integer',
    ];

    public function getTable(): string
    {
        return modularityConfig('tables.cms_redirects', 'um_cms_redirects');
    }
}
