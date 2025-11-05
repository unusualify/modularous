<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Unusualify\Modularity\Entities\Traits\Core\ModelHelpers;

class LaravelTestModel extends \Illuminate\Database\Eloquent\Model
{
    use ModelHelpers;

    protected $table = 'laravel_test_models';
    protected $fillable = ['name'];
}
