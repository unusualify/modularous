<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Unusualify\Modularity\Entities\Model;

class Post extends Model
{
    use \Unusualify\Modularity\Entities\Traits\HasTranslation;

    protected $table = 'posts';

    protected $fillable = ['postable_id', 'postable_type'];

    protected $translationModel = PostTranslation::class;

    public $translatedAttributes = ['title', 'content'];

    public function postable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
