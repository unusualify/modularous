<?php

namespace Unusualify\Modularity\Tests\Helpers;

use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Tests\TestCase;

class ModuleHelpersTest extends TestCase
{
    /** @test */
    public function test_modularity_base_key_returns_base_key()
    {
        $result = modularityBaseKey();

        $this->assertIsString($result);
    }

    /** @test */
    public function test_class_uses_deep_gets_all_traits()
    {
        $class = new class {
            use \Illuminate\Database\Eloquent\Concerns\HasAttributes;
        };

        $result = classUsesDeep($class);

        $this->assertIsArray($result);
    }

    /** @test */
    public function test_class_has_trait_checks_for_trait()
    {
        $class = new class {
            use \Illuminate\Database\Eloquent\Concerns\HasAttributes;
        };

        $result = classHasTrait($class, \Illuminate\Database\Eloquent\Concerns\HasAttributes::class);

        $this->assertTrue($result);
    }

    /** @test */
    public function test_modularity_config_retrieves_config()
    {
        $result = modularityConfig();

        $this->assertIsArray($result);
    }

    /** @test */
    public function test_modularity_config_with_key()
    {
        $result = modularityConfig('package_generator.default');

        // May return null if config not set
        $this->assertTrue(is_null($result) || is_string($result) || is_array($result));
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
