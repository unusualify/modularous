<?php

namespace Unusualify\Modularous\Tests\Traits;

use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\Traits\Moduleable;

class ModuleableTest extends TestCase
{
    public function test_get_module_name_returns_explicit_module_name()
    {
        $tester = new class
        {
            use Moduleable;
        };
        $tester->setModuleName('ExplicitModule');

        $this->assertEquals('ExplicitModule', $tester->getModuleName());
    }

    public function test_get_module_name_from_class_basename_when_no_module_in_namespace()
    {
        $tester = new class
        {
            use Moduleable;
        };

        $result = $tester->getModuleName();
        $this->assertNotNull($result);
        $this->assertIsString($result);
    }

    public function test_set_module_name_returns_self_and_sets_value()
    {
        $tester = new class
        {
            use Moduleable;
        };

        $result = $tester->setModuleName('CustomModule');
        $this->assertSame($tester, $result);
        $this->assertEquals('CustomModule', $tester->getModuleName());
    }

    public function test_get_route_name_returns_explicit_route_name()
    {
        $tester = new class
        {
            use Moduleable;
        };
        $tester->setRouteName('ExplicitRoute');

        $this->assertEquals('ExplicitRoute', $tester->getRouteName());
    }

    public function test_set_route_name_returns_self_and_sets_value()
    {
        $tester = new class
        {
            use Moduleable;
        };

        $result = $tester->setRouteName('CustomRoute');
        $this->assertSame($tester, $result);
        $this->assertEquals('CustomRoute', $tester->getRouteName());
    }
}
