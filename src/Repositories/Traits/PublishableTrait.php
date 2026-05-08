<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Unusualify\Modularity\Entities\Traits\Publishable;
use Unusualify\Modularity\Support\PublishableMetadata;

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
