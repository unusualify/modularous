<?php

namespace Unusualify\Modularity\Tests\Repositories;

class SecondMorph extends \Unusualify\Modularity\Entities\Model
{
    protected $fillable = [
        'name',
    ];

    public function testModels(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(TestModel::class, 'test_modelable');
    }
}
