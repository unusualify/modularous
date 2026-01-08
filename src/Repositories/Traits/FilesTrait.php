<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Unusualify\Modularity\Entities\File;

trait FilesTrait
{
    public function setColumnsFilesTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $columns[$traitName] = collect($inputs)->reduce(function ($acc, $curr) {
            if (preg_match('/\bfile\b/', $curr['type'])) {
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
    public function hydrateFilesTrait($object, $fields)
    {
        if ($this->shouldIgnoreFieldBeforeSave('files')) {
            return $object;
        }

        $filesCollection = Collection::make();
        $filesFromFields = $this->getFiles($object, $fields);

        $filesFromFields->each(function ($file) use ($object, $filesCollection) {
            $newFile = File::withTrashed()->find($file['file_id']);
            $pivot = $newFile->newPivot($object, Arr::except($file, ['id']), 'fileables', true);
            $newFile->setRelation('pivot', $pivot);
            $filesCollection->push($newFile);
        });

        $object->setRelation('files', $filesCollection);

        return $object;
    }

    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * @param array $fields
     * @return void
     */
    public function afterSaveFilesTrait($object, $fields)
    {
        if ($this->shouldIgnoreFieldBeforeSave('files')) {
            return;
        }

        $this->getFiles($object, $fields)->each(function ($file) use ($object) {
            if (isset($file['id']) && $file['id']) {
                $result = $object->files()->updateExistingPivot($file['id'], Arr::except($file, ['id', 'file_id']));
                if( $result ) {
                    $this->mustTouchEloquentModel();
                }
            } else {
                $object->files()->attach($file['file_id'], Arr::except($file, ['file_id']));
                $this->mustTouchEloquentModel();
            }
        });
    }

    /**
     * @param \Unusualify\Modularity\Entities\Model $object
     * @param array $fields
     * @return array
     */
    public function getFormFieldsFilesTrait($object, $fields, $schema)
    {
        if ($object->has('files')) {
            $schema = $schema ?? $this->inputs();
            // foreach ($object->files->groupBy('pivot.role') as $role => $filesByRole) {
            //     foreach ($filesByRole->groupBy('pivot.locale') as $locale => $filesByLocale) {
            //         // $fields['files'][$locale][$role] = $filesByLocale->map(function ($file) {
            //         //     return $file->mediableFormat();
            //         // });
            //         $fields[$role][$locale] = $filesByLocale->map(function ($file) {
            //             return $file->mediableFormat();
            //         });
            //     } d
            // }
            $systemLocales = getLocales();
            $default_locale = config('app.locale');
            $fallback_locale = config('app.fallback_locale');
            $filesByRole = $object->files->groupBy('pivot.role');

            foreach ($this->getColumns(__TRAIT__) as $role) {
                if (isset($filesByRole[$role])) {
                    $input = $schema[$role];
                    if ($input['translated'] ?? false) {
                        foreach ($filesByRole[$role]->groupBy('pivot.locale') as $locale => $filesByLocale) {
                            $fields[$role][$locale] = $filesByLocale->map(function ($file) {
                                return $file->mediableFormat();
                            });
                        }
                    } else {
                        $files = $filesByRole[$role]->groupBy('pivot.locale')[$default_locale] ?? $filesByRole[$role]->groupBy('pivot.locale')[$fallback_locale] ?? collect([]);
                        $fields[$role] = $files->map(function ($file) {
                            return $file->mediableFormat();
                        });
                    }
                } else {
                    $input = $schema[$role] ?? null;

                    if ($input) {
                        $fields += [
                            $input['name'] => ($input['translated']) ?? false ? Collection::make(Arr::mapWithKeys(getLocales(), function ($locale) {
                                return [$locale => []];
                            })) : Collection::make([]),
                        ];
                    }

                    // foreach ($systemLocales as $locale) {
                    //     $fields[$role][$locale] = [];
                    // }
                }
            }
        }

        return $fields;
    }

    /**
     * @param array $fields
     * @return \Illuminate\Support\Collection
     */
    private function getFiles($object, $fields)
    {
        $files = Collection::make();
        $systemLocales = getLocales();
        $fileRoles = $this->getColumns(__TRAIT__);
        $fileablesTable = modularityConfig('tables.fileables', 'um_fileables');

        foreach ($fileRoles as $role) {
            if (isset($fields[$role]) && count(array_keys($fields[$role])) > 0) {
                $default_locale = array_keys($fields[$role])[0];
                foreach ($systemLocales as $locale) {
                    if (isset($fields[$role][$locale])) {
                        Collection::make($fields[$role][$locale])->each(function ($file) use ($object, $fileablesTable, &$files, $role, $locale) {
                            $fileableId = $object->files()
                                ->select($fileablesTable . '.id as pivot_id')
                                ->where('file_id', $file['id'])
                                ->where('role', $role)
                                ->where('locale', $locale)->value('pivot_id') ?? null;

                            $files->push([
                                ...($fileableId ? ['id' => $fileableId] : []),
                                'file_id' => $file['id'],
                                'role' => $role,
                                'locale' => $locale,
                            ]);
                        });
                    } else {
                        Collection::make($fields[$role])->each(function ($file) use ($object, $fileablesTable, &$files, $role, $locale) {
                            $fileableId = $object->files()
                                ->select($fileablesTable . '.id as pivot_id')
                                ->where('file_id', $file['id'])
                                ->where('role', $role)
                                ->where('locale', $locale)->value('pivot_id') ?? null;

                            $files->push([
                                ...($fileableId ? ['id' => $fileableId] : []),
                                'file_id' => $file['id'],
                                'role' => $role,
                                'locale' => $locale,
                            ]);
                        });
                    }
                }
                // foreach($fields[$role] as $locale => $filesForRole){
                //     Collection::make($filesForRole)->each(function ($file) use (&$files, $role, $locale) {
                //         $files->push([
                //             'id' => $file['id'],
                //             'role' => $role,
                //             'locale' => $locale,
                //         ]);
                //     });
                // }
            } else {
                // dd($role);
            }
        }

        return $files;
    }
}
