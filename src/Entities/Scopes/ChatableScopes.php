<?php

namespace Unusualify\Modularity\Entities\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Unusualify\Modularity\Entities\ChatMessage;

trait ChatableScopes
{
    public function scopeHasChatMessages(Builder $query): Builder
    {
        return $query->whereHas('chatMessages');
    }

    public function scopeHasUnreadChatMessages(Builder $query): Builder
    {
        return $query->whereHas('chatMessages', function (Builder $query) {
            $query->unread();
        });
    }

    public function scopeHasUnreadChatMessagesForYou(Builder $query, $guardName = null): Builder
    {
        return $query->whereHas('chatMessages', function (Builder $query) use ($guardName) {
            $query->where('is_read', false)->whereNot(fn ($query) => $query->authorized($guardName));
        });
    }

    /**
     * Latest message is from client roles.
     *
     * We filter messages with created_at = MAX(created_at) per chat_id (correlated subquery).
     * Do not use whereHas(latestMessage): latestOfMany() + GROUP BY breaks under MySQL
     * ONLY_FULL_GROUP_BY when nested inside EXISTS for counts.
     */
    public function scopeHasUnansweredChatMessageFromClient(Builder $query): Builder
    {
        $chatMessageTable = (new ChatMessage)->getTable();

        // return $query->whereHas('latestChatMessage', function (Builder $query) {
        //     $query->fromClient();
        // });

        return $query->whereHas('chat', function (Builder $chatQuery) use ($chatMessageTable) {
            $chatQuery->whereHas('messages', function (Builder $mq) use ($chatMessageTable) {
                $mq->whereNull($chatMessageTable.'.deleted_at')
                    ->whereRaw($chatMessageTable.'.`created_at` = (
                        SELECT MAX(`m2`.`created_at`)
                        FROM `'.$chatMessageTable.'` AS `m2`
                        WHERE `m2`.`chat_id` = `'.$chatMessageTable.'`.`chat_id`
                        AND `m2`.`deleted_at` IS NULL
                    )')
                    ->fromClient();
            });
        });
    }

    /**
     * Latest message is tied to the same creator records as this model (see creator_records join).
     * Do not use whereHas(latestMessage): latestOfMany() + GROUP BY breaks under MySQL ONLY_FULL_GROUP_BY
     * when nested inside EXISTS (counts / filters).
     */
    public function scopeHasUnansweredChatMessageFromCreator(Builder $query): Builder
    {
        $creatorRecordTable = modularityConfig('tables.creator_records', 'um_creator_records');
        $chatMessageTable = (new ChatMessage)->getTable();

        // return $query->whereHas('latestChatMessage', function ($messageQuery) use ($creatorRecordTable, $chatMessageTable) {
        //     $messageQuery->whereExists(function ($subQuery) use ($creatorRecordTable, $chatMessageTable) {
        //         $creatableTableAlias = 'creatable_creators';
        //         $chatableTableAlias = 'chatable_creators';

        //         $subQuery->select(DB::raw(1))
        //             ->from($creatorRecordTable . ' as ' . $creatableTableAlias)
        //             ->join($creatorRecordTable . ' as ' . $chatableTableAlias, function ($join) use ($creatableTableAlias, $chatableTableAlias) {
        //                 $join->on($creatableTableAlias . '.creator_id', '=', $chatableTableAlias . '.creator_id')
        //                     ->on($creatableTableAlias . '.guard_name', '=', $chatableTableAlias . '.guard_name');
        //             })
        //             ->whereColumn($creatableTableAlias . '.creatable_id', $this->getTable() . '.id')
        //             ->where($creatableTableAlias . '.creatable_type', static::class)
        //             ->whereColumn($chatableTableAlias . '.creatable_id', $chatMessageTable . '.id')
        //             ->where($chatableTableAlias . '.creatable_type', ChatMessage::class);
        //     });
        // });

        return $query->whereHas('chat', function (Builder $chatQuery) use ($creatorRecordTable, $chatMessageTable) {
            $chatQuery->whereHas('messages', function (Builder $mq) use ($creatorRecordTable, $chatMessageTable) {
                $mq->whereNull($chatMessageTable.'.deleted_at')
                    ->whereRaw($chatMessageTable.'.`created_at` = (
                        SELECT MAX(`m2`.`created_at`)
                        FROM `'.$chatMessageTable.'` AS `m2`
                        WHERE `m2`.`chat_id` = `'.$chatMessageTable.'`.`chat_id`
                        AND `m2`.`deleted_at` IS NULL
                    )')
                    ->whereExists(function ($subQuery) use ($creatorRecordTable, $chatMessageTable) {
                        $creatableTableAlias = 'creatable_creators';
                        $chatableTableAlias = 'chatable_creators';

                        $subQuery->select(DB::raw(1))
                            ->from($creatorRecordTable.' as '.$creatableTableAlias)
                            ->join($creatorRecordTable.' as '.$chatableTableAlias, function ($join) use ($creatableTableAlias, $chatableTableAlias) {
                                $join->on($creatableTableAlias.'.creator_id', '=', $chatableTableAlias.'.creator_id')
                                    ->on($creatableTableAlias.'.guard_name', '=', $chatableTableAlias.'.guard_name');
                            })
                            ->whereColumn($creatableTableAlias.'.creatable_id', $this->getTable().'.id')
                            ->where($creatableTableAlias.'.creatable_type', static::class)
                            ->whereColumn($chatableTableAlias.'.creatable_id', $chatMessageTable.'.id')
                            ->where($chatableTableAlias.'.creatable_type', ChatMessage::class);
                    });
            });
        });
    }

    /**
     * Scope to get models that have a notifiable message.
     *
     * @param int|null $minuteOffset
     */
    public function scopeHasNotifiableMessage(Builder $query, $minuteOffset = null): Builder
    {
        $chatMessageTable = (new ChatMessage)->getTable();

        return $query->whereHas('chat', function (Builder $chatQuery) use ($minuteOffset, $chatMessageTable) {
            $chatQuery->whereHas('messages', function (Builder $mq) use ($minuteOffset, $chatMessageTable) {
                $mq->whereNull($chatMessageTable.'.deleted_at')
                    ->whereRaw($chatMessageTable.'.`created_at` = (
                        SELECT MAX(`m2`.`created_at`)
                        FROM `'.$chatMessageTable.'` AS `m2`
                        WHERE `m2`.`chat_id` = `'.$chatMessageTable.'`.`chat_id`
                        AND `m2`.`deleted_at` IS NULL
                    )')
                    ->where($chatMessageTable.'.is_read', false)
                    ->whereNull($chatMessageTable.'.notified_at')
                    ->when($minuteOffset, function (Builder $q) use ($minuteOffset, $chatMessageTable) {
                        $q->where($chatMessageTable.'.created_at', '<', now()->subMinutes($minuteOffset));
                    });
            });
        });

        // return $query->whereHas('latestChatMessage', function (Builder $query) use ($minuteOffset, $chatMessageTable) {
        //     $query->where('is_read', false)->whereNull('notified_at')->when($minuteOffset, function ($query) use ($minuteOffset, $chatMessageTable) {
        //         $query->where($chatMessageTable . '.created_at', '<', now()->subMinutes($minuteOffset));
        //     });
        // });
    }
}
