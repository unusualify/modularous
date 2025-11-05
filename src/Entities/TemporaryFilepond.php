<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Model;

class TemporaryFilepond extends Model
{
    protected $fillable = [
        'file_name',
        'input_role',
        'folder_name',
    ];

    public static function booted()
    {
        static::creating(function ($model) {
            $model->folder_name ??= uniqid('', true);
        });
    }

    public function getTable()
    {
        return modularityConfig('tables.filepond_temporaries', parent::getTable());
    }
}
