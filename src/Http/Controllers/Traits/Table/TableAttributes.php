<?php

namespace Unusualify\Modularous\Http\Controllers\Traits\Table;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Unusualify\Modularous\Traits\Allowable;

trait TableAttributes
{
    use Allowable, TableEager;

    /**
     * @var array
     */
    protected $defaultTableAttributes = [
        // 'embeddedForm' => false,
        // 'createOnModal' => true,
        // 'editOnModal' => true,
        // 'formWidth' => '60%',
        // 'isRowEditing' => false,
        // 'rowActionsType' => 'inline',
        // 'hideDefaultFooter' => false,
        // 'striped' => true,
        // 'hideBorderRow' => true,
        // 'roundedRows' => true,
    ];

    /**
     * @var array
     */
    protected $tableAttributes = [];

    /**
     * getTableOptions
     *
     * @return void
     */
    public function getTableAttributes()
    {
        if ((bool) $this->config) {
            try {
                return Collection::make(
                    array_merge_recursive_preserve(
                        $this->defaultTableAttributes,
                        object_to_array($this->getConfigFieldsByRoute('table_attributes') ?? $this->getConfigFieldsByRoute('table_options') ?? (object) []))
                )->toArray();
            } catch (\Throwable $th) {
                return $this->defaultTableAttributes;
            }
        }

        return $this->defaultTableAttributes;
    }

    /**
     * method that checks whether the attribute configured on table_options
     * and returns its value or false if not.
     *
     *
     * @param mixed $attribute
     * @return bool|mixed returns referenced value or false if it's not defined at module config->table_options
     */
    public function getTableAttribute($attribute, $default = null)
    {
        return $this->tableAttributes[$attribute] ?? $default;
    }

    /**
     * Hydrate the table attributes
     *
     * @return void
     */
    protected function hydrateTableAttributes()
    {
        $attributes = $this->tableAttributes;

        if (isset($attributes['customRow'])) {
            $customRow = [];

            // Handle associative array case
            if (Arr::isAssoc($attributes['customRow'])) {
                if (isset($attributes['customRow']['name'])) {
                    $customRow = $this->hydrateCustomRow($attributes['customRow']);
                }
            }
            // Handle sequential array case with role-based filtering
            else {
                foreach ($attributes['customRow'] as $component) {
                    // Skip if component doesn't pass role check
                    if (! $this->isAllowedItem($component, searchKey: 'allowedRoles')) {
                        continue;
                    }

                    // Convert first matching component to standard format and break
                    if (isset($component['name'])) {
                        $customRow = $this->hydrateCustomRow($component);

                        break;
                    }
                }
            }

            $attributes = $this->mergeCustomRowTableAttributes($attributes, $customRow);
        }

        return $attributes;
    }

    /**
     * Apply the selected custom row's `tableAttributes` over the table attributes,
     * so a role-specific custom row can also restyle the table it renders in.
     */
    protected function mergeCustomRowTableAttributes(array $attributes, array $customRow): array
    {
        $tableAttributes = $customRow['tableAttributes'] ?? [];
        unset($customRow['tableAttributes']);

        if (is_array($tableAttributes) && $tableAttributes !== []) {
            $attributes = array_merge_recursive_preserve(
                $attributes,
                Arr::except($tableAttributes, ['customRow'])
            );
        }

        $attributes['customRow'] = $customRow;

        return $attributes;
    }

    /**
     * Set the table attributes
     *
     * @param array $tableOptions
     * @return void
     */
    protected function setTableAttributes($tableOptions = null)
    {
        if ($tableOptions) {
            $this->tableAttributes = array_merge_recursive_preserve(
                $this->defaultTableAttributes,
                $tableOptions,
            );
        }

        return $this;
    }

    /**
     * Hydrate the custom row
     *
     * @param array $customRow
     * @return array
     */
    protected function hydrateCustomRow($customRow)
    {
        return array_merge_recursive_preserve(
            ['col' => ['cols' => 12]],
            array_diff_key($customRow, ['allowedRoles' => ''])
        );
    }

    protected function addIndexWithsTableAttributes(): array
    {
        $indexWith = [];
        $model = $this->repository->getModel();

        if (method_exists($model, 'hasRelation') || method_exists($model, 'definedRelations')) {
            $indexWith = $this->mergeIndexWiths(
                $indexWith,
                $this->resolveHeaderWiths($this->getConfigFieldsByRoute('index_with', []), $model)
            );
        }

        return $indexWith;
    }
}
