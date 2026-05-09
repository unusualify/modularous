<?php

namespace Unusualify\Modularous\Entities\Translations;

use Unusualify\Modularous\Entities\Model;

class StateTranslation extends Model
{
    protected $fillable = [
        'name',
        'active',
        'locale',
    ];

    public function getTable()
    {
        return modularousConfig('tables.state_translations', 'um_state_translations');
    }
}
