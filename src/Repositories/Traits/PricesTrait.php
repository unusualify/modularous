<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Modules\SystemPricing\Entities\Price;
use Oobook\Priceable\Models\Currency;
use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Facades\CurrencyExchange;

trait PricesTrait
{
    /**
     * When true, {@see RevisionsTrait::bypassAfterSaves} may set `passAfterSavePricesTrait` during pending-only
     * revision saves so {@see afterSavePricesTrait} is skipped.
     */
    protected bool $pendingBypassRevisionPricesTrait = true;

    protected $formatableColumns = [
        'id',
        'raw_amount',
        'currency_id',
        'vat_rate_id',
        'price_type_id',
        'discount_percentage',
    ];

    public function setColumnsPricesTrait($columns, $inputs)
    {
        $traitName = get_class_short_name(__TRAIT__);

        $columns[$traitName] = collect($inputs)->reduce(function ($acc, $curr) {
            if (preg_match('/price/', $curr['type'])) {
                $acc[] = $curr['name'];
            }

            return $acc;
        }, []);

        return $columns;
    }

    /**
     * @param Model $object
     * @param array $fields
     * @return void
     */
    public function afterSavePricesTrait($object, $fields)
    {
        // if ($this->shouldIgnoreFieldBeforeSave('prices')) {
        //     return;
        // }

        $onlyBaseCurrency = modularityConfig('services.currency_exchange.active');
        $baseCurrencyIso4217 = modularityConfig('services.currency_exchange.base_currency');

        $priceSavingKey = Price::$priceSavingKey;

        foreach ($this->getColumns(__TRAIT__) as $name) {
            if ($name !== 'payment' && isset($fields[$name])) {
                $existingPrices = $object->prices()->where('role', $name)->get();
                $defaultPriceAttributes = $object->prices()->getRelated()->defaultAttributes();

                foreach ($fields[$name] as $priceData) {
                    $priceModel = isset($priceData['id'])
                        ? $existingPrices->where('id', $priceData['id'])->first()
                        : null;
                    $data = array_merge_recursive_preserve($defaultPriceAttributes, $priceData + ['role' => $name]);

                    if ($priceModel) {
                        // Update existing price
                        $priceModel->update($data);
                        if ($priceModel->wasChanged()) {
                            $this->mustTouchEloquentModel();
                        }
                    } else {
                        // Create a new price
                        $object->prices()->create(Arr::except($data, ['id']));
                        $this->mustTouchEloquentModel();
                    }

                    if ($onlyBaseCurrency) {
                        foreach (modularityConfig('enabled_currencies') as $key => $iso4217) {
                            $_currency = Currency::where('iso_4217', $iso4217)->first();
                            if (! $_currency) {
                                continue;
                            }
                            if ($_currency->iso_4217 !== $baseCurrencyIso4217) {
                                $_data = array_merge($data, [
                                    $priceSavingKey => round(CurrencyExchange::convertTo($data[$priceSavingKey], $_currency->iso_4217), 2),
                                    'currency_id' => $_currency->id,
                                ]);

                                if ($existingPrices->where('currency_id', $_currency->id)->count() == 0) {
                                    $object->prices()->create(Arr::except($_data, ['id']));
                                    $this->mustTouchEloquentModel();
                                } else {
                                    $existingPrice = $existingPrices->where('currency_id', $_currency->id)->first();
                                    $existingPrice->update(Arr::except($_data, ['id']));
                                    if ($existingPrice->wasChanged()) {
                                        $this->mustTouchEloquentModel();
                                    }
                                }
                                sleep(1);
                            }
                        }
                    }

                }

                if ($existingPrices && ! $onlyBaseCurrency) {
                    $pricesToDelete = $existingPrices->whereNotIn('id', Arr::pluck($fields[$name], 'id'));
                    $pricesToDelete->each->delete();
                    $this->mustTouchEloquentModel();
                }
            }
        }

    }

    /**
     * @param Model $object
     * @param array $fields
     * @return array
     */
    public function getFormFieldsPricesTrait($object, $fields)
    {
        if (method_exists($object, 'prices') && get_class($object->prices()) === 'Illuminate\Database\Eloquent\Relations\MorphMany') {
            $priceSavingKey = Price::$priceSavingKey;
            $onlyBaseCurrency = modularityConfig('services.currency_exchange.active');
            $priceModel = $object->prices()->getRelated();
            $defaultPriceAttributes = $priceModel->defaultAttributes();

            $query = $object->prices();

            if ($onlyBaseCurrency) {
                $query = $query->where('currency_id', Request::getCachedUserCurrency()->id);
            }

            $prices = null;
            $pricesByRole = null;

            foreach ($this->getColumns(__TRAIT__) as $role) {
                if (! isset($prices)) {
                    $prices = $query->where('role', $role)->get();
                    $pricesByRole = $prices->groupBy('role');
                }
                if (isset($pricesByRole[$role])) {
                    $fields[$role] = $pricesByRole[$role]->map(function ($price) use ($priceSavingKey) {
                        return Arr::mapWithKeys(Arr::only($price->toArray(), array_merge($this->formatableColumns, [$priceSavingKey])), function ($val, $key) use ($priceSavingKey) {
                            if (preg_match('/display_price|price_excluding|price_including|raw_amount|' . $priceSavingKey . '/', $key)) {
                                return [$key => (float) $val];
                            }

                            return [$key => $val];
                        });
                    });
                } else {
                    $fields[$role] = [
                        array_merge_recursive_preserve($defaultPriceAttributes, [
                            $priceSavingKey => 0.00,
                            'raw_amount' => 0.00,
                            'currency_id' => Request::getUserCurrency()->id]
                        ),
                    ];
                }
            }
        }

        return $fields;
    }
}
