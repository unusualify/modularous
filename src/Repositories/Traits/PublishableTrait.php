<?php

namespace Unusualify\Modularous\Repositories\Traits;

use Unusualify\Modularous\Entities\Traits\Publishable;
use Unusualify\Modularous\Support\PublishableMetadata;

trait PublishableTrait
{
    public function prependFormSchemaPublishableTrait($scope = []): array
    {
        if ( ! classHasTrait($this->getModel(), Publishable::class)) {
            return [];
        }

        return PublishableMetadata::defaultFormInputs();
    }
}
