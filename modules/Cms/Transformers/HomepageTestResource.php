<?php

namespace Modules\Cms\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class HomepageTestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
