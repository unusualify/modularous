<?php

namespace Unusualify\Modularity\Tests\Services\Cms;

use Illuminate\Http\Request;
use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Support\CmsFrontPath;
use Unusualify\Modularity\Tests\TestCase;

class CmsFrontPathTest extends TestCase
{
    public function test_it_strips_configured_cms_prefix(): void
    {
        config(['modularity.cms_routing.front_route_prefix' => 'cms']);

        $canonical = new CanonicalUrlResolver;
        $request = Request::create('/cms/tr/foo', 'GET');

        $inner = CmsFrontPath::innerNormalizedPath($request, $canonical);

        $this->assertSame('/tr/foo', $inner);
    }

    public function test_it_returns_slash_when_only_prefix(): void
    {
        config(['modularity.cms_routing.front_route_prefix' => 'cms']);

        $canonical = new CanonicalUrlResolver;
        $request = Request::create('/cms', 'GET');

        $inner = CmsFrontPath::innerNormalizedPath($request, $canonical);

        $this->assertSame('/', $inner);
    }
}
