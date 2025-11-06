<?php

namespace Unusualify\Modularity\Tests\Repositories\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Mockery as m;
use Unusualify\Modularity\Facades\CurrencyExchange;
use Unusualify\Modularity\Tests\Repositories\RepositorySources;
use Unusualify\Modularity\Tests\RepositoryTestCase;

class PricesTraitTest extends RepositoryTestCase
{
    use RefreshDatabase, RepositorySources;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();

        $this->repository = App::make(PricesTraitTestRepository::class);
    }

    public function test_set_columns_prices_trait_collects_price_inputs(): void
    {
        $columns = $this->repository->setColumnsPricesTrait([], [
            ['name' => 'prices', 'type' => 'price'],
            ['name' => 'title', 'type' => 'text'],
        ]);

        $this->assertSame(['prices'], $columns['PricesTrait']);
    }

    // public function test_after_save_prices_trait_with_should_ignore_field_before_save(): void
    // {
    //     $this->repository->addIgnoreFieldsBeforeSave(['prices']);

    //     $object = $this->repository->create([
    //         'name' => 'Test Priceable',
    //         'prices' => [
    //             [
    //                 'price_value' => 100,
    //                 'currency_id' => 1,
    //             ]
    //         ]
    //     ]);
    //     $this->assertCount(0, $object->prices);
    // }

    public function test_after_save_prices_trait_creates_price_records_for_role(): void
    {
        CurrencyExchange::shouldReceive('convertTo')->andReturn(114);

        $object = $this->repository->create([
            'name' => 'Test Priceable',
            'prices' => [
                [
                    'price_value' => 100,
                    'currency_id' => 1,
                ]
            ]
        ], [
            'prices' => [
                'type' => 'price',
                'name' => 'prices',
            ]
        ]);

        $this->assertCount(2, $object->prices);
        $this->assertNotEmpty($object->prices->where('currency_id', 1)->first());
        $this->assertNotEmpty($object->prices->where('currency_id', 2)->first());

        $this->assertEquals(10000, $object->prices->where('currency_id', 1)->first()->raw_amount);
        $this->assertEquals(100, $object->prices->where('currency_id', 1)->first()->price_value);
        $this->assertEquals(11400, $object->prices->where('currency_id', 2)->first()->raw_amount);
        $this->assertEquals(114, $object->prices->where('currency_id', 2)->first()->price_value);


        $this->assertNotEmpty($object->basePrice);
        $this->assertEquals(10000, $object->basePrice->raw_amount);
        $this->assertEquals(100, $object->basePrice->price_value);

        CurrencyExchange::shouldReceive('convertTo')->andReturn(114);
        $this->repository->update($object->id, [
            'prices' => [
                [
                    'id' => 1,
                    'price_value' => 200,
                    'currency_id' => 1,
                ]
            ]
        ], [
            'prices' => [
                'type' => 'price',
                'name' => 'prices',
            ]
        ]);

        $object->load('prices');
        $object->load('basePrice');


        $this->assertCount(2, $object->prices);
        $this->assertEquals(20000, $object->prices->where('currency_id', 1)->first()->raw_amount);
        $this->assertEquals(200, $object->prices->where('currency_id', 1)->first()->price_value);
        $this->assertEquals(11400, $object->prices->where('currency_id', 2)->first()->raw_amount);
        $this->assertEquals(114, $object->prices->where('currency_id', 2)->first()->price_value);

        $this->assertEquals(20000, $object->basePrice->raw_amount);
        $this->assertEquals(200, $object->basePrice->price_value);
    }

    public function test_get_form_fields_prices_trait_with_default_prices(): void
    {
        CurrencyExchange::shouldReceive('convertTo')->andReturn(114, 470);
        $schema = [
            'prices' => [
                'type' => 'price',
                'name' => 'prices',
            ]
        ];
        $object = $this->repository->create([
            'name' => 'Test Priceable',
        ], $schema);

        $formFields = $this->repository->getFormFields($object, $schema);

        $this->assertArrayHasKey('prices', $formFields);
        $this->assertCount(1, $formFields['prices']);
        $this->assertArrayNotHasKey('id', $formFields['prices'][0]);
        $this->assertEquals(1, $formFields['prices'][0]['vat_rate_id']);
        $this->assertEquals(1, $formFields['prices'][0]['currency_id']);
        $this->assertEquals(1, $formFields['prices'][0]['price_type_id']);
        $this->assertEquals(0.0, $formFields['prices'][0]['price_value']);
        $this->assertEquals(0.0, $formFields['prices'][0]['raw_amount']);

        $this->repository->update($object->id, [
            'prices' => [
                [
                    'price_value' => 100,
                    'currency_id' => 1,
                ]
            ]
        ], $schema);
        $formFields = $this->repository->getFormFields($object, $schema);

        $this->assertArrayHasKey('prices', $formFields);
        $this->assertCount(1, $formFields['prices']);
        $this->assertArrayHasKey('id', $formFields['prices'][0]);
        $this->assertEquals(1, $formFields['prices'][0]['vat_rate_id']);
        $this->assertEquals(1, $formFields['prices'][0]['currency_id']);
        $this->assertEquals(1, $formFields['prices'][0]['price_type_id']);
        $this->assertEquals(100.0, $formFields['prices'][0]['price_value']);
        $this->assertEquals(10000.0, $formFields['prices'][0]['raw_amount']);
    }

    public function test_get_form_fields_prices_trait_with_deleting_prices(): void
    {
        config(['modularity.services.currency_exchange.active' => false]);
        $object = $this->repository->create([
            'name' => 'Test Priceable',
            'prices' => [
                [
                    'price_value' => 100,
                    'currency_id' => 1,
                ]
            ]
        ], [
            'prices' => [
                'type' => 'price',
                'name' => 'prices',
            ]
        ]);

        $this->repository->update($object->id, [
            'prices' => [
                [
                    'price_value' => 200,
                    'currency_id' => 1,
                ]
            ]
        ], [
            'prices' => [
                'type' => 'price',
                'name' => 'prices',
            ]
        ]);

        $object->load('prices');

        $this->assertCount(1, $object->prices);
        $this->assertEquals(2, $object->prices->where('currency_id', 1)->first()->id);
        $this->assertEquals(20000, $object->prices->where('currency_id', 1)->first()->raw_amount);
        $this->assertEquals(200, $object->prices->where('currency_id', 1)->first()->price_value);
    }
}

class HasPriceableTestModel extends \Unusualify\Modularity\Tests\Repositories\TestModel
{
    use \Unusualify\Modularity\Entities\Traits\HasPriceable;

    public static $priceSavingKey = 'price_value';

}

class PricesTraitTestRepository extends \Unusualify\Modularity\Tests\Repositories\TestRepository
{
    use \Unusualify\Modularity\Repositories\Traits\PricesTrait;

    public function __construct(HasPriceableTestModel $model)
    {
        $this->model = $model;
    }
}



