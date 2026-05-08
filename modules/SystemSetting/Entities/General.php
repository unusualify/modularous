<?php

namespace Modules\SystemSetting\Entities;

use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasImages;
use Unusualify\Modularity\Entities\Traits\HasSpreadable;
use Unusualify\Modularity\Entities\Traits\IsSingular;

class General extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    use HasImages, HasSpreadable, IsSingular;

    /**
     * Override the default `spread_payload` so the trait's
     * `getSpreadableSavingKey()` returns `cont`, matching the input
     * name in Config/config.php and the entry in `$fillable`. Without
     * this, SpreadHydrate would rewrite the input name to
     * `spread_payload` and the form column would silently desync.
     */
    protected static $spreadableSavingKey = 'cont';

    protected $fillable = [
        'name',
        'published',
        'cont',
    ];
}
