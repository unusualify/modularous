<?php

namespace Modules\SystemNotification\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\User;

class Notification extends Model
{
    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    protected $appends = [
        'is_read',
        'is_mine',
        'subject',
        'message',
        'html_message',
        'redirector',
        'has_redirector',
        'redirector_text',
    ];

    /**
     * Get the notifiable entity that the notification belongs to.
     *
     * @return MorphTo
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    /**
     * Notifiable owner, eager-loaded with the fields the admin recipient card needs.
     *
     * The company name is selected via subquery: loading Company models would trigger a
     * per-row spreadable lookup (HasSpreadable `retrieved` hook).
     */
    public function recipient(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'notifiable_type', 'notifiable_id')
            ->morphWith([
                User::class => [
                    'fileponds' => fn ($query) => $query->where('role', 'avatar'),
                ],
            ])
            ->constrain([
                User::class => function ($query) {
                    $company = $query->getModel()->company();
                    $companyModel = $company->getRelated();

                    $query->addSelect([
                        'recipient_company_name' => $companyModel->newQueryWithoutScopes()
                            ->select('name')
                            ->whereColumn($company->getQualifiedOwnerKeyName(), $company->getQualifiedForeignKeyName())
                            ->when(
                                method_exists($companyModel, 'getQualifiedDeletedAtColumn'),
                                fn ($subquery) => $subquery->whereNull($companyModel->getQualifiedDeletedAtColumn())
                            )
                            ->limit(1),
                    ]);
                },
            ]);
    }

    /**
     * Mark the notification as read.
     *
     * @return void
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
        }
    }

    /**
     * Mark the notification as unread.
     *
     * @return void
     */
    public function markAsUnread()
    {
        if (! is_null($this->read_at)) {
            $this->forceFill(['read_at' => null])->save();
        }
    }

    /**
     * Determine if a notification has been read.
     *
     * @return bool
     */
    public function read()
    {
        return $this->read_at !== null;
    }

    protected function isRead(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->read_at !== null,
        );
    }

    protected function isMine(): Attribute
    {
        $user = auth()->user() ?? null;

        return new Attribute(
            get: fn ($value) => $user ? ($this->notifiable_type === $user->getMorphClass() && $this->notifiable_id == $user->id) : false,
        );
    }

    protected function subject(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->data['subject'] ?? '',
        );
    }

    protected function message(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->data['message'] ?? '',
        );
    }

    protected function htmlMessage(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->data['htmlMessage'] ?? '',
        );
    }

    protected function redirector(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->data['redirector'] ?? '',
        );
    }

    protected function hasRedirector(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->data['hasRedirector'] ?? false,
        );
    }

    protected function redirectorText(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->data['redirectorText'] ?? $this->data['actionText'] ?? '',
        );
    }

    /**
     * Compact recipient payload for table cells; null unless `recipient` was eager-loaded.
     *
     * Keys intentionally avoid `name` / `title`, which the index formatter collapses to a scalar.
     */
    protected function recipientSummary(): Attribute
    {
        return Attribute::get(function (): ?array {
            if (! $this->relationLoaded('recipient') || ! ($recipient = $this->getRelation('recipient'))) {
                return null;
            }

            $fullName = trim(implode(' ', array_filter([$recipient->name ?? null, $recipient->surname ?? null])));

            return [
                'id' => $recipient->getKey(),
                'full_name' => $fullName !== '' ? $fullName : null,
                'email' => $recipient->email ?? null,
                'company_name' => ($recipient->getAttributes()['recipient_company_name'] ?? null) ?: null,
                'avatar' => $recipient instanceof User ? $recipient->avatar : null,
            ];
        })->shouldCache();
    }

    /**
     * Determine if a notification has not been read.
     *
     * @return bool
     */
    public function unread()
    {
        return $this->read_at === null;
    }

    /**
     * Scope a query to only include read notifications.
     *
     * @return Builder
     */
    public function scopeRead(Builder $query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope a query to only include unread notifications.
     *
     * @return Builder
     */
    public function scopeUnread(Builder $query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeMyNotification(Builder $query)
    {
        return $query->where('notifiable_type', auth()->user()->getMorphClass())
            ->where('notifiable_id', auth()->user()->id);
    }

    public function scopeMyUnreadNotification(Builder $query)
    {
        return $query->myNotification()->unread();
    }

    public function scopeCompanyNotification(Builder $query)
    {
        $company = auth()->user()->company;

        if ($company) {
            return $query->where('notifiable_type', auth()->user()->getMorphClass())
                ->whereIn('notifiable_id', $company->users->pluck('id'));
        }

        return $query->myNotification();
    }

    /**
     * Create a new database notification collection instance.
     *
     * @return DatabaseNotificationCollection
     */
    public function newCollection(array $models = [])
    {
        return new DatabaseNotificationCollection($models);
    }
}
