<?php

namespace Unusualify\Modularous\Tests\Repositories\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Unusualify\Modularous\Entities\Traits\HasSpreadable;
use Unusualify\Modularous\Repositories\Traits\SpreadableTrait;
use Unusualify\Modularous\Tests\Repositories\RepositorySources;
use Unusualify\Modularous\Tests\Repositories\TestModel;
use Unusualify\Modularous\Tests\Repositories\TestRepository;
use Unusualify\Modularous\Tests\RepositoryTestCase;

class SpreadableTraitTest extends RepositoryTestCase
{
    use RefreshDatabase, RepositorySources;

    protected $schema = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();

        $this->repository = App::make(SpreadableTestRepository::class);

        $this->schema = [
            'spread_payload' => [
                'type' => 'input-spread',
                'name' => 'spread_payload',
            ],
        ];
    }

    public function test_set_columns_spreadable_trait_collects_spreadable_inputs()
    {
        $this->repository->setColumns($this->schema);

        $columns = $this->repository->getColumns(SpreadableTrait::class);

        $this->assertSame(['spread_payload'], $columns);
    }

    public function test_prepare_fields_before_save_moves_spreadable_fields_into_spread_key()
    {
        $mock = \Mockery::mock(SpreadableTestRepository::class, [new SpreadableTestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $schema = [
            ['name' => 'name', 'type' => 'text'],
            ['name' => 'headline', 'type' => 'text', 'spreadable' => true],
            ['name' => 'summary', 'type' => 'text', 'spreadable' => true],
        ];

        $mock->shouldReceive('inputs')->andReturn($schema);

        $fields = [
            'name' => 'Test',
            'headline' => 'Hello',
            'summary' => 'Short',
            'spread_payload' => ['existing' => true],
        ];

        $object = $mock->create($fields, $schema);

        $object = $mock->getModel()->find($object->id);
        $attributes = $object->toArray();

        $this->assertSame('Hello', $attributes['headline']);
        $this->assertSame('Short', $attributes['summary']);
    }

    public function test_get_form_fields_spreadable_trait_merges_content_excluding_input_keys()
    {
        $mock = \Mockery::mock(SpreadableTestRepository::class, [new SpreadableTestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $schema = [
            ['name' => 'name', 'type' => 'text'],
            ['name' => 'spread_payload', 'type' => 'input-spread'],
        ];

        $mock->shouldReceive('inputs')->andReturn($schema);

        $fields = [
            'name' => 'Test',
            'spread_payload' => ['headline' => 'Hello', 'summary' => 'Short'],
        ];

        $object = $mock->create($fields, $schema);

        $object = $mock->getModel()->find($object->id);

        $formFields = $mock->getFormFields($object, $schema);

        $this->assertSame('Test', $formFields['name']);
        $this->assertSame('Hello', $formFields['spread_payload']['headline']);
        $this->assertSame('Short', $formFields['spread_payload']['summary']);
    }

    public function test_before_save_spreadable_trait_creates_spreadable_model_if_not_exists()
    {
        $mock = \Mockery::mock(SpreadableTestRepository::class, [new SpreadableTestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $schema = [
            ['name' => 'name', 'type' => 'text'],
            ['name' => 'title', 'type' => 'text', 'spreadable' => true],
        ];

        $object = new SpreadableTestModel;
        $object->name = 'Test';
        $object->is_active = true;
        $object->saveQuietly();
        $object->refresh();

        $mock->shouldReceive('inputs')->andReturn($schema);

        $fields = [
            'name' => 'Test',
            'title' => 'Hello',
        ];

        $mock->update($object->id, $fields, $schema);
        $object->refresh();

        $this->assertNotNull($object->spreadable);
        $this->assertSame('Hello', $object->spreadable->content['title']);
    }
}

class SpreadableTestModel extends TestModel
{
    use HasSpreadable;

    protected static $spreadableSavingKey = 'spread_payload';

    public static $spreadableClass = TestModel::class;
}

class SpreadableTestRepository extends TestRepository
{
    use SpreadableTrait;

    public function __construct(SpreadableTestModel $model)
    {
        $this->model = $model;
    }
}
