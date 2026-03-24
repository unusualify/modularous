<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Hydrates\Inputs\ProcessHydrate;
use Unusualify\Modularity\Tests\TestCase;

class ProcessHydrateTest extends TestCase
{
    public function test_process_hydrate_throws_without_module_context()
    {
        $input = [
            'type' => 'process',
            'name' => 'process',
            'eager' => [],
        ];

        $h = new ProcessHydrate($input, null, null, true);

        // ProcessHydrate requires module/route context and throws exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid input');

        $h->render();
    }

    public function test_process_hydrate_throws_with_incomplete_context()
    {
        $input = [
            'type' => 'process',
            'name' => 'process',
            '_moduleName' => 'TestModule',
            'eager' => [],
        ];

        $moduleMock = \Mockery::mock();

        Modularity::shouldReceive('find')
            ->with('TestModule')
            ->andReturn($moduleMock);

        $h = new ProcessHydrate($input, null, null, true);

        // Missing _routeName
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid input');

        $h->render();
    }
}
