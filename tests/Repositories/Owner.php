<?php

namespace Unusualify\Modularous\Tests\Repositories;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Unusualify\Modularous\Entities\Model;

class Owner extends Model
{
    protected $table = 'owners';

    protected $fillable = ['name'];

    public function testModels(): HasMany
    {
        return $this->hasMany(TestModel::class);
    }

    public function posts(): MorphMany
    {
        return $this->morphMany(Post::class, 'postable');
    }
}
