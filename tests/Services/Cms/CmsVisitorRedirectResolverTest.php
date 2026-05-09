<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Modules\Cms\Localization\TranslatableCmsLocalizationAdapter;
use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Services\CmsVisitorRedirectResolver;
use Unusualify\Modularous\Tests\TestCase;

class CmsVisitorRedirectResolverTest extends TestCase
{
    private function makeResolver(): CmsVisitorRedirectResolver
    {
        $canonical = new CanonicalUrlResolver;

        return new CmsVisitorRedirectResolver($canonical, new TranslatableCmsLocalizationAdapter($canonical));
    }

    public function test_resolve_locale_and_inner_path_strips_locale_prefix(): void
    {
        config(['translatable.locales' => ['en', 'tr']]);
        config(['modularous.cms_routing.default_locale' => 'en']);

        $resolver = $this->makeResolver();

        [$locale, $inner, $explicit] = $resolver->resolveLocaleAndInnerPath('/tr/foo/bar');

        $this->assertSame('tr', $locale);
        $this->assertSame('/foo/bar', $inner);
        $this->assertTrue($explicit);
    }

    public function test_resolve_locale_and_inner_path_uses_default_when_no_prefix(): void
    {
        config(['translatable.locales' => ['en', 'tr']]);
        config(['modularous.cms_routing.default_locale' => 'en']);

        $resolver = $this->makeResolver();

        [$locale, $inner, $explicit] = $resolver->resolveLocaleAndInnerPath('/about');

        $this->assertSame('en', $locale);
        $this->assertSame('/about', $inner);
        $this->assertFalse($explicit);
    }

    public function test_resolve_locale_prefers_longer_locale_codes_first(): void
    {
        config(['translatable.locales' => ['pt', 'pt-br']]);
        config(['modularous.cms_routing.default_locale' => 'en']);

        $resolver = $this->makeResolver();

        [$locale, $inner, $explicit] = $resolver->resolveLocaleAndInnerPath('/pt-br/produtos');

        $this->assertSame('pt-br', $locale);
        $this->assertSame('/produtos', $inner);
        $this->assertTrue($explicit);
    }

    public function test_resolve_locale_path_key_prefers_route_locale_and_path_parameters(): void
    {
        config(['translatable.locales' => ['en', 'tr']]);
        config(['modularous.cms_routing.default_locale' => 'en']);

        $resolver = $this->makeResolver();

        $request = Request::create('http://frontend.test/en/blog/my-post', 'GET');
        $route = new Route(['GET'], '{locale}/{path}', []);
        $route->where('path', '.*');
        $route->bind($request);
        $request->setRouteResolver(static fn () => $route);

        [$locale, $pathKey, $explicit] = $resolver->resolveLocalePathKeyAndExplicitFlag($request);

        $this->assertSame('en', $locale);
        $this->assertSame('/blog/my-post', $pathKey);
        $this->assertTrue($explicit);
    }

    public function test_resolve_locale_path_key_falls_back_when_route_locale_not_in_allowed_locales(): void
    {
        config(['translatable.locales' => ['en', 'tr']]);
        config(['modularous.cms_routing.default_locale' => 'en']);

        $resolver = $this->makeResolver();

        $request = Request::create('http://frontend.test/xx/blog/my-post', 'GET');
        $route = new Route(['GET'], '{locale}/{path}', []);
        $route->where('path', '.*');
        $route->bind($request);
        $request->setRouteResolver(static fn () => $route);

        [$locale, $pathKey, $explicit] = $resolver->resolveLocalePathKeyAndExplicitFlag($request);

        $this->assertSame('en', $locale);
        $this->assertSame('/xx/blog/my-post', $pathKey);
        $this->assertFalse($explicit);
    }
}
