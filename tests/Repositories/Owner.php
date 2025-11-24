<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Unusualify\Modularity\Entities\Model;

class Owner extends Model
{
    protected $table = 'owners';

    protected $fillable = ['name'];

    public function testModels(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TestModel::class);
    }

    public function posts(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Post::class, 'postable');
    }
}
