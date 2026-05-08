<?php

namespace Modules\Cms\Entities;

use Modules\Cms\Entities\Concerns\IsCmr;
use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasFileponds;
use Unusualify\Modularity\Entities\Traits\HasTranslatableMetadata;
use Unusualify\Modularity\Entities\Traits\IsSingular;
use Unusualify\Modularity\Entities\Traits\Publishable;

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
