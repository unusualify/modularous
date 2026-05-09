<?php

namespace Modules\SystemUser\Http\Requests;

use Unusualify\Modularous\Http\Requests\Request;

class CapabilityRequest extends Request
{
    public function rulesForAll()
    {
        return [
            'roles' => ['sometimes', 'required', 'array'],
            'routes' => ['sometimes', 'array'],
            'strict_route_binding' => ['nullable', 'boolean'],
            'requires_step_up' => ['nullable', 'boolean'],
        ];
    }

    public function rulesForCreate()
    {
        $tableName = $this->model->getTable();

        return [
            'name' => "required|string|min:3|unique:{$tableName},name",
            'routes' => ['sometimes', 'array'],
        ];
    }

    public function rulesForUpdate()
    {
        $tableName = $this->model->getTable();

        return [
            'name' => "sometimes|required|string|min:3|unique:{$tableName},name,{$this->id}",
            'roles' => ['sometimes', 'required', 'array'],
            'routes' => ['sometimes', 'array'],
        ];
    }
}
