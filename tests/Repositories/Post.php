<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasTranslation;

class Post extends Model
{
    use HasTranslation;

    protected $table = 'posts';

    protected $fillable = ['postable_id', 'postable_type', 'position'];

    protected $translationModel = PostTranslation::class;

    public $translatedAttributes = ['title', 'content'];

    public function postable(): MorphTo
    {
        return $this->morphTo();
    }
}
