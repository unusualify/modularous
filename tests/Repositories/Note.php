<?php

namespace Unusualify\Modularity\Tests\Repositories;

class Note extends \Unusualify\Modularity\Entities\Model
{
    protected $table = 'notes';

    protected $fillable = ['test_model_id', 'external_id', 'content'];

    public function testModel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TestModel::class);
    }
}
