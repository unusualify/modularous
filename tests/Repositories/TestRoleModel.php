<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Unusualify\Modularity\Entities\Model;

class TestRole extends Model
{
    protected $table = 'test_roles';

    protected $fillable = ['name'];

    public function testModels(): BelongsToMany
    {
        return $this->belongsToMany(TestModel::class);
    }
}
