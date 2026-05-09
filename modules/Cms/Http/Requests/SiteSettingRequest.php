<?php

namespace Modules\Cms\Http\Requests;

use Unusualify\Modularous\Http\Requests\Request;

class SiteSettingRequest extends Request
{
    public function rulesForAll()
    {
        return [
            'group_key' => 'required|string|max:100',
            'key' => 'required|string|max:100',
            'locale' => 'required|string|max:12',
            'value' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function rulesForCreate()
    {
        return [];
    }

    public function rulesForUpdate()
    {
        return [];
    }
}
