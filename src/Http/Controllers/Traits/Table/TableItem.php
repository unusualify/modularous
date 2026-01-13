<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Unusualify\Modularity\Facades\ModularityLog;

trait TableItem
{
    use TableAttributes;

    /**
     * Name of the index column to use as identifier column.
     *
     * @var string
     */
    protected $identifierColumnKey = 'id';

    /**
     * @param \Unusualify\Modularity\Models\Model $item
     * @return int|string
     */
    protected function getItemIdentifier($item)
    {
        return $item->{$this->identifierColumnKey};
    }

    public function searchTitleKeyValue($columnsData)
    {
        $value = null;

        if (isset($columnsData[($newKey = $this->titleColumnKey . '_relation')])) {
            $this->titleColumnKey = $newKey;
            $value = $columnsData[$newKey];
        } elseif (isset($columnsData[($newKey = $this->titleColumnKey . '_timestamp')])) {
            $this->titleColumnKey = $newKey;
            $value = $columnsData[$newKey];
        } elseif (isset($columnsData[($newKey = $this->titleColumnKey . '_uuid')])) {
            $this->titleColumnKey = $newKey;
            $value = $columnsData[$newKey];
        } else {
            $newKey = array_keys($columnsData)[0];
            $this->titleColumnKey = $newKey;
            $value = $columnsData[$newKey];
        }

        return $value;
    }

    protected function getItemColumnData($item, $column)
    {

        if (isset($column['thumb']) && $column['thumb']) {
            if (isset($column['present']) && $column['present']) {
                return [
                    'thumbnail' => $item->presentAdmin()->{$column['presenter']},
                ];
            } else {
                $variant = isset($column['variant']);
                $role = $variant ? $column['variant']['role'] : head(array_keys($item->mediasParams));
                $crop = $variant ? $column['variant']['crop'] : head(array_keys(head($item->mediasParams)));
                $params = $variant && isset($column['variant']['params'])
                ? $column['variant']['params']
                : ['w' => 80, 'h' => 80, 'fit' => 'crop'];

                return [
                    'thumbnail' => $item->cmsImage($role, $crop, $params),
                ];
            }
        }

        if (isset($column['nested']) && $column['nested']) {
            $field = $column['nested'];
            $nestedCount = $item->{$column['nested']}->count();
            $module = Str::singular(last(explode('.', $this->moduleName)));
            $value = '<a href="';
            $value .= moduleRoute("$this->moduleName.$field", $this->routePrefix, 'index', [$module => $this->getItemIdentifier($item)]);
            $value .= '">' . $nestedCount . ' ' . (mb_strtolower(Str::plural($column['title'], $nestedCount))) . '</a>';
        } else {
            $field = $column['key'];
            $value = data_get($item, $field, null);
        }

        // for relationship fields
        if (preg_match('/(.*)(_relation)/', $column['key'], $matches)) {
            // $field = $column['key'];
            $relationshipName = $matches[1];
            $exploded = explode('.', $relationshipName);

            $relation = null;

            if (count($exploded) > 1) {
                $relationshipName = $exploded[1];
                $item = $item->{$exploded[0]};
            } else {
                $relation = $item->{$relationshipName}();
            }

            $itemTitle = $column['itemTitle'] ?? 'name';
            $isSole = $column['isSole'] ?? false;
            $maxItems = $column['maxItems'] ?? 3;

            $count = 0;

            $relationshipType = get_class($item->{$relationshipName}());

            if (in_array($relationshipType, [
                'Illuminate\Database\Eloquent\Relations\BelongsTo',
                'Illuminate\Database\Eloquent\Relations\HasOne',
                'Illuminate\Database\Eloquent\Relations\HasOneThrough',
                'Illuminate\Database\Eloquent\Relations\MorphOne',
                'Illuminate\Database\Eloquent\Relations\MorphTo',
            ])) {

                // Allow overriding the relation via "relation.field" or "relation->field"
                if (preg_match('/^([\w_]+)(?:\.|->)(.+)$/', $itemTitle, $m) && method_exists($item, $m[1])) {
                    $relationshipName = $m[1];
                    $itemTitle = $m[2];
                }

                $relation = $item->{$relationshipName}();
                $related = $relation->getRelated();
                $table = $related->getTable();
                $driver = $related->getConnection()->getDriverName();

                // Handle nested JSON like "field.headline" or "field->headline"
                if (preg_match('/^([\w_]+)(?:\.|->)(.+)$/', $itemTitle, $jm)) {
                    $jsonCol = $jm[1];
                    $jsonPathDots = str_replace('->', '.', $jm[2]);
                    $jsonPathEsc = str_replace("'", "''", $jsonPathDots);

                    switch ($driver) {
                        case 'pgsql':
                            $segments = explode('.', $jsonPathDots);
                            $expr = $table . '.' . $jsonCol . " #>> '{" . implode(',', $segments) . "}'";

                            break;
                        case 'sqlsrv':
                            $expr = "JSON_VALUE($table.$jsonCol, '$.$jsonPathEsc')";

                            break;
                        case 'sqlite':
                            $expr = "json_extract($table.$jsonCol, '$.$jsonPathEsc')";

                            break;
                        default: // mysql / mariadb
                            $expr = "JSON_UNQUOTE(JSON_EXTRACT($table.$jsonCol, '$.$jsonPathEsc'))";

                            break;
                    }

                    // Use an alias so value('_val') works reliably
                    $result = $relation->selectRaw("$expr as _val")->value('_val');
                } else {
                    // Simple column on the related model
                    $result = $isSole ?
                        $item->{$relationshipName}()->value($itemTitle) :
                        $item->{$relationshipName};
                }
            } elseif (in_array($relationshipType, [
                'Illuminate\Database\Eloquent\Relations\BelongsToMany',
                'Illuminate\Database\Eloquent\Relations\HasMany',
                'Illuminate\Database\Eloquent\Relations\HasManyThrough',
                'Illuminate\Database\Eloquent\Relations\MorphMany',
                'Illuminate\Database\Eloquent\Relations\MorphToMany',
            ])) {
                $count = $item->{$relationshipName}()->count();
                $result = $item->{$relationshipName}()
                    ->take($maxItems)
                    // ->pluck($itemTitle)
                    ->get();
            } else {
                $result = $item->{$relationshipName}()->value($itemTitle);
            }

            if ($result instanceof Collection) {
                $value = $result
                    ->pluck($itemTitle)
                    ->join(', ');

                if ($count > $maxItems) {
                    $value .= ' ...';
                }
            } elseif ($result instanceof Model) {
                // itemTitle is for example content->headline how to get nested json fields?
                $value = $result->{$itemTitle};
                // dd($value);
            } else {
                $value = $result;
            }
            try {
            } catch (\Throwable $th) {
                ModularityLog::error('Error getting item column data', [
                    'relationshipName' => $relationshipName,
                    'result' => $result,
                    'item' => $item,
                    'th' => $th,
                ]);
            }
        }

        if (preg_match('/(.*)(_timestamp)/', $column['key'], $matches)) {
            $value = $item->{$matches[1]};
        }

        if (preg_match('/(.*)(_uuid)/', $column['key'], $matches)) {
            // $value = $item->{$matches[1]};
            // $value = mb_substr($item->{$matches[1]}, 0, 6);
            $value = $item->{$matches[1]};
            // $value = "<span>" . substr($item->{$matches[1]}, 0, 6) . "</span>";
        }

        if (isset($column['relationship'])) {
            $field = $column['relationship'] . ucfirst($column['field']);

            $relation = $item->{$column['relationship']}();

            $value = collect($relation->get())
                ->pluck($column['field'])
                ->join(', ');

        } elseif (isset($column['present']) && $column['present']) {
            $value = $item->presentAdmin()->{$column['field']};
        }

        if (isset($column['relatedBrowser']) && $column['relatedBrowser']) {
            $field = 'relatedBrowser' . ucfirst($column['relatedBrowser']) . ucfirst($column['field']);
            $value = $item->getRelated($column['relatedBrowser'])
                ->pluck($column['field'])
                ->join(', ');
        }

        if (is_array($value)
            && (isset($value['title']) || isset($value['name']))
        ) {
            $value = $value['title'] ?? $value['name'];
        }

        return [
            "$field" => $value,
        ];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @return array
     */
    protected function _getIndexTableColumns($items)
    {
        $tableColumns = [];
        $visibleColumns = $this->request->get('columns') ?? false;
        $indexColumnCopy = $this->indexColumns;

        if (isset(Arr::first($indexColumnCopy)['thumb'])
            && Arr::first($indexColumnCopy)['thumb']
        ) {
            $tableColumns[] = [
                'name' => 'thumbnail',
                'label' => modularityTrans("$this->baseKey::lang.listing.columns.thumbnail"),
                'visible' => $visibleColumns ? in_array('thumbnail', $visibleColumns) : true,
                'optional' => true,
                'sortable' => false,
            ];
            array_shift($indexColumnCopy);
        }

        if ($this->getIndexOption('feature')) {
            $tableColumns[] = [
                'name' => 'featured',
                'label' => modularityTrans("$this->baseKey::lang.listing.columns.featured"),
                'visible' => true,
                'optional' => false,
                'sortable' => false,
            ];
        }
        if ($this->getIndexOption('publish')) {
            $tableColumns[] = [
                'name' => 'published',
                'label' => modularityTrans("$this->baseKey::lang.listing.columns.published"),
                'visible' => true,
                'optional' => false,
                'sortable' => false,
            ];
        }

        $tableColumns[] = [
            'name' => 'name',
            'label' => $indexColumnCopy[$this->titleColumnKey]['title'] ?? modularityTrans("$this->baseKey::lang.listing.columns.name"),
            'visible' => true,
            'optional' => false,
            'sortable' => $this->getIndexOption('reorder') ? false : ($indexColumnCopy[$this->titleColumnKey]['sort'] ?? false),
        ];

        unset($indexColumnCopy[$this->titleColumnKey]);

        foreach ($indexColumnCopy as $column) {
            if (isset($column['relationship'])) {
                $columnName = $column['relationship'] . ucfirst($column['field']);
            } elseif (isset($column['nested'])) {
                $columnName = $column['nested'];
            } elseif (isset($column['relatedBrowser'])) {
                $columnName = 'relatedBrowser' . ucfirst($column['relatedBrowser']) . ucfirst($column['field']);
            } else {
                $columnName = $column['value'];
                // $columnName = $column['field'];
            }

            $tableColumns[] = [
                'name' => $columnName,
                'label' => $column['text'],
                // 'label' => $column['title'],
                'visible' => $visibleColumns ? in_array($columnName, $visibleColumns) : ($column['visible'] ?? true),
                'optional' => $column['optional'] ?? true,
                'sortable' => $this->getIndexOption('reorder') ? false : ($column['sort'] ?? false),
                'html' => $column['html'] ?? false,
            ];
        }

        if ($this->getIndexOption('includeScheduledInList') && $this->repository->isFillable('publish_start_date')) {
            $tableColumns[] = [
                'name' => 'publish_start_date',
                'label' => modularityTrans("$this->baseKey::lang.listing.columns.published"),
                'visible' => true,
                'optional' => true,
                'sortable' => true,
            ];
        }

        if ($this->routeHasTrait('translations') && count(getLocales()) > 1) {
            $tableColumns[] = [
                'name' => 'languages',
                'label' => modularityTrans("$this->baseKey::lang.listing.languages"),
                'visible' => $visibleColumns ? in_array('languages', $visibleColumns) : true,
                'optional' => true,
                'sortable' => false,
            ];
        }

        return $tableColumns;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $item
     * @return array
     */
    protected function getCustomRowData($item)
    {
        $customRows = $this->getTableAttribute('customRow') ?? [];
        $customRowFillable = [];

        foreach ($customRows as $customRow) {
            if (isset($customRow['allowedRoles']) && isset($this->user) && $this->user->hasRole($customRow['allowedRoles'])) {
                if ($customRow['itemAttributes'] && is_array($customRow['itemAttributes'])) {
                    $customRowFillable = $customRow['itemAttributes'];
                } else {
                    $customRowFillable = [];
                }

                break;
            }
        }

        $customRowData = [];

        foreach ($customRowFillable as $fillable) {
            $customRowData[$fillable] = $item->{$fillable};
        }

        return $customRowData;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $item
     * @param bool $translated
     * @param array $schema
     * @return array
     */
    protected function formatIndexItem($item, $translated, $schema)
    {
        $columnsData = Collection::make($this->getIndexTableColumns())->mapWithKeys(function (&$column) use ($item) {
            return $this->getItemColumnData($item, $column);
        })->toArray();

        // $name = $columnsData[$this->titleColumnKey] ?? $this->searchTitleKeyValue($columnsData);
        $name = data_get($item, $this->titleColumnKey, '');

        if (empty($name)) {
            if ($translated) {
                $fallBackTranslation = $item->translations()->where('active', true)->first();

                if (isset($fallBackTranslation->{$this->titleColumnKey})) {
                    $name = $fallBackTranslation->{$this->titleColumnKey};
                }
            }

            $name = $name ?? ('Missing ' . $this->titleColumnKey);
        }

        unset($columnsData[$this->titleColumnKey]);

        $itemIsTrashed = method_exists($item, 'trashed') && $item->trashed();
        $itemCanDelete = $this->getIndexOption('delete') && ($item->canDelete ?? true);
        $canEdit = $this->getIndexOption('edit');
        $canDuplicate = $this->getIndexOption('duplicate');

        $itemId = $this->getItemIdentifier($item);

        $necessaryTableData = [
            'id' => $itemId,
            $this->titleColumnKey => $name,
            'deleted_at' => $item->deleted_at,
            // 'publish_start_date' => $item->publish_start_date,
            // 'publish_end_date' => $item->publish_end_date,
            // 'edit' => $canEdit ? $this->getModuleRoute($itemId, 'edit') : null,
            // 'duplicate' => $canDuplicate ? $this->getModuleRoute($itemId, 'duplicate') : null,
            // 'delete' => $itemCanDelete ? $this->getModuleRoute($itemId, 'destroy') : null,
        ];

        return object_to_array(array_replace(
            array_merge(
                (($this->tableAttributes['editOnModal'] ?? true) ? $this->repository->getShowFields($item, $schema) : []),
                // ($this->tableAttributes['editOnModal'] ?? true) ? $item->toArray() : ['id' => $itemId],
                $item->toArray(),
                $necessaryTableData,
                (($this->tableAttributes['editOnModal'] ?? true) ? $this->repository->getFormFields($item, $schema) : []),
                // $this->repository->getFormFields($item, $schema),
                $columnsData,
                $this->getCustomRowData($item),
                // + ($this->getIndexOption('editInModal') ? [
                //     'editInModal' => $this->getModuleRoute($itemId, 'edit'),
                //     'updateUrl' => $this->getModuleRoute($itemId, 'update'),
                // ] : [])
                // + ($this->getIndexOption('publish') && ($item->canPublish ?? true) ? [
                //     'published' => $item->published,
                // ] : [])
                // + ($this->getIndexOption('feature') && ($item->canFeature ?? true) ? [
                //     'featured' => $item->{$this->featureField},
                // ] : [])
                // + (($this->getIndexOption('restore') && $itemIsTrashed) ? [
                //     'deleted' => true,
                // ] : [])
                // + (($this->getIndexOption('forceDelete') && $itemIsTrashed) ? [
                //     'destroyable' => true,
                // ] : [])
                // + ($translated ? [
                //     'languages' => $item->getActiveLanguages(),
                // ] : [])

            ),
            $this->indexItemData($item)
        ));
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $item
     * @return array
     */
    public function getFormattedIndexItem($item)
    {
        $translated = $this->routeHas('translations');

        $callback = fn () => $this->formatIndexItem($item, $translated, $this->formSchema);

        if (! $this->shouldUseCache('formattedItem')) {
            return $callback();
        }

        $params = [
            'id' => $item->id,
            'locale' => app()->getLocale(),
        ];

        return $this->rememberCache(
            callback: $callback,
            type: 'formattedItem',
            data: $params
        );
    }

    /**
     * @param Illuminate\Pagination\LengthAwarePaginator $paginator
     * @return array
     */
    public function getFormattedIndexItems(\Illuminate\Pagination\AbstractPaginator $paginator) // getIndexTableItems
    {
        $paginator->getCollection()->transform(function ($item) {
            return $this->getFormattedIndexItem($item);
        });

        return $paginator->toArray();
    }
}
