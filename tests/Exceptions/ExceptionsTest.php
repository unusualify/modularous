<?php

namespace Unusualify\Modularity\Tests\Exceptions;

use Unusualify\Modularity\Exceptions\AuthConfigurationException;
use Unusualify\Modularity\Exceptions\ModularitySystemPathException;
use Unusualify\Modularity\Exceptions\ModuleNotFoundException;
use Unusualify\Modularity\Tests\TestCase;

class ExceptionsTest extends TestCase
{
    /** @test */
    public function it_can_create_auth_configuration_exceptions()
    {
        $e = AuthConfigurationException::guardMissing();
        $this->assertEquals(AuthConfigurationException::GUARD_MISSING, $e->getCode());
        $this->assertStringContainsString('guard', $e->getMessage());

        $e = AuthConfigurationException::providerMissing();
        $this->assertEquals(AuthConfigurationException::PROVIDER_MISSING, $e->getCode());
        $this->assertStringContainsString('provider', $e->getMessage());

        $e = AuthConfigurationException::passwordMissing();
        $this->assertEquals(AuthConfigurationException::PASSWORD_MISSING, $e->getCode());
        $this->assertStringContainsString('password', $e->getMessage());
    }

    /** @test */
    public function it_can_create_modularity_system_path_exception()
    {
        $e = new ModularitySystemPathException();
        $this->assertStringContainsString('system modules path', $e->getMessage());
    }

    /** @test */
    public function it_can_create_module_not_found_exceptions()
    {
        $e = ModuleNotFoundException::moduleMissing();
        $this->assertEquals(ModuleNotFoundException::MODULE_MISSING, $e->getCode());
        $this->assertEquals('Missing module name', $e->getMessage());

        $e = ModuleNotFoundException::routeMissing();
        $this->assertEquals(ModuleNotFoundException::ROUTE_MISSING, $e->getCode());
        $this->assertEquals('Missing route name', $e->getMessage());

        $e = ModuleNotFoundException::moduleNotFound('Custom Not Found');
        $this->assertEquals(ModuleNotFoundException::MODULE_NOT_FOUND, $e->getCode());
        $this->assertEquals('Custom Not Found', $e->getMessage());

        $e = ModuleNotFoundException::routeNotFound();
        $this->assertEquals(ModuleNotFoundException::ROUTE_NOT_FOUND, $e->getCode());
        $this->assertEquals('Route not found', $e->getMessage());
    }
}
