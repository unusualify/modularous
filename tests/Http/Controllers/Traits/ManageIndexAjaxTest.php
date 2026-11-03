<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Unusualify\Modularous\Http\Controllers\Traits\ManageIndexAjax;
use Unusualify\Modularous\Tests\TestCase;

class ManageIndexAjaxTest extends TestCase
{
    private function makeController(array $query): object
    {
        $request = Request::create('/items', 'GET', $query);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        return new class($request)
        {
            use ManageIndexAjax;

            public int $mainFilterCalls = 0;

            public function __construct(public Request $request) {}

            public function callRespondToIndexAjax()
            {
                return $this->respondToIndexAjax();
            }

            protected function getJSONData($with = [])
            {
                return ['data' => [['id' => 1, 'name' => 'Turkey']]];
            }

            protected function getTableMainFilters($scopes = [])
            {
                $this->mainFilterCalls++;

                return [['slug' => 'all']];
            }

            protected function getExactScope()
            {
                return [];
            }

            protected function getReplaceUrl()
            {
                return true;
            }
        };
    }

    /** @test */
    public function it_returns_only_the_resource_for_light_requests(): void
    {
        $controller = $this->makeController(['light' => '1']);

        $payload = $controller->callRespondToIndexAjax()->getData(true);

        $this->assertSame(['resource'], array_keys($payload));
        $this->assertSame(0, $controller->mainFilterCalls);
    }

    /** @test */
    public function it_keeps_main_filters_and_replace_url_for_default_requests(): void
    {
        $controller = $this->makeController([]);

        $payload = $controller->callRespondToIndexAjax()->getData(true);

        $this->assertSame(['resource', 'mainFilters', 'replaceUrl'], array_keys($payload));
        $this->assertSame(1, $controller->mainFilterCalls);
    }
}
