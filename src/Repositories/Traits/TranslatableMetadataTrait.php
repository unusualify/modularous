<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Unusualify\Modularity\Entities\Traits\HasTranslatableMetadata;
use Unusualify\Modularity\Entities\Traits\HasTranslation;
use Unusualify\Modularity\Support\TranslatableMetadata;

/**
 * Appends translatable metadata form inputs when the model uses {@see HasTranslatableMetadata} and
 * {@see HasTranslation}.
 *
 * **Convention:** use {@see TranslationsTrait} on the same repository **before** this trait so translation
 * save / form field pipelines run in the expected order.
 */
trait TranslatableMetadataTrait
{
    /**
     * @param array<string, mixed> $scope
     * @return list<array<string, mixed>>
     */
    public function appendFormSchemaTranslatableMetadataTrait($scope = []): array
    {
        if (! $this->hasModelTrait(HasTranslatableMetadata::class)) {
            return [];
        }

        return TranslatableMetadata::defaultFormInputs();
    }
}
