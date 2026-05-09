<?php

namespace Unusualify\Modularous\Tests\Repositories;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Unusualify\Modularous\Entities\Model;

class TestRole extends Model
{
    protected $table = 'test_roles';

    protected $fillable = ['name', 'position'];

    public function testModels(): BelongsToMany
    {
        return $this->belongsToMany(TestModel::class);
    }
}
