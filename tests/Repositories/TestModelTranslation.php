<?php

namespace Unusualify\Modularous\Tests\Repositories;

use Unusualify\Modularous\Entities\Model;

class TestModelTranslation extends Model
{
    protected $table = 'test_model_repo_translations';

    protected $baseModuleModel = TestModel::class;
}
