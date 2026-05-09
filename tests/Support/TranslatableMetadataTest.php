<?php

namespace Unusualify\Modularous\Tests\Support;

use Unusualify\Modularous\Entities\Traits\HasTranslatableMetadata;
use Unusualify\Modularous\Support\TranslatableMetadata;
use Unusualify\Modularous\Tests\TestCase;

class TranslatableMetadataTest extends TestCase
{
    public function test_translated_attributes_constant_matches_trait(): void
    {
        $dummy = new class
        {
            use HasTranslatableMetadata;
        };

        $this->assertSame(
            TranslatableMetadata::TRANSLATED_ATTRIBUTES,
            get_class($dummy)::translatableMetadataAttributeNames()
        );
    }

    public function test_default_form_inputs_cover_all_translated_keys(): void
    {
        $names = array_column(TranslatableMetadata::defaultFormInputs(), 'name');

        foreach (TranslatableMetadata::TRANSLATED_ATTRIBUTES as $attr) {
            $this->assertContains($attr, $names, "Missing form input for [{$attr}]");
        }
    }
}
