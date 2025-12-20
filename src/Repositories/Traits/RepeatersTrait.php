<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * This trait is used for repeaters that may or may not have files or images in them.
 * If there is a file or image in the repeater, it should be removed from the repeater object, to be able to get detected by the FilesTrait and/or ImagesTrait.
 * as of @version 1.0.0, this trait onl manages Medias.
 *
 * @uses name::function Name
 *
 * @author Hazarcan Doğa
 *
 * @version ${1:1.0.0}
 *
 * @since 08 Jan 2024
 */
trait RepeatersTrait
{
    use FilesTrait, ImagesTrait, PricesTrait;

    public function setColumnsRepeatersTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $columns[$traitName] = collect($this->getRawInputs())->reduce(function ($acc, $curr) {
            if (isset($curr['name'])
                && (preg_match('/json-repeater/', $curr['type'] ?? 'default')
                    || preg_match('/json-repeater/', $curr['root'] ?? 'default')
                )
            ) {
                $acc[] = $curr['name'];
            }

            return $acc;
        }, []);

        return $columns;
    }

    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * @param array $fields
     * @return \Unusualify\Modularity\Entities\Model
     */
    public function afterSaveRepeatersTrait($object, $fields)
    {
        if ($this->shouldIgnoreFieldBeforeSave('repeaters')) {
            return $object;
        }

        $systemLocales = getLocales();
        $fallbackLocale = app()->getFallbackLocale();

        $schema = $this->getRawInputs();

        foreach ($this->getColumns(__TRAIT__) as $name) {
            $input = $schema[$name];
            $isTranslated = $input['translated'] ?? false;

            if (isset($fields[$name])) {
                $unsetColumns = [];
                Collection::make($this->traitColumns)->each(function ($columns, $traitName) use (&$unsetColumns) {
                    $unsetColumns = Collection::make($columns)->reduce(function ($unsetColumns, $column) {
                        if (preg_match('/([A-Za-z-_\.]+)\.\*\.([A-Za-z-_\.]+)/', $column, $matches)) {
                            if (! in_array($matches[2], $unsetColumns)) {
                                $unsetColumns[] = $matches[2];
                            }
                        }

                        return $unsetColumns;
                    }, $unsetColumns);
                });

                $intersectLocales = array_intersect(array_keys($fields[$name]), $systemLocales);
                $localized = false;
                $existLocale = null;

                $existingRepeaters = isset($object->id) ? $object->repeaters()
                    ->where('repeatable_id', $object->id)
                    ->where('role', $name)
                    ->get() : null;

                if (count($intersectLocales) > 1) {
                    $localized = true;
                    $existLocale = $intersectLocales[0];
                }

                if ($isTranslated) {

                    foreach ($systemLocales as $systemLocale) {
                        $content = $fields[$name];

                        if ($localized) {
                            $content = isset($fields[$name][$systemLocale]) ? $fields[$name][$systemLocale] : $fields[$name][$existLocale];
                        }

                        foreach ($unsetColumns as $unsetColumn) {
                            foreach ($content as &$item) {
                                unset($item[$unsetColumn]);
                            }
                        }

                        $data = [
                            'role' => $name,
                            'content' => $content,
                            'locale' => $systemLocale,
                        ];

                        $existingRepeater = $existingRepeaters ? $existingRepeaters->where('locale', $systemLocale)->first() : null;

                        $existingRepeater
                            ? $existingRepeater->update($data)
                            : $object->repeaters()->create($data);

                    }

                } else {
                    $payload = $fields[$name];

                    if ($localized) {
                        $payload = isset($fields[$name][$fallbackLocale]) ? $fields[$name][$fallbackLocale] : $fields[$name][$existLocale];
                    }

                    foreach ($unsetColumns as $unsetColumn) {
                        foreach ($payload as &$item) {
                            unset($item[$unsetColumn]);
                        }
                    }

                    $data = [
                        'role' => $name,
                        'content' => $payload,
                        'locale' => $fallbackLocale,
                    ];

                    $existingRepeater = $existingRepeaters ? $existingRepeaters->where('locale', $fallbackLocale)->first() : null;

                    $existingRepeater
                        ? $existingRepeater->update($data)
                        : $object->repeaters()->create($data);
                }
            }
        }
    }

    /**
     * From objects input
     */
    public function getRepeaterInputs($schema = null)
    {
        $schema = $schema ?? $this->getRawInputs();

        return collect($schema)->reduce(function ($acc, $curr) {
            if (isset($curr['name']) && (preg_match('/json-repeater/', $curr['root'] ?? 'default') || preg_match('/json-repeater/', $curr['type']))) {
                $acc[] = $curr + ['translated' => $curr['translated'] ?? false];
            }

            return $acc;
        }, []);
    }

    public function getFormFieldsRepeatersTrait($object, $fields, $schema = null)
    {
        // not possess any repeater data
        if (classHasTrait($object, 'Unusualify\Modularity\Entities\Traits\HasRepeaters') && method_exists($object, 'repeaters')) {
            $schema = $schema ?? $this->getRawInputs();
            if (!$object->repeaters()->exists()) {
                $fields += Arr::mapWithKeys($this->getRepeaterInputs($schema), function ($input) {
                    return [
                        $input['name'] => ($input['translated'] ?? false) ? Arr::mapWithKeys(getLocales(), function ($locale) {
                            return [$locale => []];
                        }) : ($input['default'] ?? []),
                    ];
                });
            } else {

                foreach ($object->repeaters->groupBy('locale') as $repeatersByLocale) {

                    foreach ($repeatersByLocale as $repeater) {
                        // dd($repeater->content);
                        if ($schema[$repeater->role]['translated'] ?? false) {
                            $name = $repeater->role . '.' . $repeater->locale;

                            foreach (Arr::dot($repeater->content) as $notation => $value) {
                                Arr::set($fields, "{$name}.{$notation}", $value);
                            }

                        } else {
                            $name = $repeater->role;
                            foreach (Arr::dot($repeater->content) as $notation => $value) {
                                Arr::set($fields, "{$name}.{$notation}", $value);
                            }
                        }
                    }
                    // $fields[$repeater->role] = $repeater->content;
                }
            }
        }

        return $fields;
    }
}
