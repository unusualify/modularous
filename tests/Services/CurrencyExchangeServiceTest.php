<?php

namespace Unusualify\Modularity\Tests\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Unusualify\Modularity\Services\CurrencyExchangeService;
use Unusualify\Modularity\Tests\TestCase;

class CurrencyExchangeServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('modularity.services.currency_exchange.endpoint', 'http://api.test/latest');
        Config::set('modularity.services.currency_exchange.api_key', 'test-key');
        Config::set('modularity.services.currency_exchange.parameters', ['apiKey' => 'apikey', 'baseCurrency' => 'base_currency']);
        Config::set('modularity.services.currency_exchange.rates_key', 'data');

        $this->service = new CurrencyExchangeService;
    }

    /** @test */
    public function it_can_fetch_and_cache_rates()
    {
        Http::fake([
            'api.test/*' => Http::response(['data' => ['USD' => 1.1, 'EUR' => 1.0]], 200),
        ]);

        $rates = $this->service->fetchExchangeRates();

        $this->assertEquals(1.1, $rates['USD']);
        $this->assertTrue(Cache::has('exchange_rates'));
    }

    /** @test */
    public function it_can_convert_amount()
    {
        Cache::put('exchange_rates', ['USD' => 1.1, 'EUR' => 1.0], 3600);

        $converted = $this->service->convertTo(100, 'USD');
        $this->assertEquals(110.0, $converted);
    }

    /** @test */
    public function it_throws_exception_for_unsupported_currency()
    {
        Cache::put('exchange_rates', ['USD' => 1.1], 3600);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unsupported currency: EUR');

        $this->service->convertTo(100, 'EUR');
    }

    /** @test */
    public function it_can_get_exchange_rate()
    {
        Cache::put('exchange_rates', ['USD' => 1.1], 3600);

        $rate = $this->service->getExchangeRate('USD');
        $this->assertEquals(1.1, $rate);
    }
}
