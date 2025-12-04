<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\SystemPayment\Entities\PaymentService;
use Modules\SystemPricing\Entities\Currency;
use Modules\SystemPricing\Entities\Price;
use Modules\SystemPricing\Entities\PriceType;
use Modules\SystemPricing\Entities\VatRate;
use Unusualify\Modularity\Entities\Enums\PaymentStatus;

trait PaymentTrait
{
    use PricesTrait;

    /**
     * paymentTraitRelationName
     *
     * @var undefined
     */
    public $paymentTraitRelationName = null;

    /**
     * paymentTraitDefaultCurrencyId
     *
     * @var int
     */
    public $paymentTraitDefaultCurrencyId = 1;

    /**
     * requiredTrait
     *
     * @var string
     */
    protected $requiredTrait = 'Unusualify\Modularity\Entities\Traits\HasPriceable';

    /**
     * snapshotTrait
     *
     * @var string
     */
    protected $snapshotTrait = 'Oobook\Snapshot\Traits\HasSnapshot';

    /**
     * @param \Unusualify\Modularity\Models\Model $object
     * @param array $fields
     * @return void
     */
    protected function afterSavePaymentTrait($object, $fields)
    {
        $priceSavingKey = Price::$priceSavingKey ?? 'price_value';

        $paymentPrice = $object->paymentPrice()->first();
        $forcePaymentUpdate = isset($fields['force_payment_update']) && $fields['force_payment_update'];

        if (isset($fields['payment_price'])) {
            $val = Arr::isAssoc($fields['payment_price']) ? $fields['payment_price'] : $fields['payment_price'][0];

            $price = Price::find($val['id']);

            if ($price->isUnpaid) {
                // Update existing unpaid record
                $price->update(Arr::only($val, [
                    'price_type_id',
                    'vat_rate_id',
                    'currency_id',
                    'role',
                    'valid_from',
                    'valid_till',
                    'discount_percentage',
                    $priceSavingKey,
                ]));
            } else {
                // Create new record with previous data for paid records
                $newPrice = $price->replicate();
                $newPrice->fill(Arr::only($val, [
                    'price_type_id',
                    'vat_rate_id',
                    'currency_id',
                    'role',
                    'valid_from',
                    'valid_till',
                    'discount_percentage',

                    $priceSavingKey,
                ]));

                $newPrice->save();
            }
        } elseif (! $paymentPrice || $forcePaymentUpdate) {
            $session_currency = request()->getUserCurrency()->id;
            // dd($fields);
            $currencyId = isset($fields['currency_id'])
                ? $fields['currency_id']
                : $session_currency ?? $this->paymentTraitDefaultCurrencyId;

            $paymentRelations = $this->model->getPaymentRelations();

            if (count($paymentRelations) > 0) {

                $totalAmount = 0;
                $calculated = false;

                foreach ($paymentRelations as $relationName) {

                    $relatedClass = $object->{$relationName}()->getRelated();

                    $requirementMet = false;

                    if (classHasTrait($relatedClass, $this->requiredTrait)) {
                        $requirementMet = true;
                    } elseif (classHasTrait($relatedClass, $this->snapshotTrait)
                        && classHasTrait($relatedClass->source()->getRelated(), $this->requiredTrait)
                    ) {
                        $requirementMet = true;
                    }

                    if ($requirementMet) {
                        $records = $object->{$relationName}()->get();
                        if ($records instanceof \Illuminate\Database\Eloquent\Collection) {

                            foreach ($records as $record) {
                                $price = $record->originalBasePrice;

                                if (! is_null($price)) {
                                    $calculated = true;
                                    $totalAmount += $price->raw_amount;
                                }
                            }
                        }
                    }
                }

                if (! $paymentPrice && $calculated) {

                    $object->paymentPrice()->create([
                        'price_type_id' => 1,
                        'vat_rate_id' => 1,
                        'currency_id' => $currencyId,
                        ...($this->defaultPaymentPriceFields()),
                        $priceSavingKey => ($totalAmount / 100),
                        'role' => 'payment',
                    ]);
                } elseif ($paymentPrice && $paymentPrice->raw_amount != $totalAmount) {
                    $paymentPrice->{$priceSavingKey} = $totalAmount / 100;
                    // $paymentPrice->currency_id = $currencyId;
                    $paymentPrice->save();
                } else {
                    // dd($calculated, $totalAmount, $object->paymentPrice->raw_amount, $object->paymentPrice);
                }
            }
        }

        // in order to create a payment record, we need to have a payment service
        if (isset($fields['payment_service_id'])) {
            $paymentService = PaymentService::find($fields['payment_service_id']);
            $paymentPrice = $object->paymentPrice()->first();

            $paymentPricePayload = [
                ...(isset($fields['price_vat_rate_id']) ? ['vat_rate_id' => $fields['price_vat_rate_id']] : []),
                ...(isset($fields['price_discount_percentage']) ? ['discount_percentage' => $fields['price_discount_percentage']] : []),
            ];

            if (! empty($paymentPricePayload)) {
                $paymentPrice->update($paymentPricePayload);
                $paymentPrice->refresh();
            }

            if ($paymentService->transferrable && $paymentPrice) {
                $paymentPayload = [
                    'payment_service_id' => $paymentService->id,
                    'email' => $fields['email'] ?? null,
                ];
                $extraPayload = [];
                $user = null;

                if (Auth::check()) {
                    $user = Auth::user();
                }

                if (classHasTrait($object, 'Unusualify\Modularity\Entities\Traits\HasCreator')) {
                    $paymentPayload['custom_creator_id'] = $object->creator->id;
                    $paymentPayload['email'] = $object->creator->email;
                } elseif ($user) {
                    $paymentPayload['email'] = $user->email;
                }

                if (isset($fields['payment_receipts'])) {
                    $extraPayload['receipts'] = $fields['payment_receipts'];
                }

                if (isset($fields['payment_description'])) {
                    $paymentPayload['spread_payload']['description'] = $fields['payment_description'];
                }

                if (isset($fields['payment_status'])) {
                    $paymentPayload['status'] = $fields['payment_status'];
                }

                if (isset($fields['payment_currency_id'])) {
                    $paymentPayload['currency_id'] = $fields['payment_currency_id'];
                }

                $paymentPrice->updateOrNewPayment($paymentPayload, $extraPayload);
            }
        }
    }

    public function getFormFieldsPaymentTrait($object, $fields)
    {
        if (method_exists($object, 'paymentPrice') && $object->paymentPrice()->exists() && $object->payment()->exists()) {
            // $priceSavingKey = Price::$priceSavingKey;
            // $query = $object->paymentPrice;
            $fields['payment'] = $object->payment;
        }

        return $fields;
    }

    /**
     * Form Schema to use on vue frontend side
     *
     * @return void
     */
    public function getPaymentFormSchema()
    {
        return [
            [
                'type' => 'hidden',
                'name' => 'price_id',
                'label' => 'price_id',
            ],
            [
                'type' => 'payment-service',
                'name' => 'payment_service',
                'label' => 'Payment',
                // 'connector' => 'SystemPayment:PaymentService|repository:listAll',
            ],
        ];
    }

    public function getFormActionsConditionsForPayment(): array
    {
        return method_exists($this->model, 'getFormActionsConditionsForPayment')
            ? $this->model->getFormActionsConditionsForPayment()
            : [];
    }

    public function getFormActionPropsForPaymentTrait(): array
    {
        return method_exists($this->model, 'getFormActionPropsForPaymentTrait')
            ? $this->model->getFormActionPropsForPaymentTrait()
            : [];
    }

    public function getFormActionsPaymentTrait($scope = [])
    {
        return [
            'paymentTrait' => [
                'type' => 'modal',
                'label' => __('Pay'),
                'icon' => 'mdi-credit-card-outline',
                'tooltip' => __('Pay'),
                'color' => 'success',
                'density' => 'compact',
                'endpoint' => Route::has('admin.system.system_payment.pay') ? route('admin.system.system_payment.pay') : null,
                'schema' => modularity_format_inputs([
                    [
                        'type' => 'hidden',
                        'name' => 'price_id',
                        'label' => 'price_id',
                        'default' => '${payment_price.id}$',
                    ],
                    [
                        'type' => 'payment-service',
                        'name' => 'payment_service',
                        'label' => 'Payment',
                        'price_object' => '${payment_price}$',
                    ],
                ]),
                // 'formTitle' => __('Complete Payment'),
                'formAttributes' => [
                    'hasSubmit' => false,
                    'hasDivider' => false,
                    'refreshOnSaved' => true,
                    'async' => false,
                    'noSchemaUpdatingProgressBar' => true,
                ],
                'creatable' => false,
                'isEditing' => false,
                'modalAttributes' => [
                    'title' => __('Complete Payment'),
                    'widthType' => 'lg',
                    'persistent' => true,
                ],
                'conditions' => array_merge($this->getFormActionsConditionsForPayment(), [
                    ['payment.status', 'not in', [PaymentStatus::COMPLETED]],
                ]),
                'hideOnCondition' => true,
                ...$this->getFormActionPropsForPaymentTrait(),
            ],
        ];
    }

    protected function defaultPaymentPriceFields(): array
    {
        $fields = method_exists($this, 'getDefaultPaymentPriceFields') ? $this->getDefaultPaymentPriceFields() : [];

        if (! empty($fields)) {
            if (isset($fields['price_type_id'])) {
                $priceTypeId = PriceType::where('id', $fields['price_type_id'])->orWhere('name', $fields['price_type_id'])->value('id');
                if (! $priceTypeId) {
                    unset($fields['price_type_id']);
                } else {
                    $fields['price_type_id'] = $priceTypeId;
                }
            }

            if (isset($fields['vat_rate_id'])) {
                $vatRateId = VatRate::where('id', $fields['vat_rate_id'])->orWhere('name', $fields['vat_rate_id'])->orWhere('slug', $fields['vat_rate_id'])->value('id');

                if (! $vatRateId) {
                    unset($fields['vat_rate_id']);
                } else {
                    $fields['vat_rate_id'] = $vatRateId;
                }
            }

            if (isset($fields['currency_id'])) {
                $currencyId = Currency::where('id', $fields['currency_id'])->orWhere('name', $fields['currency_id'])->orWhere('symbol', $fields['currency_id'])->orWhere('iso_4217', $fields['currency_id'])->orWhere('iso_4217_number', $fields['currency_id'])->value('id');
                if (! $currencyId) {
                    unset($fields['currency_id']);
                } else {
                    $fields['currency_id'] = $currencyId;
                }
            }
        }

        return $fields;
    }

    public function getDefaultPaymentPriceFields(): array
    {
        return [];
    }
}
