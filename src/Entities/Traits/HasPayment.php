<?php

namespace Unusualify\Modularous\Entities\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\SystemPayment\Entities\Payment;
use Modules\SystemPricing\Entities\Price;
use Money\Currency;
use Oobook\Priceable\Facades\PriceService;
use Unusualify\Modularous\Entities\Enums\PaymentStatus;

trait HasPayment
{
    // Will be defining the relation between the completed payment model and payable model
    use HasPriceable;

    public static function bootHasPayment(): void
    {
        self::retrieved(static function (Model $model) {
            if ($model->paymentPrice) {
                // $currency = new Currency($model->paymentPrice->currency->iso_4217);
                // $model->setAttribute('_price', \Oobook\Priceable\Facades\PriceService::formatAmount($model->paymentPrice->raw_amount, $currency));
                // $model->setAttribute('priceExcludingVatFormatted', \Oobook\Priceable\Facades\PriceService::formatAmount($model->paymentPrice->raw_amount, $currency));
                // $model->setAttribute('paymentStatus', match (true) {
                //     ! $model->paidPrices()->exists() => PaymentStatus::UNPAID,
                //     $model->payablePrice?->price_including_vat > 0 => PaymentStatus::PARTIALLY_PAID,
                //     default => PaymentStatus::PAID
                // });
                // $model->setAttribute('paymentStatusTranslated', match (true) {
                //     ! $model->paidPrices()->exists() => __('Unpaid'),
                //     $model->payablePrice?->total_amount > 0 => __('Partially Paid'),
                //     default => __('Paid')
                // });
            }
        });

        self::updating(static function (Model $model) {
            // if (isset($model->_price)) {
            //     $model->offsetUnset('_price');
            //     $model->offsetUnset('priceExcludingVatFormatted');
            //     $model->offsetUnset('paymentStatus');
            //     $model->offsetUnset('paymentStatusTranslated');
            // }
        });

        self::saving(static function (Model $model) {
            $model->offsetUnset('_price');
            $model->offsetUnset('priceExcludingVatFormatted');
            $model->offsetUnset('paymentStatus');
            $model->offsetUnset('paymentStatusTranslated');
        });

    }

    public function initializeHasPayment(): void
    {
        $this->append([
            'is_paid',
            'is_partially_paid',
            'is_provided',
            'is_unpaid',
            'is_refunded',
            'payment_status_formatted',
        ]);
    }

    public static function addGlobalScopesHasPayment()
    {
        return [
            'paid_prices_exists' => [
                'scope' => function ($query) {
                    $query->withExists('paidPrices');
                },
            ],
            'payable_price_exists' => [
                'scope' => function ($query) {
                    $query->withExists('payablePrice');
                },
            ],
            'provided_prices_exists' => [
                'scope' => function ($query) {
                    $query->withExists('providedPrices');
                },
            ],
            'refunded_prices_exists' => [
                'scope' => function ($query) {
                    $query->withExists('refundedPrices');
                },
            ],
        ];
    }

    public function paymentPrices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable')
            ->where('role', 'payment');
    }

    public function paymentPrice(): MorphOne
    {
        return $this->morphOne(Price::class, 'priceable')
            ->where('role', 'payment')
            ->latest('created_at');

        // $priceTable = (new Price)->getTable();
        // $morphClass = addslashes($this->getMorphClass());

        // return $this->morphOne(Price::class, 'priceable')
        //     ->where('role', 'payment')
        //     ->whereRaw("created_at = (
        //         SELECT MAX(p2.created_at)
        //         FROM {$priceTable} p2
        //         WHERE p2.priceable_id = {$priceTable}.priceable_id
        //         AND p2.priceable_type = {$priceTable}.priceable_type
        //         AND p2.role = ?
        //     )", ['payment']);

        // return $this->morphOne(Price::class, 'priceable')
        //     ->whereRaw("{$priceTable}.created_at = (select max(created_at) from {$priceTable} where {$priceTable}.priceable_id = '{$this->id}' and {$priceTable}.priceable_type = '{$morphClass}' and {$priceTable}.role = 'payment')");

        // return $this->morphOne(Price::class, 'priceable')
        //     ->where('role', 'payment')
        //     ->latest('created_at');
    }

    public function initialPayablePrice(): MorphOne
    {
        return $this->morphOne(Price::class, 'priceable')
            ->where('role', 'payment')
            ->oldest('created_at');

        // $priceTable = (new Price)->getTable();
        // $morphClass = addslashes($this->getMorphClass());

        // return $this->morphOne(Price::class, 'priceable')
        //     ->whereRaw("{$priceTable}.created_at = (select min(created_at) from {$priceTable} where {$priceTable}.priceable_id = '{$this->id}' and {$priceTable}.priceable_type = '{$morphClass}' and {$priceTable}.role = 'payment')");

    }

    public function payablePrice(): MorphOne
    {
        return $this->morphOne(Price::class, 'priceable')
            ->where('role', 'payment')
            ->whereDoesntHave('payments', fn ($q) => $q->whereIn('status', [PaymentStatus::COMPLETED, PaymentStatus::PROVISION]))
            ->latest('created_at');

        // $priceTable = (new Price)->getTable();
        // $morphClass = addslashes($this->getMorphClass());

        // return $this->morphOne(Price::class, 'priceable')
        //     // ->hasPayment(false)
        //     ->hasPayment(false)
        //     ->orWhereHas('payments', fn ($q) => $q->where('status', '!=', PaymentStatus::COMPLETED))
        //     ->whereRaw("{$priceTable}.created_at = (select max(created_at) from {$priceTable} where {$priceTable}.priceable_id = '{$this->id}' and {$priceTable}.priceable_type = '{$morphClass}' and {$priceTable}.role = 'payment')");
    }

    public function paidPrices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable')
            ->where('role', 'payment')
            ->hasPayment(true, PaymentStatus::COMPLETED);
    }

    public function providedPrices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable')
            ->where('role', 'payment')
            ->hasPayment(true, PaymentStatus::PROVISION);
    }

    public function refundedPrices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable')
            ->where('role', 'payment')
            ->hasPayment(true, PaymentStatus::REFUNDED);
    }

    public function payment(): HasOneThrough
    {
        $priceTable = (new Price)->getTable();
        $paymentTable = (new Payment)->getTable();
        $morphClass = $this->getMorphClass();

        return $this->hasOneThrough(
            Payment::class,
            Price::class,
            'priceable_id',   // Foreign key on Price table
            'price_id',       // Foreign key on Payment table
            'id',             // Local key on this model
            'id'              // Local key on Price model
        )->where("{$priceTable}.priceable_type", $morphClass)
            ->where("{$priceTable}.role", 'payment')
            ->latest("{$paymentTable}.created_at");
    }

    public function payments(): HasManyThrough
    {
        $priceTable = (new Price)->getTable();
        $morphClass = $this->getMorphClass();

        return $this->hasManyThrough(
            Payment::class,
            Price::class,
            'priceable_id',   // Foreign key on Price table
            'price_id',       // Foreign key on Payment table
            'id',             // Local key on this model
            'id'              // Local key on Price model
        )->where("{$priceTable}.priceable_type", $morphClass)
            ->where("{$priceTable}.role", 'payment');
    }

    protected function totalCostExcludingVat(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->paymentPrices->sum('raw_amount')
        );
    }

    protected function totalCostIncludingVat(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->paymentPrices->sum('total_amount')
        );
    }

    protected function totalCostExcludingVatFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->totalCostExcludingVat
                ? PriceService::formatAmount($this->totalCostExcludingVat, new Currency($this->paymentPrice->currency_iso_4217))
                : null
        );
    }

    protected function totalCostIncludingVatFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->totalCostIncludingVat
                ? PriceService::formatAmount($this->totalCostIncludingVat, new Currency($this->paymentPrice->currency_iso_4217))
                : null
        );
    }

    protected function initialPriceExcludingVat(): Attribute
    {

        return Attribute::make(
            get: function ($value) {
                $price = 0;

                if ($this->initialPayablePrice) {
                    $price = $this->initialPayablePrice->raw_amount;
                }

                return $price;
            }
        );
    }

    protected function initialPriceExcludingVatFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => PriceService::formatAmount($this->initialPriceExcludingVat, new Currency($this->initialPayablePrice->currency_iso_4217))
        );
    }

    protected function payablePriceExcludingVat(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->payablePrice ? $this->payablePrice->price_excluding_vat : null,
        );
    }

    protected function payablePriceExcludingVatFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => isset($this->payablePriceExcludingVat)
                ? PriceService::formatAmount($this->payablePriceExcludingVat, new Currency($this->payablePrice->currency_iso_4217)) . ' +' . __('VAT')
                : null,
        );
    }

    protected function payablePriceIncludingVat(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->payablePrice ? $this->payablePrice->price_including_vat : null,
        );
    }

    protected function payablePriceIncludingVatFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => isset($this->payablePriceIncludingVat)
                ? PriceService::formatAmount($this->payablePriceIncludingVat, new Currency($this->payablePrice->currency_iso_4217))
                : null,
        );
    }

    protected function isPaid(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                return $value ?? $this->paid_prices_exists ?? $this->paidPrices()->exists();
            }
        );
    }

    protected function isUnpaid(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? $this->payable_price_exists ?? $this->payablePrice()->exists(),
        );
    }

    protected function isProvided(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? $this->provided_prices_exists ?? $this->providedPrices()->exists(),
        );
    }

    protected function isPartiallyPaid(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->is_paid && $this->is_unpaid,
        );
    }

    protected function isRefunded(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? $this->refunded_prices_exists ?? $this->refundedPrices()->exists(),
        );
    }

    protected function paymentStatusFormatted(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $color = 'grey';
                $label = '';
                switch (true) {
                    case $this->is_refunded:
                        $color = 'error';
                        $label = __(PaymentStatus::REFUNDED->label());

                        break;
                    case $this->is_provided:
                        $color = 'info';
                        $label = __(PaymentStatus::PROVISION->label());

                        break;
                    case $this->is_paid:
                        $color = 'success';
                        $label = __('Paid');

                        break;
                    case $this->is_partially_paid:
                        $color = 'warning';
                        $label = __('Partially Paid');

                        break;
                    case $this->is_unpaid:
                        $color = 'error';
                        $label = __('Unpaid');

                        break;
                    default:
                        $label = __('Not Ready');

                        break;
                }

                return '<v-chip color="' . $color . '">' . $label . '</v-chip>';
            },
        );
    }

    // protected function paymentStatus(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn ($value) => match(true) {
    //             !$this->paidPrices()->exists() => __('Unpaid'),
    //             $this->payablePrice?->price_including_vat > 0 => __('Partially Paid'),
    //             default => __('Paid')
    //         },
    //     );
    // }

    final public function getPaymentRelations(): array
    {
        return $this->hasPaymentRelations
                ? (is_string($this->hasPaymentRelations) ? [$this->hasPaymentRelations] : $this->hasPaymentRelations)
                : [];
    }
}
