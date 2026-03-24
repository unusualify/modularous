<?php

namespace Unusualify\Modularity\Tests\Repositories\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Entities\Traits\HasRepeaters;
use Unusualify\Modularity\Repositories\Traits\RepeatersTrait;
use Unusualify\Modularity\Tests\Repositories\Owner;
use Unusualify\Modularity\Tests\Repositories\RepositorySources;
use Unusualify\Modularity\Tests\Repositories\TestModel;
use Unusualify\Modularity\Tests\Repositories\TestRepository;
use Unusualify\Modularity\Tests\RepositoryTestCase;

class RepeatersTraitTest extends RepositoryTestCase
{
    use RepositorySources, RefreshDatabase;

    protected $schema = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();

        $this->repository = App::make(RepeatersTestRepository::class);

        $this->schema = [
            'name' => [
                'name' => 'name',
                'type' => 'text',
            ],
            'owner_id' => [
                'name' => 'owner_id',
                'type' => 'select',
            ],
            'custom_repeater' => [
                'type' => 'input-repeater',
                'root' => 'json-repeater',
                'name' => 'custom_repeater',
                'translated' => true,
                'schema' => [
                    [
                        'type' => 'text',
                        'name' => 'title',
                    ],
                    [
                        'type' => 'select',
                        'name' => 'company_type',
                    ],
                ],
            ],
        ];
    }

    public function test_set_columns_repeaters_trait()
    {
        $mock = \Mockery::mock(RepeatersTestRepository::class, [new RepeatersTestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('getRawInputs')->andReturn($this->schema);

        $mock->setColumns([]);

        $columns = $mock->getColumns(RepeatersTrait::class);

        $this->assertSame(['custom_repeater'], $columns);
    }

    public function test_should_ignore_field_before_save()
    {
        $owner = Owner::create(['name' => 'Test Owner']);
        config(['translatable.locales' => ['en', 'tr']]);

        $mock = \Mockery::mock(RepeatersTestRepository::class, [new RepeatersTestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('shouldIgnoreFieldBeforeSave')->withArgs(['repeaters'])->andReturn(true);

        $object = $mock->create([
            'name' => 'Test',
            'owner_id' => $owner->id,
            'custom_repeater' => [
                'en' => [
                    [
                        'title' => 'Test Title',
                        'company_type' => 'corporate',
                    ],
                ],
                'tr' => [
                    [
                        'title' => 'Deneme Başlık',
                        'company_type' => 'corporate',
                    ],
                ],
            ],
        ], $this->schema);

        $this->assertEquals(0, $object->repeaters()->count());
    }

    public function test_after_save_repeaters_trait_with_translated_input()
    {
        $owner = Owner::create(['name' => 'Test Owner']);
        config(['translatable.locales' => ['en', 'tr']]);

        $mock = \Mockery::mock(RepeatersTestRepository::class, [new RepeatersTestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('getRawInputs')->andReturn($this->schema);

        $object = $mock->create([
            'name' => 'Test',
            'owner_id' => $owner->id,
            'custom_repeater' => [
                'en' => [
                    [
                        'title' => 'Test Title',
                        'company_type' => 'corporate',
                    ],
                ],
                'tr' => [
                    [
                        'title' => 'Deneme Başlık',
                        'company_type' => 'corporate',
                    ],
                ],
            ],
        ], $this->schema);
        $object->refresh();

        $fields = $mock->getFormFields($object, $this->schema);

        $this->assertArrayHasKey('custom_repeater', $fields);
        $this->assertArrayHasKey('en', $fields['custom_repeater']);
        $this->assertArrayHasKey('tr', $fields['custom_repeater']);
        $this->assertArrayHasKey('title', $fields['custom_repeater']['en'][0]);
        $this->assertArrayHasKey('company_type', $fields['custom_repeater']['en'][0]);
        $this->assertArrayHasKey('title', $fields['custom_repeater']['tr'][0]);
        $this->assertArrayHasKey('company_type', $fields['custom_repeater']['tr'][0]);
        $this->assertSame('Test Title', $fields['custom_repeater']['en'][0]['title']);
        $this->assertSame('corporate', $fields['custom_repeater']['en'][0]['company_type']);
        $this->assertSame('Deneme Başlık', $fields['custom_repeater']['tr'][0]['title']);
        $this->assertSame('corporate', $fields['custom_repeater']['tr'][0]['company_type']);

        $mock->update($object->id, [
            'name' => 'Test',
            'owner_id' => $owner->id,
            'custom_repeater' => [
                'en' => [
                    [
                        'title' => 'Test Title',
                        'company_type' => 'corporate',
                    ],
                ],
                'tr' => [
                    [
                        'title' => 'Deneme Başlık 2',
                        'company_type' => 'corporate',
                    ],
                ],
            ],
        ], $this->schema);
        $object->refresh();

        $fields = $mock->getFormFields($object, $this->schema);

        $this->assertArrayHasKey('custom_repeater', $fields);
        $this->assertArrayHasKey('en', $fields['custom_repeater']);
        $this->assertArrayHasKey('tr', $fields['custom_repeater']);
        $this->assertArrayHasKey('title', $fields['custom_repeater']['en'][0]);
        $this->assertArrayHasKey('company_type', $fields['custom_repeater']['en'][0]);
        $this->assertArrayHasKey('title', $fields['custom_repeater']['tr'][0]);
        $this->assertArrayHasKey('company_type', $fields['custom_repeater']['tr'][0]);
        $this->assertSame('Test Title', $fields['custom_repeater']['en'][0]['title']);
        $this->assertSame('corporate', $fields['custom_repeater']['en'][0]['company_type']);
        $this->assertSame('Deneme Başlık 2', $fields['custom_repeater']['tr'][0]['title']);
        $this->assertSame('corporate', $fields['custom_repeater']['tr'][0]['company_type']);
    }

    public function test_after_save_repeaters_trait_without_translated_input()
    {
        $owner = Owner::create(['name' => 'Test Owner']);
        config(['translatable.locales' => ['en', 'tr']]);

        $mock = \Mockery::mock(RepeatersTestRepository::class, [new RepeatersTestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $schema = array_merge($this->schema, [
            'custom_repeater' => [
                'type' => 'json-repeater',
                'name' => 'custom_repeater',
                'translated' => false,
                'schema' => [
                    [
                        'type' => 'text',
                        'name' => 'title',
                    ],
                    [
                        'type' => 'select',
                        'name' => 'company_type',
                    ],
                ],
            ],
        ]);

        $mock->shouldReceive('getRawInputs')->andReturn($schema);

        $object = $mock->create([
            'name' => 'Test',
            'owner_id' => $owner->id,
            'custom_repeater' => [
                [
                    'title' => 'Test Title',
                    'company_type' => 'corporate',
                ],
            ],
        ], $schema);
        $object->refresh();

        $fields = $mock->getFormFields($object, $schema);

        $this->assertArrayHasKey('custom_repeater', $fields);
        $this->assertArrayHasKey('title', $fields['custom_repeater'][0]);
        $this->assertArrayHasKey('company_type', $fields['custom_repeater'][0]);
        $this->assertSame('Test Title', $fields['custom_repeater'][0]['title']);
        $this->assertSame('corporate', $fields['custom_repeater'][0]['company_type']);

        $mock->update($object->id, [
            'name' => 'Test',
            'owner_id' => $owner->id,
            'custom_repeater' => [
                [
                    'title' => 'Test Title 2',
                    'company_type' => 'corporate',
                ],
            ],
        ], $schema);
        $object->refresh();

        $fields = $mock->getFormFields($object, $schema);

        $this->assertArrayHasKey('custom_repeater', $fields);
        $this->assertArrayHasKey('title', $fields['custom_repeater'][0]);
        $this->assertArrayHasKey('company_type', $fields['custom_repeater'][0]);
        $this->assertSame('Test Title 2', $fields['custom_repeater'][0]['title']);
        $this->assertSame('corporate', $fields['custom_repeater'][0]['company_type']);

        // update with nontranslated input but with translated payload
        $mock->update($object->id, [
            'name' => 'Test',
            'owner_id' => $owner->id,
            'custom_repeater' => [
                'en' => [
                    [
                        'title' => 'Test Title 4',
                        'company_type' => 'corporate',
                    ],
                ],
                'tr' => [
                    [
                        'title' => 'Deneme Başlık 4',
                        'company_type' => 'corporate',
                    ],
                ],
            ],
        ], $schema);
        $object->refresh();

        $fields = $mock->getFormFields($object, $schema);

        $this->assertArrayHasKey('custom_repeater', $fields);
        $this->assertArrayHasKey('title', $fields['custom_repeater'][0]);
        $this->assertArrayHasKey('company_type', $fields['custom_repeater'][0]);
        $this->assertSame('Test Title 4', $fields['custom_repeater'][0]['title']);
        $this->assertSame('corporate', $fields['custom_repeater'][0]['company_type']);
    }

    public function test_get_form_fields_when_repeaters_is_empty()
    {
        $owner = Owner::create(['name' => 'Test Owner']);
        config(['translatable.locales' => ['en', 'tr']]);

        $mock = \Mockery::mock(RepeatersTestRepository::class, [new RepeatersTestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('getRawInputs')->andReturn($this->schema);

        $object = $mock->create([
            'name' => 'Test',
            'owner_id' => $owner->id,
        ], $this->schema);
        $object->refresh();

        $fields = $mock->getFormFields($object, $this->schema);

        $this->assertArrayHasKey('custom_repeater', $fields);
        $this->assertArrayHasKey('en', $fields['custom_repeater']);
        $this->assertArrayHasKey('tr', $fields['custom_repeater']);
        $this->assertCount(0, $fields['custom_repeater']['en']);
        $this->assertCount(0, $fields['custom_repeater']['tr']);
    }
}

class RepeatersTestModel extends TestModel
{
    use HasRepeaters;
}

class RepeatersTestRepository extends TestRepository
{
    use RepeatersTrait;

    public function __construct(RepeatersTestModel $model)
    {
        $this->model = $model;
    }
}
