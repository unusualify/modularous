<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Unusualify\Modularity\Entities\Model;

class SecondMorph extends Model
{
    protected $fillable = [
        'name',
    ];

    public function testModels(): MorphMany
    {
        return $this->morphMany(TestModel::class, 'test_modelable');
    }
}
