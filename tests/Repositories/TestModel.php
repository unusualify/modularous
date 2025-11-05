<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasPosition;

class TestModel extends Model
{
    use HasPosition;

    protected $table = 'test_models';

    protected $fillable = ['name', 'owner_id', 'is_active', 'description'];

    public $checkboxes = ['is_active'];

    public $nullable = ['description'];

    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function testRoles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(TestRole::class);
    }

    public function notes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Note::class);
    }
}
