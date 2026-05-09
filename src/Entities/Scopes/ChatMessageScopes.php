<?php

namespace Unusualify\Modularous\Entities\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Unusualify\Modularous\Entities\Traits\HasCreator;

trait ChatMessageScopes
{
    use HasCreator;

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeUnreadForYou(Builder $query, $guardName = null): Builder
    {
        return $query->where('is_read', false)->whereNot(fn ($query) => $query->hasAccessToCreation(null, $guardName));
    }

    public function scopeFromClient(Builder $query): Builder
    {
        // Avoid the HasCreator global `creator_record_exists` (withExists) scope here:
        // it adds a nested EXISTS on um_creator_records to every ChatMessage subquery and
        // makes parent counts (e.g. whereHas('latestChatMessage', fromClient)) extremely slow.
        // return $query->withoutGlobalScope('creator_record_exists')
        //     ->whereHas('creator', function (Builder $query) {
        //         $query->role(['client-manager', 'client-assistant']);
        //     });

        return $query->whereHas('creator', function (Builder $query) {
            $query->role(['client-manager', 'client-assistant']);
        });
    }

    public function scopeFromCreator(Builder $query, $creatorId, $creatorType): Builder
    {
        return $query->whereHas('creator', function (Builder $query) use ($creatorId, $creatorType) {
            $query->where('creator_id', $creatorId)->where('creator_type', $creatorType);
        });
    }
}
