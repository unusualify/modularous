<?php

namespace Modules\Cms\Http\Requests;

use Unusualify\Modularity\Http\Requests\Request;

/**
 * Panel JSON POST for {@see \Modules\Cms\Http\Controllers\CmsSitemapPanelController} (body genelde boş; ileri alanlar için genişletilebilir).
 */
class SitemapRequest extends Request
{
    public function rulesForAll()
    {
        return [
            // Reserved for future: 'force' => 'sometimes|boolean',
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
