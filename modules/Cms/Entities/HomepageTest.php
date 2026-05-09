<?php

namespace Modules\Cms\Entities;

use Modules\Cms\Entities\Concerns\IsCmr;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Traits\HasFileponds;
use Unusualify\Modularous\Entities\Traits\HasTranslatableMetadata;
use Unusualify\Modularous\Entities\Traits\IsSingular;
use Unusualify\Modularous\Entities\Traits\Publishable;

class HomepageTest extends Model
{
    use HasFileponds,
        IsSingular,
        IsCmr,
        HasTranslatableMetadata,
        Publishable;

    public bool $usePublishDates = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'published',
    ];
}
