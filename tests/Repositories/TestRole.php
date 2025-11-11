<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Unusualify\Modularity\Entities\Model;

class TestRole extends Model
{
    protected $table = 'test_roles';

    protected $fillable = ['name'];

    public function testModels(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(TestModel::class);
    }
}
