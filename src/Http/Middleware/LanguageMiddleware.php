<?php

namespace Unusualify\Modularous\Http\Middleware;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Contracts\CurrencyProviderInterface;
use Unusualify\Modularous\Facades\ModularousLog;

class LanguageMiddleware
{
    /**
     * Handles an incoming request.
     *
     * @param Request $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $defaultLocale = app()->getLocale();
        $fallbackLocale = app()->getFallbackLocale();
        $locale = $defaultLocale;
        $availableUserLocales = modularousConfig('available_user_locales');
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $request->ip();

        if ($request->has('language')) {
            $locale = $request->get('language');
        } elseif ($request->user() && $request->user()->language) {
            $locale = $request->user()->language;
        } else {
            try {
                if (env('MODULAROUS_AUTO_LOCALE_FINDER', false)) {
                    $newLocale = mb_strtolower(geoip()->getLocation($ip)->iso_code);

                    if (in_array($newLocale, $availableUserLocales)) {
                        $locale = $newLocale;
                    }
                }

            } catch (\Exception $e) {
                ModularousLog::error('LanguageMiddleware: Error while finding locale with geoip: ' . $e->getMessage());
            }
        }

        if ($locale !== $fallbackLocale && Route::currentRouteName() == 'languages.translations.index') {
            $locale = $fallbackLocale;
        }

        config([modularousBaseKey() . '.locale' => $locale]);
        config([modularousBaseKey() . '.timezone' => auth()->user()->timezone ?? 'Europe/London']);

        App::setLocale($locale);
        App::setFallbackLocale(modularousConfig('fallback_locale'));

        $currency = config('priceable.currency', 'EUR');

        if (! modularousConfig('services.currency_exchange.active')) { // onlyBaseCurrency
            $currency = modularousConfig("payment.locale_currencies.{$locale}", null)
                ?? config('priceable.currency');
        }

        if ($currency !== mb_strtoupper(config('priceable.currency'))) {
            config(['priceable.currency' => $currency]);
            $provider = App::make(CurrencyProviderInterface::class);
            if ($provider->isAvailable()) {
                $currencyModel = $provider->findByIso4217(config('priceable.currency'));
                if ($currencyModel && method_exists($request, 'setUserCurrency')) {
                    $request->setUserCurrency($currencyModel);
                }
            }
        }

        config(['priceable.currency_locale' => config('app.locale')]);

        CarbonInterval::setLocale(config('app.locale'));
        Carbon::setLocale(config('app.locale'));

        return $next($request);
    }
}
