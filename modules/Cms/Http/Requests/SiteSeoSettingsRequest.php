<?php

namespace Modules\Cms\Http\Requests;

use Unusualify\Modularity\Http\Requests\Request;

class SiteSeoSettingsRequest extends Request
{
    public function rulesForAll()
    {
        return [
            'global_robots_txt' => 'nullable|string|max:200000',
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
