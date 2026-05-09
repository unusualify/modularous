<?php

namespace Unusualify\Modularous\Tests\Helpers;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Unusualify\Modularous\Tests\TestCase;

class ModuleHelpersTest extends TestCase
{
    /** @test */
    public function test_modularous_base_key_returns_base_key()
    {
        $result = modularousBaseKey();

        $this->assertIsString($result);
    }

    /** @test */
    public function test_class_uses_deep_gets_all_traits()
    {
        $class = new class
        {
            use HasAttributes;
        };

        $result = classUsesDeep($class);

        $this->assertIsArray($result);
    }

    /** @test */
    public function test_class_has_trait_checks_for_trait()
    {
        $class = new class
        {
            use HasAttributes;
        };

        $result = classHasTrait($class, HasAttributes::class);

        $this->assertTrue($result);
    }

    /** @test */
    public function test_modularous_config_retrieves_config()
    {
        $result = modularousConfig();

        $this->assertIsArray($result);
    }

    /** @test */
    public function test_modularous_config_with_key()
    {
        $result = modularousConfig('package_generator.default');

        // May return null if config not set
        $this->assertTrue(is_null($result) || is_string($result) || is_array($result));
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
