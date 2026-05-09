<?php

namespace Unusualify\Modularous\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request
     * @return array
     */
    public function toArray($request)
    {
        // dd(parent::toArray($request));
        return parent::toArray($request);
    }
}
