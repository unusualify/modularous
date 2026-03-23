<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Services\Currency;

use Illuminate\Support\Facades\Cache;
use Unusualify\Modularity\Contracts\CurrencyProviderInterface;

/**
 * Currency provider using Modules\SystemPricing\Entities\Currency.
 * Only used when SystemPricing module is enabled.
 */
class SystemPricingCurrencyProvider implements CurrencyProviderInterface
{
    public function findByIso4217(string $isoCode): ?object
    {
        if (! class_exists(\Modules\SystemPricing\Entities\Currency::class)) {
            return null;
        }

        return Cache::remember('currency_by_iso_4217_' . $isoCode, now()->addHours(1), function () use ($isoCode) {
            return \Modules\SystemPricing\Entities\Currency::query()
                ->where('iso_4217', mb_strtoupper($isoCode))
                ->first();
        });
    }

    public function findById(int $id): ?object
    {
        if (! class_exists(\Modules\SystemPricing\Entities\Currency::class)) {
            return null;
        }

        return Cache::remember('currency_by_id_' . $id, now()->addHours(1), function () use ($id) {
            return \Modules\SystemPricing\Entities\Currency::find($id);
        });
    }

    public function getCurrenciesForSelect(): array
    {
        if (! class_exists(\Modules\SystemPricing\Entities\Currency::class)) {
            return [];
        }

        return \Modules\SystemPricing\Entities\Currency::query()
            ->select(['id', 'symbol as name', 'iso_4217 as iso'])
            ->when(
                modularityConfig('services.currency_exchange.active'),
                fn ($q) => $q->where('iso_4217', mb_strtoupper(modularityConfig('services.currency_exchange.base_currency', 'EUR')))
            )
            ->get()
            ->toArray();
    }

    public function isAvailable(): bool
    {
        return class_exists(\Modules\SystemPricing\Entities\Currency::class);
    }
}
