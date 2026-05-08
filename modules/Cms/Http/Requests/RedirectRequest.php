<?php

namespace Modules\Cms\Http\Requests;

use Unusualify\Modularity\Http\Requests\Request;

class RedirectRequest extends Request
{
    public function rulesForAll()
    {
        return [
            'from_path' => 'sometimes|required|string|max:255',
            'to_path' => 'sometimes|required|string|max:255|different:from_path',
            'locale' => 'sometimes|required|string|max:12',
            'status_code' => 'sometimes|required|integer|in:301,302,307,308',
            'is_active' => 'sometimes|nullable|boolean',
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
