<?php

namespace Unusualify\Modularous\Tests\Repositories;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Unusualify\Modularous\Entities\Model;

class FirstMorph extends Model
{
    protected $fillable = [
        'name',
    ];

    public function testModels(): MorphMany
    {
        return $this->morphMany(TestModel::class, 'test_modelable');
    }
}
