<?php

namespace Modules\Cms\Routing;

use Closure;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Modules\Cms\Contracts\CmsLocalizationContract;

/**
 * Wraps CMS public catch-all registration so it can follow mcamara-style {@code {locale}/{path}} routing when desired.
 *
 * @see CmsFrontRouteRegistrar
 */
final class CmsFrontRouteLocalizationBinding
{
    /**
     * When {@code locale_param}, registers routes inside {@code Route::group(['prefix' => '{locale}', ...])}
     * so the first segment is a real route parameter (works with {@see LaravelLocalization} middleware).
     *
     * @param Closure():void $register Registers the inner route(s), typically {@code GET {path}} catch-all.
     */
    public static function wrapLocalizedRouteGroupIfEnabled(Closure $register): void
    {
        if (! self::shouldUseLocalePrefixRouteGroup()) {
            $register();

            return;
        }

        $keys = self::localeKeysForRouteConstraint();
        if ($keys === []) {
            $register();

            return;
        }

        $pattern = self::implodeLocalesAsRegexAlternation($keys);

        Route::group([
            'prefix' => '{locale}',
            'where' => ['locale' => $pattern],
        ], function () use ($register): void {
            $register();
        });
    }

    public static function shouldUseLocalePrefixRouteGroup(): bool
    {
        if ((string) modularousConfig('cms_routing.public_front_route_group_mode', 'catch_all') !== 'locale_param') {
            return false;
        }

        return self::isMcamaraLocalizationStackAvailable();
    }

    public static function isMcamaraLocalizationStackAvailable(): bool
    {
        $driver = (string) modularousConfig('cms_routing.localization_driver', 'auto');

        if ($driver === 'mcamara') {
            return class_exists(LaravelLocalization::class);
        }

        if ($driver === 'translatable') {
            return false;
        }

        return class_exists(LaravelLocalization::class);
    }

    /**
     * @return list<string>
     */
    public static function localeKeysForRouteConstraint(): array
    {
        if (app()->bound(CmsLocalizationContract::class)) {
            return app(CmsLocalizationContract::class)->pathSegmentLocales();
        }

        if (class_exists(LaravelLocalization::class)) {
            try {
                $keys = LaravelLocalization::getSupportedLanguagesKeys();
                if (is_array($keys) && $keys !== []) {
                    return array_values(array_map('strval', $keys));
                }
            } catch (\Throwable) {
            }
        }

        return array_values(array_unique(array_map('strval', getLocales())));
    }

    /**
     * Alternation order is longest-first so {@code pt-br} wins over {@code pt}.
     *
     * @param list<string> $localeKeys
     */
    public static function implodeLocalesAsRegexAlternation(array $localeKeys): string
    {
        $sorted = $localeKeys;
        usort($sorted, fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));

        return implode('|', array_map(static fn (string $k): string => preg_quote($k, '/'), $sorted));
    }
}
