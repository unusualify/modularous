<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Unusualify\Modularity\Entities\Interfaces\Sortable;
use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasFileponds;
use Unusualify\Modularity\Entities\Traits\HasFiles;
use Unusualify\Modularity\Entities\Traits\HasImages;
use Unusualify\Modularity\Entities\Traits\HasPosition;
use Unusualify\Modularity\Entities\Traits\HasPriceable;

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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function testRoles(): BelongsToMany
    {
        return $this->belongsToMany(TestRole::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function posts(): MorphMany
    {
        return $this->morphMany(Post::class, 'postable');
    }

    public function testModelable(): MorphTo
    {
        return $this->morphTo();
    }
}
