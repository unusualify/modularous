<?php

namespace Modules\SystemPayment\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Modules\SystemPricing\Entities\Currency;
use Modules\SystemPricing\Entities\Price;
use Unusualify\Modularity\Entities\Traits\Core\HasCaching;
use Unusualify\Modularity\Entities\Traits\Core\ModelHelpers;
use Unusualify\Modularity\Entities\Traits\HasCreator;
use Unusualify\Modularity\Entities\Traits\HasFileponds;
use Unusualify\Modularity\Entities\Traits\HasSpreadable;
use Unusualify\Modularity\Relations\PaymentableRelation;

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

    protected $appends = [
        'bank_receipts',
        'invoice_file',
        'amount_formatted',
        'invoices',

        'status_label',
        'status_color',
        'status_icon',
        'status_vuetify_icon',
        'status_vuetify_chip',
        'transaction_snapshot',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('paymentable_morph_keys', function (\Illuminate\Database\Eloquent\Builder $builder) {
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
    public function paymentService(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\SystemPayment\Entities\PaymentService::class, 'payment_service_id', 'id');
    }

    public function price(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Price::class, 'price_id', 'id');
    }

    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
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
    public function paymentable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo('paymentable');
    }
    // /**
    //  * Behaves like a real morphTo by providing the morph keys via subselects.
    //  */
    // public function paymentable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    // {
    //     return new PaymentableRelation($this);
    // }

    protected function serviceClass(): Attribute
    {
        $serviceClass = null;
        $paymentGateway = null;
        try {
            $paymentGateway = $this->paymentService->key;
            $serviceClass = \Unusualify\Payable\Payable::getServiceClass($paymentGateway);
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

        return Attribute::make(
            get: fn ($value) => $serviceClass,
        );
    }

    protected function amountFormatted(): Attribute
    {
        $currency = Currency::find($this->currency_id);
        $moneyCurrency = new \Money\Currency($currency->iso_4217);

        return Attribute::make(
            get: fn ($value) => \Oobook\Priceable\Facades\PriceService::formatAmount($this->amount, $moneyCurrency),
        );
    }

    /**
     * The currencyServices that belong to the Payment.
     */
    public function currencyServices(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\Modules\SystemPayment\Entities\PaymentCurrency::class);
    }

    /**
     * The currencies that belong to the Payment.
     */
    public function currencies(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\Modules\SystemPayment\Entities\PaymentCurrency::class);
    }

    protected function bankReceipts(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->fileponds()->where('role', 'receipts')->get()->map(fn ($file) => $file->mediableFormat()),
        );
    }

    protected function invoiceFile(): Attribute
    {
        $file = $this->fileponds()->where('role', 'invoice')->first();

        return Attribute::make(
            get: fn ($value) => $file ? $file->mediableFormat() : null,
        );
    }

    protected function invoices(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->fileponds()->where('role', 'invoice')->get()->map(fn ($file) => $file->mediableFormat()),
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
                    'original_amount' => \Oobook\Priceable\Facades\PriceService::formatAmount(
                        $t->original_amount ?? $this->price->raw_amount,
                        new \Money\Currency(strtoupper($t->original_currency))
                    ),
                    'discount_percentage' => ($t->discount_percentage > 0 ? '%' . $t->discount_percentage : '%0'),
                    'discount_amount' => \Oobook\Priceable\Facades\PriceService::formatAmount(
                        $t->discount_amount ?? $this->price->raw_amount - $this->price->discounted_raw_amount,
                        new \Money\Currency(strtoupper($t->original_currency))
                    ),
                    'subtotal' => \Oobook\Priceable\Facades\PriceService::formatAmount(
                        $t->subtotal ?? $t->original_raw_amount ?? $this->price->discounted_raw_amount,
                        new \Money\Currency(strtoupper($t->original_currency))
                    ),
                    'original_total_amount' => \Oobook\Priceable\Facades\PriceService::formatAmount(
                        $t->original_total_amount,
                        new \Money\Currency(strtoupper($t->original_currency))
                    ),

                    'using_country_based_vat_rates' => ($usingCountryBasedVatRates) ? __('Yes') : __('No'),
                    'vat_rate_from' => $t->vat_rate_from ?? null,

                    'is_company_based_vat_rate' => ($t->is_company_based_vat_rate ?? false) ? __('Yes') : __('No'),
                    'company_type' => $t->company_type ?? null,
                    'company_based_vat_rate_name' => $t->company_based_vat_rate_name ?? null,
                    'company_based_vat_percentage' => isset($t->company_based_vat_percentage) ? ($t->company_based_vat_percentage > 0 ? '%' . $t->company_based_vat_percentage : null) : null,
                    'company_based_vat_multiplier' => $t->company_based_vat_multiplier ?? 0,
                    'company_based_total_amount' => \Oobook\Priceable\Facades\PriceService::formatAmount(
                        $t->company_based_total_amount ?? $t->original_total_amount,
                        new \Money\Currency(strtoupper($t->converted_currency ?? $t->original_currency))
                    ),

                    'final_vat_percentage' => '%' . ($usingCountryBasedVatRates ? ($t->company_based_vat_percentage ?? $t->vat_percentage) : $t->vat_percentage),

                    'is_converted' => ($t->converted ?? false) ? __('Yes') : __('No'),
                    'converted_currency' => $t->converted_currency ?? $t->original_currency,
                    'exchange_rate' => $t->exchange_rate ?? 1,
                    'converted_raw_amount' => \Oobook\Priceable\Facades\PriceService::formatAmount(
                        $t->converted_raw_amount,
                        new \Money\Currency(strtoupper($t->converted_currency ?? $t->original_currency))
                    ),
                    'converted_amount' => \Oobook\Priceable\Facades\PriceService::formatAmount(
                        $t->converted_total_amount,
                        new \Money\Currency(strtoupper($t->converted_currency ?? $t->original_currency))
                    ),
                    'converted_currency' => $t->converted_currency,

                    'transaction_fee_exists' => ($t->transaction_fee_exists ?? false) ? __('Yes') : __('No'),
                    'transaction_fee_percentage' => isset($t->transaction_fee_percentage) ? ($t->transaction_fee_percentage > 0 ? '%' . $t->transaction_fee_percentage : '%0') : '%0',
                    'transaction_fee_amount' => \Oobook\Priceable\Facades\PriceService::formatAmount(
                        $t->transaction_fee_amount ?? 0,
                        new \Money\Currency(strtoupper($t->converted_currency ?? $t->original_currency))
                    ),
                    'total_amount_with_transaction_fee' => \Oobook\Priceable\Facades\PriceService::formatAmount(
                        $t->total_amount_with_transaction_fee ?? $t->converted_amount ?? $t->original_total_amount,
                        new \Money\Currency(strtoupper($t->converted_currency ?? $t->original_currency))
                    ),
                ];
            }
        );
    }
}
