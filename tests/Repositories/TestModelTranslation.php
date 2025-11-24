<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Unusualify\Modularity\Entities\Model;

class TestModelTranslation extends Model
{
    protected $table = 'test_model_repo_translations';

    protected $baseModuleModel = TestModel::class;
}
