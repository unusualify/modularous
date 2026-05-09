<?php

namespace Unusualify\Modularous\Entities;

use Illuminate\Support\Str;
use Unusualify\Modularous\Entities\Traits\HasImages;
use Unusualify\Modularous\Entities\Traits\HasTranslation;

class Setting extends Model
{
    use HasImages, HasTranslation;

    public $useTranslationFallback = true;

    protected $fillable = [
        'key',
        'section',
    ];

    public $translatedAttributes = [
        'value',
        'locale',
        'active',
    ];

    public function getTranslationModelNameDefault()
    {
        return "Unusualify\Modularous\Entities\Translations\SettingTranslation";
    }

    public function getTable()
    {
        return modularousConfig('settings_table', 'twill_settings');
    }

    protected function getTranslationRelationKey(): string
    {
        return Str::singular(modularousConfig('settings_table', 'twill_settings')) . '_id';
    }
}
