<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Entities\Traits\Core\ModelHelpers;

class LaravelTestModel extends Model
{
    use ModelHelpers;

    protected $table = 'laravel_test_models';

    protected $fillable = ['name'];
}
