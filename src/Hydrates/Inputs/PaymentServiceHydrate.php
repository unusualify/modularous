<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Modules\SystemPayment\Entities\PaymentCurrency;
use Modules\SystemPayment\Entities\PaymentService;
use Unusualify\Modularous\Facades\Modularous;

class PaymentServiceHydrate extends InputHydrate
{
    /**
     * Request attribute holding the payment records resolved for the current request.
     */
    protected const RECORDS_ATTRIBUTE = '_modularous_payment_service_records';

    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'itemValue' => 'id',
        'itemTitle' => 'name',
        'default' => [],
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;
        $input['type'] = 'input-payment-service';

        $input['default_payment_service'] = config('modularous.default_payment_service');
        $input['currencyConversionEndpoint'] = route('currency.convert');

        $input['useCountryBasedVatRates'] = Modularous::shouldUseCountryBasedVatRates();

        $input = array_merge($input, $this->skipQueries
            ? ['supportedCurrencies' => [], 'items' => [], 'currencyCardTypes' => []]
            : $this->resolvePaymentRecords($input['useCountryBasedVatRates']));

        $input['transferFormSchema'] = modularous_format_inputs([
            [
                'type' => 'hidden',
                'name' => 'price_id',
            ],
            [
                'type' => 'hidden',
                'name' => 'payment_service_id',
            ],
            [
                'type' => 'hidden',
                'name' => 'currency_id',
            ],
            [
                'type' => 'filepond',
                'name' => 'bank_receipt',
                'label' => __('Upload Transfer Receipt'),
                'col' => ['cols' => 12],
                'acceptedExtensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max-files' => 2,
                'min' => 1,
                'default' => [],
            ],
            [
                'type' => 'checkbox',
                'name' => 'tos',
                'col' => ['cols' => 12],
                'label' => __('I have made the transfer'),
                'rules' => 'required',
            ],
        ]);

        $input['includeTransactionFee'] = Modularous::shouldIncludeTransactionFee();

        $input['paymentUrl'] = Route::has('admin.system.system_payment.pay')
            ? route('admin.system.system_payment.pay')
            : null;
        $input['checkoutUrl'] = Route::has('admin.system.system_payment.checkout')
            ? route('admin.system.system_payment.checkout')
            : null;
        $input['completeUrl'] = Route::has('admin.system.system_payment.payment.response')
            ? route('admin.system.system_payment.payment.response')
            : null;

        return $input;
    }

    /**
     * Resolve supported currencies, payment services and card types once per request;
     * a single page can render this input several times (row action, form action, form).
     *
     * @return array{supportedCurrencies: mixed, items: array, currencyCardTypes: array}
     */
    protected function resolvePaymentRecords(bool $useCountryBasedVatRates): array
    {
        $attributes = Request::instance()->attributes;
        $key = self::RECORDS_ATTRIBUTE . ($useCountryBasedVatRates ? ':country' : ':default');

        if (! $attributes->has($key)) {
            $attributes->set($key, [
                'supportedCurrencies' => $this->getSupportedCurrencies($useCountryBasedVatRates),
                'items' => PaymentService::published()
                    ->isExternal()
                    ->orWhere(fn ($query) => $query->published()->isTransfer())
                    ->with('paymentCurrencies')
                    ->get()
                    ->toArray(),
                'currencyCardTypes' => $this->getCurrencyCardTypes(),
            ]);
        }

        return $attributes->get($key);
    }

    protected function getSupportedCurrencies(bool $useCountryBasedVatRates)
    {
        if (! $useCountryBasedVatRates) {
            return PaymentCurrency::whereHas('paymentServices')
                ->orWhereHas('paymentService')
                ->with('paymentServices', 'paymentService')
                ->get()
                ->each(function ($item) {
                    $item->append('has_built_in_form');
                });
        }

        $userPaymentCountryCurrencies = get_user_payment_country_currencies();

        $userPaymentCountryCurrencies = $userPaymentCountryCurrencies->filter(function ($item) {
            return $item->paymentServices()->exists() || $item->paymentService()->exists();
        })->each(function ($item) {
            $item->load('paymentServices', 'paymentService');
        });

        // $defaultPaymentCurrencies = PaymentCurrency::whereNotIn('id', $userPaymentCountryCurrencies->pluck('id'))->get();
        $query = PaymentCurrency::whereHas('paymentServices')
            ->orWhereHas('paymentService')
            ->whereNotIn('id', $userPaymentCountryCurrencies->pluck('id'))
            ->with('paymentServices', 'paymentService');

        if (Auth::guard('modularous')->check() && ($user = Auth::guard('modularous')->user()) && $user->is_client && ($user->validCompany)) {
            if ($user->company->isCorporateCompany) {
                $query = $query->defaultCorporatePaymentCurrency();
            } else {
                $query = $query->defaultPersonalPaymentCurrency();
            }
        } else {
            $query = PaymentCurrency::whereHas('paymentServices')
                ->orWhereHas('paymentService')
                ->with('paymentServices', 'paymentService');
        }

        $defaultPaymentCurrencies = $query->get();

        return $defaultPaymentCurrencies->merge($userPaymentCountryCurrencies)->sortBy('id')->values()->map(function ($item) {
            $item->append('has_built_in_form');
            $item->setCompanyVatRate();

            return $item;
        });
    }

    protected function getCurrencyCardTypes(): array
    {
        $paymentServices = PaymentService::published()
            ->where('is_internal', 1)
            ->with(['paymentCurrencies', 'cardTypes'])
            ->get()
            ->all();

        $mappedData = [];

        foreach ($paymentServices as $paymentService) {
            foreach ($paymentService->internalPaymentCurrencies as $internalPaymentCurrency) {
                $currencyCode = $internalPaymentCurrency->iso_4217 ?? '';

                if (! isset($mappedData[$currencyCode])) {
                    $mappedData[$currencyCode] = [];
                }

                foreach ($paymentService->cardTypes as $cardType) {
                    $cardInfo = [
                        'name' => mb_strtolower($cardType->name ?? ''),
                        'logo' => $cardType->image('logo', locale: 'en'),
                    ];

                    if ($cardInfo['name'] && ! $this->cardExists($mappedData[$currencyCode], $cardInfo['name'])) {
                        $mappedData[$currencyCode][] = $cardInfo;
                    }
                }
            }
        }

        return $mappedData;
    }

    private function cardExists($currencyCards, $cardName)
    {
        return collect($currencyCards)->contains(function ($card) use ($cardName) {
            return $card['name'] === $cardName;
        });

    }
}
