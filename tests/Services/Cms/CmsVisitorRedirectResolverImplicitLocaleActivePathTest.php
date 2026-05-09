<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Entities\Page;
use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Localization\TranslatableCmsLocalizationAdapter;
use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Services\CmsVisitorRedirectResolver;
use Unusualify\Modularous\Tests\TestCase;

final class CmsVisitorRedirectResolverImplicitLocaleActivePathTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createUrlRoutesTable();
        $this->app->singleton(CanonicalUrlResolverInterface::class, CanonicalUrlResolver::class);
    }

    public function test_implicit_path_not_active_when_only_non_fallback_locale_row_exists_slugless_on(): void
    {
        $this->app['config']->set('translatable.fallback_locale', 'en');
        $this->app['config']->set('modularous.cms_routing.default_locale', 'tr');
        $this->app['config']->set('modularous.cms_routing.fallback_locale_optional_path_segment', true);

        $canonical = app(CanonicalUrlResolverInterface::class);
        $localization = new TranslatableCmsLocalizationAdapter($canonical);
        $resolver = new CmsVisitorRedirectResolver($canonical, $localization);

        UrlRoute::query()->create([
            'locale' => 'tr',
            'normalized_path' => '/sayfalar/deneme-2',
            'urlable_type' => Page::class,
            'urlable_id' => 1,
            'kind' => UrlRoute::KIND_PAGE_PUBLIC,
        ]);

        $this->assertFalse($resolver->isActivePagePath('en', '/sayfalar/deneme-2', false));
        $this->assertTrue($resolver->isActivePagePath('tr', '/sayfalar/deneme-2', true));
    }

    public function test_implicit_path_active_for_fallback_locale_row_when_slugless_on(): void
    {
        $this->app['config']->set('translatable.fallback_locale', 'en');
        $this->app['config']->set('modularous.cms_routing.default_locale', 'tr');
        $this->app['config']->set('modularous.cms_routing.fallback_locale_optional_path_segment', true);

        $canonical = app(CanonicalUrlResolverInterface::class);
        $localization = new TranslatableCmsLocalizationAdapter($canonical);
        $resolver = new CmsVisitorRedirectResolver($canonical, $localization);

        UrlRoute::query()->create([
            'locale' => 'en',
            'normalized_path' => '/pages/test',
            'urlable_type' => Page::class,
            'urlable_id' => 2,
            'kind' => UrlRoute::KIND_PAGE_PUBLIC,
        ]);

        $this->assertTrue($resolver->isActivePagePath('en', '/pages/test', false));
    }

    public function test_implicit_path_only_matches_cms_default_locale_when_slugless_off(): void
    {
        $this->app['config']->set('translatable.fallback_locale', 'en');
        $this->app['config']->set('modularous.cms_routing.default_locale', 'tr');
        $this->app['config']->set('modularous.cms_routing.fallback_locale_optional_path_segment', false);

        $canonical = app(CanonicalUrlResolverInterface::class);
        $localization = new TranslatableCmsLocalizationAdapter($canonical);
        $resolver = new CmsVisitorRedirectResolver($canonical, $localization);

        UrlRoute::query()->create([
            'locale' => 'tr',
            'normalized_path' => '/sayfalar/deneme-2',
            'urlable_type' => Page::class,
            'urlable_id' => 3,
            'kind' => UrlRoute::KIND_PAGE_PUBLIC,
        ]);

        $this->assertTrue($resolver->isActivePagePath('tr', '/sayfalar/deneme-2', false));

        UrlRoute::query()->create([
            'locale' => 'en',
            'normalized_path' => '/pages/only-en',
            'urlable_type' => Page::class,
            'urlable_id' => 4,
            'kind' => UrlRoute::KIND_PAGE_PUBLIC,
        ]);

        $this->assertFalse($resolver->isActivePagePath('tr', '/pages/only-en', false));
    }

    protected function createUrlRoutesTable(): void
    {
        $t = modularousConfig('tables.cms_url_routes', 'um_cms_url_routes');
        Schema::dropIfExists($t);
        Schema::create($t, function (Blueprint $table): void {
            $table->id();
            $table->string('locale', 12)->index();
            $table->string('normalized_path', 2048);
            $table->morphs('urlable');
            $table->string('kind', 32)->nullable()->index();
            $table->timestamps();

            $table->unique(['locale', 'normalized_path']);
        });
    }
}
