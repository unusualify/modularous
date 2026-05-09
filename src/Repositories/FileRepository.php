<?php

namespace Unusualify\Modularous\Repositories;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Unusualify\Modularous\Entities\File;
use Unusualify\Modularous\Repositories\Traits\CreatorTrait;
use Unusualify\Modularous\Repositories\Traits\TagsTrait;

class FileRepository extends Repository
{
    use CreatorTrait, TagsTrait;

    public function __construct(File $model)
    {
        $this->model = $model;
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function filter($query, array $scopes = [])
    {
        $this->searchIn($query, $scopes, 'search', ['filename']);

        return parent::filter($query, $scopes);
    }

    /**
     * @param A17\Twill\Models\File $object
     * @return void
     */
    public function afterDelete($object)
    {
        $storageId = $object->uuid;
        if (Config::get('twill.file_library.cascade_delete')) {
            Storage::disk(Config::get('twill.file_library.disk'))->delete($storageId);
        }
    }

    /**
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeCreate($fields)
    {
        if (! isset($fields['size'])) {
            $uuid = str_replace(Config::get('filesystems.disks.twill_file_library.root'), '', $fields['uuid']);
            $fields['size'] = Storage::disk(Config::get('twill.file_library.disk'))->size($uuid);
        }

        return $fields;
    }
}
