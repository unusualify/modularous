<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Unusualify\Modularity\Entities\Interfaces\Sortable;
use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasFileponds;
use Unusualify\Modularity\Entities\Traits\HasFiles;
use Unusualify\Modularity\Entities\Traits\HasImages;
use Unusualify\Modularity\Entities\Traits\HasPosition;
use Unusualify\Modularity\Entities\Traits\HasPriceable;
use Unusualify\Modularity\Entities\Traits\HasTranslation;

class TestModel extends Model implements Sortable
{
    use HasPosition, HasPriceable, HasFiles, HasImages, HasFileponds;

    protected $table = 'test_models';

    protected $fillable = ['name', 'owner_id', 'is_active', 'description', 'published', 'public', 'publish_start_date', 'publish_end_date'];

    public $checkboxes = ['is_active'];

    public $nullable = ['description'];

    public $appends = ['owner_name'];

    protected $translationModel = TestModelTranslation::class;

    public $translationForeignKey = 'test_model_id';

    protected $translatedAttributes = ['context', 'active'];

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

    public function testModelable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
