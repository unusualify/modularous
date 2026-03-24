<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Unusualify\Modularity\Entities\Model;

class Note extends Model
{
    protected $table = 'notes';

    protected $fillable = ['test_model_id', 'external_id', 'content'];

    public function testModel(): BelongsTo
    {
        return $this->belongsTo(TestModel::class);
    }
}
