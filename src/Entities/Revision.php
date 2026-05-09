<?php

namespace Unusualify\Modularous\Entities;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Unusualify\Modularous\Entities\Enums\RevisionStatus;

abstract class Revision extends BaseModel
{
    public $timestamps = true;

    protected $with = ['user'];

    protected $fillable = [
        'payload',
        'user_id',
        'source_id',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Remember to update this if you add fields to the fillable array here
        // this is to allow child classes to provide a custom foreign key in fillable
        if (count($this->fillable) == 3) {
            $this->fillable[] = mb_strtolower(str_replace('Revision', '', get_called_class())) . '_id';
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Parent revision this row was branched from (e.g. after a restore, points at the snapshot that was applied).
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(static::class, 'source_id');
    }

    public function getByUserAttribute()
    {
        return isset($this->user) ? $this->user->name : 'System';
    }

    public function isDraft(): bool
    {
        $data = json_decode($this->payload, true);

        $cmsSaveType = $data['cmsSaveType'] ?? '';

        return Str::startsWith($cmsSaveType, 'draft-revision');
    }

    public function isPending(): bool
    {
        $status = $this->status ?? RevisionStatus::Approved->value;

        return $status === RevisionStatus::Pending->value;
    }

    public function isApproved(): bool
    {
        $status = $this->status ?? RevisionStatus::Approved->value;

        return $status === RevisionStatus::Approved->value;
    }

    public function isRejected(): bool
    {
        $status = $this->status ?? RevisionStatus::Approved->value;

        return $status === RevisionStatus::Rejected->value;
    }
}
