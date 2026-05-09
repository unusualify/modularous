<?php

namespace Unusualify\Modularous\Entities;

use Modules\SystemNotification\Events\FilepondCreated;
use Modules\SystemNotification\Events\FilepondDeleted;
use Modules\SystemNotification\Events\FilepondUpdated;
use Unusualify\Modularous\Facades\Filepond as FilepondFacade;

class Filepond extends Model
{
    /**
     * Preview / hydrate only: true when the row is built for a pending revision and the UUID still exists only
     * as a {@see TemporaryFilepond} (not persisted on the subject yet). Not stored in the database.
     */
    public bool $isTemporaryRevisionPreview = false;

    protected $fillable = [
        'uuid',
        'file_name',
        'filepondable_id',
        'filepondable_type',
        'role',
        'locale',
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            FilepondCreated::dispatch($model);
        });

        static::updated(function ($model) {
            FilepondUpdated::dispatch($model);
        });

        static::deleted(function ($model) {
            FilepondDeleted::dispatch($model);
        });
    }

    public function canDeleteSafely()
    {
        // return DB::table(modularousConfig('tables.fileponds'))->where('file_id', $this->id)->count() === 0;
    }

    public function filepondable()
    {
        return $this->morphTo();
    }

    public function mediableFormat()
    {
        return [
            'uuid' => $this->uuid,
            'file_name' => $this->file_name,
            'source' => route('filepond.preview', ['uuid' => $this->uuid]),
            'created_at' => $this->created_at,
            'file' => FilepondFacade::getFileInfo($this->uuid),
            // 'source' => $this->uuid,
            // 'source' => $this->uuid . '/' .  $this->file_name,
        ];
    }

    public function getTable()
    {
        return modularousConfig('tables.fileponds', parent::getTable());
    }
}
