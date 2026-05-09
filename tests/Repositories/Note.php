<?php

namespace Unusualify\Modularous\Tests\Repositories;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Unusualify\Modularous\Entities\Model;

class Note extends Model
{
    protected $table = 'notes';

    protected $fillable = ['test_model_id', 'external_id', 'content'];

    public function testModel(): BelongsTo
    {
        return $this->belongsTo(TestModel::class);
    }
}
