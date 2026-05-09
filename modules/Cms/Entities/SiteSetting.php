<?php

namespace Modules\Cms\Entities;

use Unusualify\Modularous\Entities\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'group_key',
        'key',
        'locale',
        'value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getTable(): string
    {
        return modularousConfig('tables.cms_site_settings', 'um_cms_site_settings');
    }
}
