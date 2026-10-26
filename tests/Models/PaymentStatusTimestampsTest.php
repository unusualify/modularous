<?php

namespace Unusualify\Modularous\Tests\Models;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Modules\SystemPayment\Entities\Payment;
use Modules\SystemPayment\Entities\PaymentService;
use Modules\SystemPricing\Entities\Price;
use Oobook\Priceable\Models\Currency;
use Oobook\Priceable\Models\PriceType;
use Oobook\Priceable\Models\VatRate;
use Unusualify\Modularous\Entities\Enums\PaymentStatus;
use Unusualify\Modularous\Tests\ModelTestCase;

class PaymentStatusTimestampsTest extends ModelTestCase
{
    use RefreshDatabase;

    protected Currency $currency;

    protected Price $price;

    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currency = Currency::create(['name' => 'US Dollar', 'iso_4217' => 'USD', 'symbol' => '$']);
        $vatRate = VatRate::create(['name' => 'Standard VAT', 'slug' => 'standard-vat', 'rate' => 20.0]);
        $priceType = PriceType::create(['name' => 'Standard Price Type']);

        if (! Schema::hasTable('payment_services')) {
            Schema::create('payment_services', function (Blueprint $table) {
                createDefaultTableFields($table);
                $table->string('name')->unique();
                $table->string('key')->unique();
                $table->boolean('is_external')->default(false);
                $table->boolean('is_internal')->default(false);
                $table->string('button_style')->nullable();
                createDefaultExtraTableFields($table);
            });
        }

        $this->paymentService = PaymentService::firstOrCreate(
            ['key' => 'bank-transfer'],
            ['name' => 'Bank Transfer', 'is_external' => false, 'is_internal' => true]
        );

        $this->price = Price::create([
            'priceable_id' => 1,
            'priceable_type' => 'TestPriceable',
            'role' => 'payment',
            'raw_amount' => 10000,
            'price_including_vat' => 12000,
            'currency_id' => $this->currency->id,
            'vat_rate_id' => $vatRate->id,
            'price_type_id' => $priceType->id,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    protected function createPayment(PaymentStatus $status): Payment
    {
        return Payment::create([
            'price_id' => $this->price->id,
            'payment_service_id' => $this->paymentService->id,
            'payment_gateway' => 'test',
            'order_id' => 'ORDER-' . uniqid(),
            'amount' => 12000,
            'currency' => $this->currency->iso_4217,
            'currency_id' => $this->currency->id,
            'status' => $status,
            'email' => 'test@example.com',
        ]);
    }

    public function test_pending_and_failed_attempts_do_not_stamp_any_status_timestamp()
    {
        $payment = $this->createPayment(PaymentStatus::PENDING);
        $payment->update(['status' => PaymentStatus::FAILED]);

        $payment->refresh();
        $this->assertNull($payment->provisioned_at);
        $this->assertNull($payment->paid_at);
        $this->assertNull($payment->refunded_at);
        $this->assertNull($payment->cancelled_at);
    }

    public function test_completing_a_retried_payment_stamps_paid_at_at_completion_time()
    {
        Carbon::setTestNow('2026-10-01 10:00:00');
        $payment = $this->createPayment(PaymentStatus::FAILED);

        Carbon::setTestNow('2026-10-05 14:30:00');
        $payment->update(['status' => PaymentStatus::COMPLETED]);

        $payment->refresh();
        $this->assertSame('2026-10-01 10:00:00', $payment->created_at->toDateTimeString());
        $this->assertSame('2026-10-05 14:30:00', $payment->paid_at->toDateTimeString());
        $this->assertSame('2026-10-05 14:30:00', $payment->paid_date->toDateTimeString());
    }

    public function test_bank_transfer_keeps_provision_and_approval_moments_separately()
    {
        Carbon::setTestNow('2026-10-01 09:00:00');
        $payment = $this->createPayment(PaymentStatus::PROVISION);

        Carbon::setTestNow('2026-10-03 16:00:00');
        $payment->update(['status' => PaymentStatus::COMPLETED]);

        $payment->refresh();
        $this->assertSame('2026-10-01 09:00:00', $payment->provisioned_at->toDateTimeString());
        $this->assertSame('2026-10-03 16:00:00', $payment->paid_at->toDateTimeString());
    }

    public function test_refund_after_payment_does_not_move_paid_at()
    {
        Carbon::setTestNow('2026-10-01 09:00:00');
        $payment = $this->createPayment(PaymentStatus::COMPLETED);

        Carbon::setTestNow('2026-10-10 12:00:00');
        $payment->update(['status' => PaymentStatus::REFUNDED]);

        $payment->refresh();
        $this->assertSame('2026-10-01 09:00:00', $payment->paid_at->toDateTimeString());
        $this->assertSame('2026-10-10 12:00:00', $payment->refunded_at->toDateTimeString());
        $this->assertSame('2026-10-10 12:00:00', $payment->updated_at->toDateTimeString());
    }

    public function test_cancellation_stamps_cancelled_at()
    {
        $payment = $this->createPayment(PaymentStatus::COMPLETED);
        $payment->update(['status' => PaymentStatus::CANCELLED]);

        $this->assertNotNull($payment->refresh()->cancelled_at);
    }

    public function test_existing_timestamp_is_not_overwritten_when_status_is_reached_again()
    {
        Carbon::setTestNow('2026-10-01 09:00:00');
        $payment = $this->createPayment(PaymentStatus::COMPLETED);

        Carbon::setTestNow('2026-10-02 09:00:00');
        $payment->update(['status' => PaymentStatus::FAILED]);
        Carbon::setTestNow('2026-10-03 09:00:00');
        $payment->update(['status' => PaymentStatus::COMPLETED]);

        $this->assertSame('2026-10-01 09:00:00', $payment->refresh()->paid_at->toDateTimeString());
    }

    public function test_legacy_payment_without_paid_at_falls_back_to_created_at()
    {
        Carbon::setTestNow('2026-01-15 08:00:00');
        $payment = Payment::withoutEvents(fn () => $this->createPayment(PaymentStatus::COMPLETED));

        $payment->refresh();
        $this->assertNull($payment->paid_at);
        $this->assertSame('2026-01-15 08:00:00', $payment->paid_date->toDateTimeString());
        $this->assertContains('paid_date', $payment->getAppends());
    }
}
