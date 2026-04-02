<?php

namespace Modules\SystemPayment\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\SystemPricing\Entities\Currency;
use Modules\SystemPricing\Entities\Price;
use Oobook\Priceable\Facades\PriceService;
use Unusualify\Modularity\Entities\Traits\Core\HasCaching;
use Unusualify\Modularity\Entities\Traits\Core\ModelHelpers;
use Unusualify\Modularity\Entities\Traits\HasCreator;
use Unusualify\Modularity\Entities\Traits\HasFileponds;
use Unusualify\Modularity\Entities\Traits\HasSpreadable;
use Unusualify\Payable\Payable;

class Payment extends \Unusualify\Payable\Models\Payment
{
    use ModelHelpers, HasFileponds, HasCreator, HasSpreadable, HasCaching;

    protected $fillable = [
        'payment_service_id',
        'payment_gateway',
        'price_id',
        'order_id',
        'amount',
        'currency_id',
        'currency',
        'status',
        'email',
        'installment',
        'parameters',
        'response',
    ];

    protected $with = [
        'fileponds',
        'creator.company',
        'spreadable',
        // 'paymentService',
    ];

    protected $appends = [
        // 'bank_receipts',
        // 'invoice_file',
        // 'amount_formatted',
        // 'invoices',

        'status_label',
        'status_color',
        'status_icon',
        'status_vuetify_icon',
        'status_vuetify_chip',
        // 'transaction_snapshot',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('price_currency_iso_4217', function (Builder $builder) {
            $pricesTable = (new Price)->getTable();
            $currenciesTable = (new Currency)->getTable();
            $paymentTable = (new static)->getTable();

            $builder->addSelect([
                'price_currency_iso_4217' => Currency::query()
                    ->select($currenciesTable . '.iso_4217')
                    ->join($pricesTable, $pricesTable . '.currency_id', '=', $currenciesTable . '.id')
                    ->whereColumn($pricesTable . '.id', $paymentTable . '.price_id')
                    ->limit(1),
            ]);
        });
        static::addGlobalScope('paymentable_morph_keys', function (Builder $builder) {
            $paymentTable = (new static)->getTable();
            $pricesTable = (new Price)->getTable();

            // Ensure base columns plus our subselects are always present
            $builder->addSelect($paymentTable . '.*')
                ->addSelect([
                    'paymentable_type' => Price::select('priceable_type')
                        ->whereColumn($pricesTable . '.id', $paymentTable . '.price_id')
                        ->limit(1),
                    'paymentable_id' => Price::select('priceable_id')
                        ->whereColumn($pricesTable . '.id', $paymentTable . '.price_id')
                        ->limit(1),
                ]);
        });
    }

    /**
     * Get the paymentService that owns the Payment.
     */
    public function paymentService(): BelongsTo
    {
        return $this->belongsTo(PaymentService::class, 'payment_service_id', 'id');
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class, 'price_id', 'id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function priceCurrency(): HasOneThrough
    {
        return $this->hasOneThrough(
            Currency::class,
            Price::class,
            'id',
            'id',
            'price_id',
            'currency_id'
        );
    }

    /**
     * Behaves like a real morphTo by providing the morph keys via subselects.
     */
    public function paymentable(): MorphTo
    {
        return $this->morphTo('paymentable');
    }

    protected function serviceClass(): Attribute
    {
        return Attribute::make(
            get: function () {
                $serviceClass = null;
                $paymentGateway = null;
                // dd(debug_backtrace());
                try {
                    // $paymentGateway = $this->paymentService->key;
                    $paymentGateway = $this->payment_gateway;
                    $serviceClass = Payable::getServiceClass($paymentGateway);
                } catch (\Exception $e) {
                    if ($e->getMessage() == 'Service class not found for slug: ' . $paymentGateway && $this->paymentService->transferrable) {
                        $serviceClass = new class extends \Unusualify\Payable\Services\PaymentService
                        {
                            public function __construct()
                            {
                                $this->mode = 'test';
                                $this->config = [];
                            }

                            public function hydrateParams(array|object $params): array
                            {
                                return $params;
                            }
                        };
                    } else {
                        throw $e;
                    }
                }

                return $serviceClass;
            }
        );
    }

    protected function amountFormatted(): Attribute
    {
        return Attribute::make(
            get: function () {
                return PriceService::formatAmount($this->amount, new \Money\Currency($this->price_currency_iso_4217 ?? $this->currency->iso_4217));
            },
        );
    }

    /**
     * The currencyServices that belong to the Payment.
     */
    public function currencyServices(): BelongsToMany
    {
        return $this->belongsToMany(PaymentCurrency::class);
    }

    /**
     * The currencies that belong to the Payment.
     */
    public function currencies(): BelongsToMany
    {
        return $this->belongsToMany(PaymentCurrency::class);
    }

    protected function bankReceipts(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->fileponds->filter(fn ($file) => $file->role == 'receipts')->map(fn ($file) => $file->mediableFormat())->values();
            }
        );
    }

    protected function invoiceFile(): Attribute
    {
        return Attribute::make(
            get: function () {
                $file = $this->fileponds->first(fn ($file) => $file->role == 'invoice');

                return $file ? $file->mediableFormat() : null;
            }
        );
    }

    protected function invoices(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->fileponds->filter(fn ($file) => $file->role == 'invoice')->map(fn ($file) => $file->mediableFormat())->values();
            }
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->status->label(),
        );
    }

    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->status->color(),
        );
    }

    protected function statusIcon(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->status->icon(),
        );
    }

    protected function statusVuetifyIcon(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->status ? "<v-icon icon='{$this->status_icon}' color='{$this->status_color}'/>" : null,
        );
    }

    protected function statusVuetifyChip(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->status
                ? "<v-chip variant='text' color='{$this->status_color}' prepend-icon='{$this->status_icon}'>{$this->status_label}</v-chip>"
                : null
        );
    }

    protected function transactionSnapshot(): Attribute
    {
        return Attribute::make(
            get: function () {
                $t = $this->parameters->modularity ?? [];

                $usingCountryBasedVatRates = $t->using_country_based_vat_rates ?? false;

                return [
                    'paid_at' => $this->updated_at,
                    'original_amount' => PriceService::formatAmount(
                        $t->original_amount ?? $this->price->raw_amount,
                        new \Money\Currency(mb_strtoupper($t->original_currency))
                    ),

                    'discount_percentage' => ($t->discount_percentage > 0 ? '%' . $t->discount_percentage : '%0'),
                    'discount_amount' => PriceService::formatAmount(
                        $t->discount_amount ?? $this->price->raw_amount - $this->price->discounted_raw_amount,
                        new \Money\Currency(mb_strtoupper($t->original_currency))
                    ),

                    'subtotal' => PriceService::formatAmount(
                        $t->subtotal ?? $t->original_raw_amount ?? $this->price->discounted_raw_amount,
                        new \Money\Currency(mb_strtoupper($t->original_currency))
                    ),
                    'original_total_amount' => PriceService::formatAmount(
                        $t->original_total_amount,
                        new \Money\Currency(mb_strtoupper($t->original_currency))
                    ),

                    'using_country_based_vat_rates' => ($usingCountryBasedVatRates) ? __('Yes') : __('No'),
                    'vat_rate_from' => $t->vat_rate_from ?? null,

                    'is_company_based_vat_rate' => ($t->is_company_based_vat_rate ?? false) ? __('Yes') : __('No'),
                    'company_type' => $t->company_type ?? null,
                    'company_based_vat_rate_name' => $t->company_based_vat_rate_name ?? null,
                    'company_based_vat_percentage' => isset($t->company_based_vat_percentage) ? ($t->company_based_vat_percentage > 0 ? '%' . $t->company_based_vat_percentage : null) : null,
                    'company_based_vat_multiplier' => $t->company_based_vat_multiplier ?? 0,
                    'company_based_total_amount' => PriceService::formatAmount(
                        $t->company_based_total_amount ?? $t->original_total_amount,
                        new \Money\Currency(mb_strtoupper($t->converted_currency ?? $t->original_currency))
                    ),

                    'final_vat_percentage' => '%' . ($usingCountryBasedVatRates ? ($t->company_based_vat_percentage ?? $t->vat_percentage) : $t->vat_percentage),

                    'is_converted' => ($t->converted ?? false) ? __('Yes') : __('No'),
                    'converted_currency' => $t->converted_currency ?? $t->original_currency,
                    'exchange_rate' => $t->exchange_rate ?? 1,
                    'converted_raw_amount' => PriceService::formatAmount(
                        $t->converted_raw_amount ?? $t->subtotal ?? $t->original_raw_amount ?? $this->price->discounted_raw_amount,
                        new \Money\Currency(mb_strtoupper($t->converted_currency ?? $t->original_currency))
                    ),
                    'converted_amount' => PriceService::formatAmount(
                        $t->converted_total_amount,
                        new \Money\Currency(mb_strtoupper($t->converted_currency ?? $t->original_currency))
                    ),
                    'converted_currency' => $t->converted_currency,

                    'transaction_fee_exists' => ($t->transaction_fee_exists ?? false) ? __('Yes') : __('No'),
                    'transaction_fee_percentage' => isset($t->transaction_fee_percentage) ? ($t->transaction_fee_percentage > 0 ? '%' . $t->transaction_fee_percentage : '%0') : '%0',
                    'transaction_fee_amount' => PriceService::formatAmount(
                        $t->transaction_fee_amount ?? 0,
                        new \Money\Currency(mb_strtoupper($t->converted_currency ?? $t->original_currency))
                    ),
                    'paid_amount' => PriceService::formatAmount(
                        $t->total_amount_with_transaction_fee ?? $t->converted_amount ?? $this->amount,
                        new \Money\Currency(mb_strtoupper($t->converted_currency ?? $t->original_currency))
                    ),
                ];
            }
        );
    }
}
