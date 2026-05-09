<?php

namespace Unusualify\Modularous\Entities\Translations;

use Illuminate\Support\Str;
use Unusualify\Modularous\Entities\Model;

class SettingTranslation extends Model
{
    protected $fillable = [
        'value',
        'active',
        'locale',
    ];

    public function getTable()
    {
        $twillSettingsTable = modularousConfig('settings_table', 'twill_settings');

        return Str::singular($twillSettingsTable) . '_translations';
    }
}
