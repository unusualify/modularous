<?php

namespace Modules\Cms\Repositories;

use Modules\Cms\Entities\HomepageTest;
use Modules\Cms\Repositories\Traits\CmrTrait;
use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\FilepondsTrait;
use Unusualify\Modularity\Repositories\Traits\PublishableTrait;
use Unusualify\Modularity\Repositories\Traits\TranslatableMetadataTrait;

class HomepageTestRepository extends Repository
{
    use FilepondsTrait,
        CmrTrait,
        TranslatableMetadataTrait,
        PublishableTrait;

    public function __construct(HomepageTest $model)
    {
        $this->model = $model;
    }
}
