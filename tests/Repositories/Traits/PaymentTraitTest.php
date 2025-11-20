<?php

namespace Unusualify\Modularity\Tests\Repositories\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Modules\SystemUser\Entities\Role;
use Unusualify\Modularity\Entities\TemporaryFilepond;
use Unusualify\Modularity\Tests\Repositories\RepositorySources;
use Unusualify\Modularity\Tests\RepositoryTestCase;

class PaymentTraitTest extends RepositoryTestCase
{
    use RefreshDatabase, RepositorySources;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();

        $this->repository = App::make(PaymentTraitTestRepository::class);

        Schema::create('items', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('posts', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('name');
            $table->string('content');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('copy_posts', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->foreignId('test_model_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('test_model_item', function (Blueprint $table) {
            createDefaultRelationshipTableFields($table, 'test_model', 'item');
        });

        Schema::create('payment_services', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('name')->unique();
            $table->string('key')->unique();

            $table->decimal('transaction_fee_percentage', 5, 2)->default(0.00);
            $table->boolean('is_external')->default(false);
            $table->boolean('is_internal')->default(false);
            $table->string('button_style')->nullable();
            createDefaultExtraTableFields($table);
        });

        Schema::create('payment_currency_payment_service', function (Blueprint $table) {
            createDefaultRelationshipTableFields($table, 'payment_currency', 'payment_service', config('priceable.tables.currencies', 'unfy_currencies'));
        });

        Schema::table(config('priceable.tables.currencies', 'unfy_currencies'), function (Blueprint $table) {
            $table->after('id', function () use ($table) {
                $table->bigInteger('payment_service_id')->nullable();
            });
        });

        $paymentCurrency = \Modules\SystemPayment\Entities\PaymentCurrency::updateOrCreate([
            'iso_4217' => 'EUR',
        ], [
            'name' => 'Euro',
            'symbol' => '€',
            'iso_4217_number' => 978,
        ]);

        $paymentService = \Modules\SystemPayment\Entities\PaymentService::create([
            'name' => 'Stripe',
            'key' => 'stripe',
            'published' => true,
            'transaction_fee_percentage' => 0.00,
            'is_external' => false,
            'is_internal' => false,
            'spread_payload' => [
                'type' => 2,
                'transfer_details' => [
                    'account_holder' => 'Test Account Holder',
                    'iban' => '',
                    'swift_code' => '',
                    'description' => '',
                    'address' => '',
                ],
            ],
        ]);

        $paymentService->paymentCurrencies()->attach($paymentCurrency->id);

    }

    public function test_get_payment_form_schema_structure(): void
    {
        $schema = $this->repository->getPaymentFormSchema();

        $this->assertIsArray($schema);
        $this->assertNotEmpty($schema);
        $this->assertSame('hidden', $schema[0]['type']);
        $this->assertSame('price_id', $schema[0]['name']);
        $this->assertSame('payment-service', $schema[1]['type']);
        $this->assertSame('payment_service', $schema[1]['name']);
    }

    public function test_get_form_actions_conditions_for_payment_delegates_to_model(): void
    {
        $conditions = $this->repository->getFormActionsConditionsForPayment();

        $this->assertIsArray($conditions);
        $this->assertSame('state.code', $conditions[0][0]);
        $this->assertSame('in', $conditions[0][1]);
        $this->assertContains('pending-payment', $conditions[0][2]);
    }

    public function test_get_form_action_props_for_payment_trait_delegates_to_model(): void
    {
        $props = $this->repository->getFormActionPropsForPaymentTrait();

        $this->assertIsArray($props);
        $this->assertArrayHasKey('allowedRoles', $props);
        $this->assertEquals(['client-manager', 'client-assistant'], $props['allowedRoles']);
    }

    public function test_get_table_row_props_for_payment_trait_delegates_to_model(): void
    {
        $props = $this->repository->getTableRowPropsForPayment();

        $this->assertIsArray($props);
        $this->assertArrayHasKey('allowedRoles', $props);
        $this->assertEquals(['admin', 'manager'], $props['allowedRoles']);
    }

    public function test_get_form_actions_payment_trait_delegates_to_model(): void
    {
        $actions = $this->repository->getFormActions();

        $this->assertIsArray($actions['paymentTrait']);
        $this->assertTrue(Arr::isAssoc($actions['paymentTrait']));
        $this->assertEquals('modal', $actions['paymentTrait']['type']);
        $this->assertEquals(__('Pay'), $actions['paymentTrait']['label']);
        $this->assertEquals(__('Pay'), $actions['paymentTrait']['tooltip']);
        $this->assertEquals('success', $actions['paymentTrait']['color']);
        $this->assertEquals('compact', $actions['paymentTrait']['density']);

        $this->assertArrayHasKey('schema', $actions['paymentTrait']);
        $this->assertEquals(2, count($actions['paymentTrait']['schema']));
        $this->assertEquals('hidden', $actions['paymentTrait']['schema']['price_id']['type']);
        $this->assertEquals('price_id', $actions['paymentTrait']['schema']['price_id']['name']);
        $this->assertEquals('input-payment-service', $actions['paymentTrait']['schema']['payment_service']['type']);
        $this->assertEquals('payment_service', $actions['paymentTrait']['schema']['payment_service']['name']);

        $this->assertArrayHasKey('formAttributes', $actions['paymentTrait']);
        $this->assertEquals(false, $actions['paymentTrait']['formAttributes']['hasSubmit']);
        $this->assertEquals(false, $actions['paymentTrait']['formAttributes']['hasDivider']);
        $this->assertEquals(true, $actions['paymentTrait']['formAttributes']['refreshOnSaved']);
        $this->assertEquals(false, $actions['paymentTrait']['formAttributes']['async']);
        $this->assertEquals(true, $actions['paymentTrait']['formAttributes']['noSchemaUpdatingProgressBar']);

        $this->assertArrayHasKey('creatable', $actions['paymentTrait']);
        $this->assertEquals(false, $actions['paymentTrait']['creatable']);

        $this->assertArrayHasKey('isEditing', $actions['paymentTrait']);
        $this->assertEquals(false, $actions['paymentTrait']['isEditing']);

        $this->assertArrayHasKey('modalAttributes', $actions['paymentTrait']);
        $this->assertEquals(__('Complete Payment'), $actions['paymentTrait']['modalAttributes']['title']);
        $this->assertEquals('lg', $actions['paymentTrait']['modalAttributes']['widthType']);
        $this->assertEquals(true, $actions['paymentTrait']['modalAttributes']['persistent']);

        $this->assertArrayHasKey('hideOnCondition', $actions['paymentTrait']);
        $this->assertEquals(true, $actions['paymentTrait']['hideOnCondition']);
    }

    public function test_get_form_fields_payment_trait_includes_payment_when_relationship_present(): void
    {
        $fields = [];
        $object = HasPaymentTestModel::create([
            'name' => 'Test Payment Item',
            'is_active' => true,
            'published' => true,
        ]);

        $object->paymentPrice()->create([
            'price_value' => 100,
            'price_type_id' => 1,
            'currency_id' => 1,
            'vat_rate_id' => 1,
            'role' => 'payment',
        ]);

        $price = $object->paymentPrice()->first();

        $orderId = uniqid('ORD');
        $price->payment()->create([
            'price_id' => $price->id,
            'payment_service_id' => 1,
            'currency' => $price->currency->iso_4217,
            'currency_id' => $price->currency->id,
            'order_id' => $orderId,

            'status' => \Unusualify\Modularity\Entities\Enums\PaymentStatus::COMPLETED,

            'amount' => 100,
            'modularity' => [
                'previous_url' => 'https://www.modularity.test',
                'datetime' => now()->format('Y-m-d H:i:s'),
                'original_raw_amount' => $price->discounted_raw_amount,
                'original_total_amount' => $price->total_amount,
                'converted_raw_amount' => $price->discounted_raw_amount,
                'converted_total_amount' => $price->total_amount,
                'vat_percentage' => $price->vat_percentage,
                'vat_multiplier' => $price->vat_multiplier,
                'discount_percentage' => $price->discount_percentage,
                'original_currency' => $price->currency->iso_4217,
                'original_currency_id' => $price->currency->id,
                'converted_currency' => $price->currency->iso_4217,
                'converted_currency_id' => $price->currency->id,
                'converted' => false,
                'exchange_rate' => 1,
                'basket_id' => $orderId,
                'items' => [
                    [
                        'id' => 1,
                        'name' => 'Test Payment Item',
                    ],
                ],
            ],
        ]);

        $mapped = $this->repository->getFormFieldsPaymentTrait($object, $fields);

        $this->assertArrayHasKey('payment', $mapped);
        $this->assertSame(\Unusualify\Modularity\Entities\Enums\PaymentStatus::COMPLETED, $mapped['payment']['status']);
    }

    public function test_after_save_payment_trait(): void
    {
        $user = \Unusualify\Modularity\Entities\User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'published' => true,
            'language' => 1,
        ]);
        $role = Role::firstOrCreate([
            'name' => 'admin',
        ], [
            'guard_name' => 'modularity',
        ]);
        $user->assignRole($role);

        $this->actingAs($user, 'modularity');

        $item = Item::create([
            'name' => 'Test Payment Item',
            'is_active' => true,
            'published' => true,
        ]);

        $item->prices()->create([
            'price_value' => 100,
            'price_type_id' => 1,
            'vat_rate_id' => 1,
            'currency_id' => 1,
        ]);

        $temporaryFile = TemporaryFilepond::create([
            'file_name' => 'test_receipt_1.pdf',
            'input_role' => 'receipts',
            'folder_name' => uniqid('', true),
        ]);

        $fields = [
            'name' => 'Test Payment Item',
            'is_active' => true,
            'published' => true,

            'payment_service_id' => 1,
            'price_vat_rate_id' => 1,
            'price_discount_percentage' => 10,
            'items' => [$item->id],

            'payment_description' => 'Test Payment Description',
            'payment_status' => \Unusualify\Modularity\Entities\Enums\PaymentStatus::PENDING,
            'payment_currency_id' => 1,
            'payment_receipts' => [
                [
                    'uuid' => $temporaryFile->folder_name,
                ],
            ],
        ];

        $object = $this->repository->create($fields);

        $paymentPrice = $object->paymentPrice;
        $payment = $object->payment;

        $this->assertNotEmpty($paymentPrice);
        $this->assertSame(100, $paymentPrice->price_value);
        $this->assertSame(10.0, $paymentPrice->discount_percentage);
        $this->assertSame(1, $paymentPrice->vat_rate_id);

        $this->assertNotEmpty($payment);
        $this->assertSame(\Unusualify\Modularity\Entities\Enums\PaymentStatus::PENDING, $payment->status);
        $this->assertSame('Test Payment Description', $payment->description);
        $this->assertSame(1, $payment->currency_id);

        sleep(1);

        $this->repository->update($object->id, [

            'payment_price' => [
                'id' => $paymentPrice->id,
                'price_value' => 500,
            ],
        ]);

        $object->load('paymentPrice');
        $this->assertNotEmpty($object->paymentPrice);
        $this->assertSame(500, $object->paymentPrice->price_value);

        $payment->update([
            'status' => \Unusualify\Modularity\Entities\Enums\PaymentStatus::COMPLETED,
        ]);

        sleep(1);

        $this->repository->update($object->id, [
            'payment_price' => [
                'id' => $paymentPrice->id,
                'price_value' => 400,
            ],
        ]);

        $object->load('paymentPrice');
        $this->assertNotEmpty($object->paymentPrice);
        $this->assertSame(400, $object->paymentPrice->price_value);
    }

    public function test_after_save_payment_trait_with_creator_trait(): void
    {
        $this->repository = App::makeWith(PaymentTraitTestRepository::class, ['model' => new HasCreatorTestModel]);
        $user = \Unusualify\Modularity\Entities\User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'published' => true,
            'language' => 1,
        ]);
        $role = Role::firstOrCreate([
            'name' => 'admin',
        ], [
            'guard_name' => 'modularity',
        ]);
        $user->assignRole($role);
        $this->actingAs($user, 'modularity');

        $item = Item::create([
            'name' => 'Test Payment Item',
            'is_active' => true,
            'published' => true,
        ]);

        $item->prices()->create([
            'price_value' => 100,
            'price_type_id' => 1,
            'vat_rate_id' => 1,
            'currency_id' => 1,
        ]);

        $fields = [
            'name' => 'Test Payment Item',
            'is_active' => true,
            'published' => true,

            'currency_id' => 1,

            'payment_service_id' => 1,
            'price_vat_rate_id' => 1,
            'price_discount_percentage' => 10,
            'items' => [$item->id],

            'payment_description' => 'Test Payment Description',
        ];

        $object = $this->repository->create($fields);

        $newItem = Item::create([
            'name' => 'Test Payment Item New',
            'is_active' => true,
            'published' => true,
        ]);

        $newItem->prices()->create([
            'price_value' => 200,
            'price_type_id' => 1,
            'vat_rate_id' => 1,
            'currency_id' => 1,
        ]);

        $post = Post::create([
            'name' => 'Test Payment Post',
            'content' => 'Test Payment Content',
        ]);
        $post->prices()->create([
            'price_value' => 200,
            'price_type_id' => 1,
            'vat_rate_id' => 1,
            'currency_id' => 1,
        ]);
        $copyPost = CopyPost::create([
            'test_model_id' => $object->id,
            'post_id' => $post->id,
        ]);

        $this->repository->update($object->id, [
            'items' => [$item->id, $newItem->id],
            'force_payment_update' => true,
        ]);

        $this->assertNotEmpty($object->paymentPrice);
        $this->assertSame(500, $object->paymentPrice->price_value);
    }

    public function test_default_payment_price_fields(): void
    {
        $this->assertEquals([], $this->repository->getDefaultPaymentPriceFields());

        // I wanna mock getDefaultPaymentPriceFields method to return empty array
        $mock = $this->partialMock(PaymentTraitTestRepository::class, function (\Mockery\MockInterface $mock) {
            $mock->shouldReceive('getDefaultPaymentPriceFields')->andReturn([
                'price_type_id' => 0,
                'vat_rate_id' => 0,
                'currency_id' => 0,
            ]);
        });

        $this->assertEquals([], $mock->defaultPaymentPriceFields());

        $mock = $this->partialMock(PaymentTraitTestRepository::class, function (\Mockery\MockInterface $mock) {
            $mock->shouldReceive('getDefaultPaymentPriceFields')->andReturn([
                'price_type_id' => 1,
                'vat_rate_id' => 1,
                'currency_id' => 1,
            ]);
        });

        $this->assertEquals([
            'price_type_id' => 1,
            'vat_rate_id' => 1,
            'currency_id' => 1,
        ], $mock->defaultPaymentPriceFields());
    }
}

class Item extends \Unusualify\Modularity\Entities\Model
{
    use \Unusualify\Modularity\Entities\Traits\HasPriceable;

    public $table = 'items';

    public $fillable = ['name'];

    public function hasPaymentTestModel(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(HasPaymentTestModel::class, 'test_model_item', 'item_id', 'test_model_id');
    }
}

class Post extends \Unusualify\Modularity\Entities\Model
{
    use \Unusualify\Modularity\Entities\Traits\HasPriceable;

    public $table = 'posts';

    public $fillable = ['name', 'content'];
}

class CopyPost extends \Unusualify\Modularity\Entities\Model
{
    use \Oobook\Snapshot\Traits\HasSnapshot;

    public static $snapshotSourceModel = Post::class;

    public $table = 'copy_posts';

    public $fillable = [
        'test_model_id',
    ];
}

class HasPaymentTestModel extends \Unusualify\Modularity\Tests\Repositories\TestModel
{
    use \Unusualify\Modularity\Entities\Traits\HasPayment;

    public $hasPaymentRelations = ['items', 'copyPosts'];

    public function items(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'test_model_item', 'test_model_id', 'item_id');
    }

    public function copyPosts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CopyPost::class, 'test_model_id');
    }

    public function getFormActionsConditionsForPayment(): array
    {
        return [
            ['state.code', 'in', ['pending-payment']],
        ];
    }

    public function getFormActionPropsForPaymentTrait()
    {
        return [
            'allowedRoles' => ['client-manager', 'client-assistant'],
        ];
    }

    public function getTableRowPropsForPayment()
    {
        return [
            'allowedRoles' => ['admin', 'manager'],
        ];
    }
}

class HasCreatorTestModel extends HasPaymentTestModel
{
    use \Unusualify\Modularity\Entities\Traits\HasCreator;

    protected static $creatableClass = TestModel::class;
}

class PaymentTraitTestRepository extends \Unusualify\Modularity\Tests\Repositories\TestRepository
{
    use \Unusualify\Modularity\Repositories\Traits\PaymentTrait;

    public function __construct(HasPaymentTestModel $model)
    {
        $this->model = $model;
    }
}

class CopyPostRepository extends \Unusualify\Modularity\Tests\Repositories\TestRepository
{
    public function __construct(CopyPost $model)
    {
        $this->model = $model;
    }
}
