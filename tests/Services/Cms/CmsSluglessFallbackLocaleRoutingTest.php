<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Modules\Cms\Localization\TranslatableCmsLocalizationAdapter;
use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Services\CmsVisitorRedirectResolver;
use Modules\Cms\Support\CmsSluglessFallbackLocale;
use Unusualify\Modularous\Tests\TestCase;

class CmsSluglessFallbackLocaleRoutingTest extends TestCase
{
    public function test_implicit_path_uses_fallback_chain_when_slugless_toggle_on(): void
    {
        $this->app['config']->set('translatable.fallback_locale', 'en');
        $this->app['config']->set('modularous.cms_routing.default_locale', 'tr');
        $this->app['config']->set('modularous.cms_routing.fallback_locale_optional_path_segment', true);
        $this->app['config']->set('modularous.cms_routing.fallback_locale_optional_path_segment_locale', null);

        $canonical = new CanonicalUrlResolver;
        $localization = new TranslatableCmsLocalizationAdapter($canonical);
        $resolver = new CmsVisitorRedirectResolver($canonical, $localization);

        [$locale, $path, $explicit] = $resolver->resolveLocaleAndInnerPath('/pages/test');

        $this->assertFalse($explicit);
        $this->assertSame('/pages/test', $path);
        $this->assertSame('en', $locale);
        $this->assertSame('en', CmsSluglessFallbackLocale::resolvedCode());
    }

    public function test_implicit_path_uses_cms_default_locale_when_slugless_toggle_off(): void
    {
        $this->app['config']->set('translatable.fallback_locale', 'en');
        $this->app['config']->set('modularous.cms_routing.default_locale', 'tr');
        $this->app['config']->set('modularous.cms_routing.fallback_locale_optional_path_segment', false);

        $canonical = new CanonicalUrlResolver;
        $localization = new TranslatableCmsLocalizationAdapter($canonical);
        $resolver = new CmsVisitorRedirectResolver($canonical, $localization);

        [$locale, $path, $explicit] = $resolver->resolveLocaleAndInnerPath('/pages/test');

        $this->assertFalse($explicit);
        $this->assertSame('/pages/test', $path);
        $this->assertSame('tr', $locale);
    }
}
