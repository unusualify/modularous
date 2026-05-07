<?php

namespace Unusualify\Modularity\Tests\Services\Cms;

use Illuminate\Http\Request;
use Modules\Cms\Support\CmsPublicSeo;
use Unusualify\Modularity\Tests\TestCase;

class CmsPublicSeoTest extends TestCase
{
    public function test_robots_defaults_to_index_follow_when_null(): void
    {
        $request = Request::create('https://example.test/cms/tr/foo', 'GET');
        $translation = (object) [
            'seo_title' => 'T',
            'title' => 'T',
            'seo_description' => null,
            'canonical_url' => null,
            'robots_index' => null,
            'robots_follow' => null,
        ];

        $canonical = new \Modules\Cms\Services\CanonicalUrlResolver;
        $out = CmsPublicSeo::build($request, $translation, $canonical);

        $this->assertSame('index, follow', $out['robotsMeta']);
    }

    public function test_custom_canonical_absolute_is_preserved(): void
    {
        $request = Request::create('https://example.test/cms/foo', 'GET');
        $translation = (object) [
            'seo_title' => 'T',
            'title' => 'T',
            'canonical_url' => 'https://other.example/path',
            'robots_index' => true,
            'robots_follow' => true,
        ];

        $canonical = new \Modules\Cms\Services\CanonicalUrlResolver;
        $out = CmsPublicSeo::build($request, $translation, $canonical);

        $this->assertSame('https://other.example/path', $out['canonicalUrl']);
    }
}
