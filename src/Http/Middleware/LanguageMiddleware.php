<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Oobook\Priceable\Models\Currency;
use Unusualify\Modularity\Facades\ModularityLog;

class LanguageMiddleware
{
    /**
     * Handles an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $defaultLocale = app()->getLocale();
        $fallbackLocale = app()->getFallbackLocale();
        $locale = $defaultLocale;
        $availableUserLocales = modularityConfig('available_user_locales');
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $request->ip();

        if ($request->has('language')) {
            $locale = $request->get('language');
        } elseif ($request->user() && $request->user()->language) {
            $locale = $request->user()->language;
        } else {
            try {
                if (env('MODULARITY_AUTO_LOCALE_FINDER', false)) {
                    $newLocale = mb_strtolower(geoip()->getLocation($ip)->iso_code);

                    if (in_array($newLocale, $availableUserLocales)) {
                        $locale = $newLocale;
                    }
                }

            } catch (\Exception $e) {
                ModularityLog::error('LanguageMiddleware: Error while finding locale with geoip: ' . $e->getMessage());
            }
        }

        if ($locale !== $fallbackLocale && \Illuminate\Support\Facades\Route::currentRouteName() == 'languages.translations.index') {
            $locale = $fallbackLocale;
        }

        config([modularityBaseKey() . '.locale' => $locale]);
        config([modularityBaseKey() . '.timezone' => auth()->user()->timezone ?? 'Europe/London']);

        App::setLocale($locale);
        App::setFallbackLocale(modularityConfig('fallback_locale'));

        $currency = config('priceable.currency', 'EUR');

        if (! modularityConfig('services.currency_exchange.active')) { // onlyBaseCurrency
            $currency = modularityConfig("payment.locale_currencies.{$locale}", null)
                ?? config('priceable.currency');
        }

        if ($currency !== mb_strtoupper(config('priceable.currency'))) {
            config(['priceable.currency' => $currency]);
            $currencyModel = Currency::where('iso_4217', config('priceable.currency'))->first();
            $request->setUserCurrency($currencyModel);
        }

        config(['priceable.currency_locale' => config('app.locale')]);

        \Carbon\CarbonInterval::setLocale(config('app.locale'));
        \Carbon\Carbon::setLocale(config('app.locale'));

        return $next($request);
    }
}
