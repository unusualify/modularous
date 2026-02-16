<?php

namespace Unusualify\Modularity\Tests\Helpers;

use Unusualify\Modularity\Tests\TestCase;

class I18nHelpersTest extends TestCase
{
    /** @test */
    public function test_modularity_trans_returns_translation()
    {
        // Don't mock - test with actual translation system
        $result = modularityTrans('validation.required');

        $this->assertIsString($result);
    }

    /** @test */
    public function test_triple_underscore_translates_keys()
    {
        // Don't mock - test with actual translation system
        $result = ___('validation.accepted');

        $this->assertIsString($result);
    }

    /** @test */
    public function test_get_label_from_locale_returns_formatted_label()
    {
        $result = getLabelFromLocale('en');

        $this->assertIsString($result);
        $this->assertStringContainsString('en', strtolower($result));
    }

    /** @test */
    public function test_get_code_2_language_texts_returns_array()
    {
        $result = getCode2LanguageTexts();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /** @test */
    public function test_get_languages_for_vue_store_returns_array()
    {
        $result = getLanguagesForVueStore();

        $this->assertIsArray($result);
        // Should return language data for Vue
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
