<?php

namespace Unusualify\Modularity\Repositories\Logic;

use Unusualify\Modularity\Facades\Modularity;

trait CollationSelector
{
    use \Illuminate\Database\Concerns\CompilesJsonPaths;

    private $collationSelectorColumns = [
        'char',
        'varchar',
        'tinytext',
        'text',
        'mediumtext',
        'longtext',
        'enum',
        'set',
    ];

    /**
     * @var bool
     */
    protected $shouldUseSearchCollation = false;

    /**
     * Set the should use search collation value.
     *
     * @param bool $value
     * @return void
     */
    public function setShouldUseSearchCollation($value)
    {
        $this->shouldUseSearchCollation = $value;

        return $this;
    }
    /**
     * Determine if the query is a collation query.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return bool
     */
    protected function isCollationQuery(\Illuminate\Database\Query\Builder $query)
    {
        return $query->getConnection()->getDriverName() === 'mysql';
    }

    /**
     * Determine if the query should use collation for search.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return bool
     */
    public function shouldUseSearchCollation(\Illuminate\Database\Query\Builder $query)
    {
        return (Modularity::shouldUseCollationForSearch() || $this->shouldUseSearchCollation) && $this->isCollationQuery($query);
    }

    public function getCollationSelectorColumns()
    {
        return $this->collationSelectorColumns;
    }

    /**
     * Add search collation to the query.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $field
     * @param  mixed  $value
     * @return \Illuminate\Database\Query\Builder
     */
    public function addSearchCollationToQuery(\Illuminate\Database\Query\Builder $query, string $field, $value)
    {
        $config = $query->getConnection()->getConfig();
        $collation = $config['collation'] ?? 'utf8mb4_unicode_ci';
        $columnTypes = $this->getModel()->getColumnTypes();

        // Check if this is a JSON field (contains -> or ->>)
        if (str_contains($field, '->')) {
            // For JSON fields, cast to CHAR first to avoid binary collation issues
            $wrappedField = $query->getGrammar()->wrap($field);
            return $query->whereRaw('CAST(' . $wrappedField . ' AS CHAR) COLLATE ' . $collation . ' LIKE ?', ['%' . $value . '%']);
        }

        if(isset($columnTypes[$field]) && in_array($columnTypes[$field], $this->getCollationSelectorColumns())) {
            $wrappedField = $query->getGrammar()->wrap($field);

            return $query->orWhereRaw($wrappedField . ' LIKE ? COLLATE ' . $collation, ['%' . $value . '%']);
        }

        return $query->orWhere($field, $this->getLikeOperator(), '%' . $value . '%');

    }
}
