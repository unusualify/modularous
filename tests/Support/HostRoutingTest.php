<?php

namespace Unusualify\Modularous\Tests\Support;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Support\HostRouting;
use Unusualify\Modularous\Tests\Support\Stubs\HostableStub;
use Unusualify\Modularous\Tests\TestCase;

class HostRoutingTest extends TestCase
{
    public function test_get_base_host_name_returns_configured_host(): void
    {
        $hostRouting = new HostRouting(app(), 'localhost');

        $this->assertSame('localhost', $hostRouting->getBaseHostName());
    }

    public function test_set_options_without_model_uses_base_host(): void
    {
        $hostRouting = new HostRouting(app(), 'example.com');
        $hostRouting->setOptions();

        $options = $hostRouting->getOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('domain', $options);
        $this->assertSame('example.com', $options['domain']);
        $this->assertArrayHasKey('middleware', $options);
        $this->assertContains('hostable', $options['middleware']);
    }

    public function test_set_options_with_middleware_merge(): void
    {
        $hostRouting = new HostRouting(app(), 'example.com');
        $hostRouting->setOptions(['middleware' => ['web']]);

        $options = $hostRouting->getOptions();

        $this->assertArrayHasKey('middleware', $options);
        $this->assertContains('hostable', $options['middleware']);
        $this->assertContains('web', $options['middleware']);
    }

    public function test_combine_host_models_returns_empty_when_classes_not_hostable(): void
    {
        Schema::shouldReceive('hasTable')->andReturn(false);
        $stub = new HostableStub;
        App::bind(HostableStub::class, fn () => $stub);

        $hostRouting = new HostRouting(app(), 'example.com', [HostableStub::class]);
        $models = $hostRouting->combineHostModels();

        $this->assertCount(0, $models);
    }

    public function test_combine_host_models_returns_empty_for_empty_hostable_classes(): void
    {
        $hostRouting = new HostRouting(app(), 'example.com', []);
        $models = $hostRouting->combineHostModels();

        $this->assertCount(0, $models);
    }

    public function test_classes_is_hostable_returns_true_when_tables_exist(): void
    {
        Schema::shouldReceive('hasTable')->andReturn(true);
        $stub = new HostableStub;
        App::bind(HostableStub::class, fn () => $stub);

        $hostRouting = new HostRouting(app(), 'example.com', [HostableStub::class]);

        $this->assertTrue($hostRouting->classesIsHostable());
    }

    public function test_classes_is_hostable_returns_false_when_table_missing(): void
    {
        Schema::shouldReceive('hasTable')->andReturn(false);
        $stub = new HostableStub;
        App::bind(HostableStub::class, fn () => $stub);

        $hostRouting = new HostRouting(app(), 'example.com', [HostableStub::class]);

        $this->assertFalse($hostRouting->classesIsHostable());
    }

    public function test_set_model_with_string_sets_single_class(): void
    {
        Schema::shouldReceive('hasTable')->andReturn(true);
        $stub = new HostableStub;
        App::bind(HostableStub::class, fn () => $stub);

        $hostRouting = new HostRouting(app(), 'example.com');
        $hostRouting->setModel(HostableStub::class);

        $this->assertSame([HostableStub::class], $hostRouting->hostableClasses);
    }

    public function test_set_model_with_array_sets_multiple_classes(): void
    {
        Schema::shouldReceive('hasTable')->andReturn(true);
        $stub = new HostableStub;
        App::bind(HostableStub::class, fn () => $stub);

        $hostRouting = new HostRouting(app(), 'example.com');
        $hostRouting->setModel([HostableStub::class, HostableStub::class]);

        $this->assertSame([HostableStub::class, HostableStub::class], $hostRouting->hostableClasses);
    }

    public function test_options_method_via_call_delegates_to_set_options(): void
    {
        $hostRouting = new HostRouting(app(), 'example.com');
        $result = $hostRouting->options(['middleware' => ['web']]);

        $this->assertSame($hostRouting, $result);
        $this->assertContains('web', $hostRouting->getOptions()['middleware']);
    }

    public function test_model_method_via_call_delegates_to_set_model(): void
    {
        Schema::shouldReceive('hasTable')->andReturn(true);
        $stub = new HostableStub;
        App::bind(HostableStub::class, fn () => $stub);

        $hostRouting = new HostRouting(app(), 'example.com');
        $result = $hostRouting->model(HostableStub::class);

        $this->assertSame($hostRouting, $result);
        $this->assertSame([HostableStub::class], $hostRouting->hostableClasses);
    }

    public function test_call_throws_for_undefined_method(): void
    {
        $hostRouting = new HostRouting(app(), 'example.com');

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method');

        $hostRouting->nonexistentMethod();
    }

    public function test_group_registers_routes_with_options(): void
    {
        Route::shouldReceive('group')->once()->with(
            \Mockery::on(fn ($opts) => isset($opts['domain']) && isset($opts['middleware'])),
            \Mockery::type('Closure')
        );

        $hostRouting = new HostRouting(app(), 'example.com');
        $hostRouting->setOptions();
        $hostRouting->group(function () {
            // callback
        });
    }
}
