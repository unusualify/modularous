<?php

namespace Unusualify\Modularous\Repositories\Traits;

use Unusualify\Modularous\Entities\Traits\HasTranslatableMetadata;
use Unusualify\Modularous\Entities\Traits\HasTranslation;
use Unusualify\Modularous\Support\TranslatableMetadata;

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
