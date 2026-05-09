<?php

namespace Unusualify\Modularous\Tests\Repositories;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularous\Entities\Traits\Core\ModelHelpers;

class LaravelTestModel extends Model
{
    use ModelHelpers;

    protected $table = 'laravel_test_models';

    protected $fillable = ['name'];
}
