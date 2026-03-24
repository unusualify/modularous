<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Request;
use Modules\SystemPricing\Entities\Price;
use Oobook\Priceable\Traits\HasPriceable as TraitsHasPriceable;
use Unusualify\Modularity\Entities\Mutators\HasPriceableMutators;
use Unusualify\Modularity\Facades\CurrencyExchange;
use Unusualify\Modularity\Facades\Modularity;

trait HasPriceable
{
    use TraitsHasPriceable,
        HasPriceableMutators;

    public function prices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    public function originalBasePrice(): MorphOne
    {
        return $this->morphOne(Price::class, 'priceable')
            ->with('currency')
            ->where('currency_id', Request::getCachedUserCurrency()?->id);
    }

    public function basePrice(): MorphOne
    {
        $query = $this->morphOne(Price::class, 'priceable')
            ->with('currency')
            ->where('currency_id', Request::getCachedUserCurrency()?->id);

        $currencyForLanguageBasedPrices = Modularity::getCurrencyForLanguageBasedPrices();

        if ($currencyForLanguageBasedPrices) {
            $convertedExchangeRate = CurrencyExchange::getExchangeRate($currencyForLanguageBasedPrices->iso_4217);
            $priceTable = app(Price::class)->getTable();

            $languageBasedPriceFactor = $this->getLanguageBasedPriceFactor();
            if (method_exists($this, 'getLanguageBasedPriceQuery')) {
                // Get the subquery that checks if language-based pricing conditions are met
                $conditionSubquery = $this->getLanguageBasedPriceQuery();

                // Use CASE WHEN to conditionally apply the exchange rate conversion
                $query->selectRaw(
                    "{$priceTable}.*,
                    CASE WHEN EXISTS({$conditionSubquery->toSql()})
                        THEN ROUND(({$priceTable}.raw_amount * ?) / {$languageBasedPriceFactor}, 0) * {$languageBasedPriceFactor}
                        ELSE {$priceTable}.raw_amount
                    END as raw_amount,
                    CASE WHEN EXISTS({$conditionSubquery->toSql()})
                        THEN ?
                        ELSE {$priceTable}.currency_id
                    END as currency_id,
                    CASE WHEN EXISTS({$conditionSubquery->toSql()})
                        THEN 1
                        ELSE 0
                    END as has_language_based_price",
                    array_merge(
                        $conditionSubquery->getBindings(),
                        [$convertedExchangeRate],
                        $conditionSubquery->getBindings(),
                        [$currencyForLanguageBasedPrices->id],
                        $conditionSubquery->getBindings()
                    )
                );
            } else {
                $query->selectRaw(
                    "{$priceTable}.*, ROUND(({$priceTable}.raw_amount * ?) / {$languageBasedPriceFactor}, 0) * {$languageBasedPriceFactor} as raw_amount, ? as currency_id, 1 as has_language_based_price",
                    [$convertedExchangeRate, $currencyForLanguageBasedPrices->id]
                );
            }
        }

        return $query;
    }

    public function scopeHasBasePrice($query)
    {
        return $query->whereHas('basePrice');
    }

    public function scopeOrderByCurrencyPrice($query, $currencyId, $direction = 'asc', $role = null)
    {
        $table = $this->getTable();
        $priceTable = app(Price::class)->getTable();

        return $query->leftJoin($priceTable, function ($join) use ($table, $priceTable, $currencyId, $role) {
            $join->on("{$priceTable}.priceable_id", '=', "{$table}.id")
                ->where("{$priceTable}.priceable_type", '=', get_class($this))
                ->where("{$priceTable}.currency_id", '=', $currencyId)
                ->when($role, function ($query) use ($role) {
                    $query->where('role', $role);
                });
        })
            ->orderBy("{$priceTable}.raw_amount", $direction)
            ->select("{$table}.*"); // Ensure we only select fields from the main table
    }

    /**
     * Scope a query to order by the base price's raw_amount.
     *
     * @param Builder $query
     * @param string $direction
     * @return Builder
     */
    public function scopeOrderByBasePrice($query, $direction = 'asc', $role = null)
    {
        return $query->orderByCurrencyPrice(currencyId: Request::getCachedUserCurrency()->id, direction: $direction, role: $role);
    }

    protected function getLanguageBasedPriceFactor(): int
    {
        return 10 ** (isset($this->languageBasedPricePower) ? $this->languageBasedPricePower : 0);
    }
}
