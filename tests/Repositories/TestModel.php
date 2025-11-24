<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Unusualify\Modularity\Entities\Interfaces\Sortable;
use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasPosition;
use Unusualify\Modularity\Entities\Traits\HasTranslation;

class TestModel extends Model implements Sortable
{
    use HasPosition, HasTranslation;

    protected $table = 'test_models';

    protected $fillable = ['name', 'owner_id', 'is_active', 'description'];

    public $checkboxes = ['is_active'];

    public $nullable = ['description'];

    public $appends = ['owner_name'];

    protected $translationModel = TestModelTranslation::class;

    public $translationForeignKey = 'test_model_id';

    protected $translatedAttributes = ['context'];

    protected function ownerName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->owner ? $this->owner->name : $value,
        );
    }

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

    public function posts(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Post::class, 'postable');
    }
}
