<?php

namespace Unusualify\Modularity\Tests\Repositories;

class Note extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'notes';

    protected $fillable = ['test_model_id', 'external_id'];

    public function testModel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TestModel::class);
    }
}
