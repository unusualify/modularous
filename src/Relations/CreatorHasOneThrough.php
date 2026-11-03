<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * HasOneThrough over the creator records table, whose `creatable_id` is a
 * char(36) morph column. Comparing it against an integer parent key makes
 * MySQL cast every row and skip the (creatable_type, creatable_id) index, so
 * existence queries (whereHas('creator')) cast the parent key instead.
 */
class CreatorHasOneThrough extends HasOneThrough
{
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        if (! $this->shouldCastParentKey($query, $parentQuery)) {
            return parent::getRelationExistenceQuery($query, $parentQuery, $columns);
        }

        $this->performJoin($query);

        $grammar = $query->getQuery()->getGrammar();

        return $query->select($columns)->whereRaw(
            $grammar->wrap($this->getQualifiedFirstKeyName()) . ' = CAST(' . $grammar->wrap($this->getQualifiedLocalKeyName()) . ' AS CHAR)'
        );
    }

    protected function shouldCastParentKey(Builder $query, Builder $parentQuery): bool
    {
        $from = $parentQuery->getQuery()->from;

        return $from !== $query->getQuery()->from
            && $from !== $this->throughParent->getTable()
            && in_array($this->farParent->getKeyType(), ['int', 'integer'], true)
            && in_array($query->getConnection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
}
