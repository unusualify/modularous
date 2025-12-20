<?php

namespace Unusualify\Modularity\Tests\Repositories\Logic;

use Unusualify\Modularity\Tests\RepositoryTestCase;

class SchemaTest extends RepositoryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }
    public function test_schema_get_raw_inputs(): void
    {
        // getFormFieldsRelationships for morphMany relationships
        $mock = \Mockery::mock(\Unusualify\Modularity\Tests\Repositories\TestRepository::class, [new \Unusualify\Modularity\Tests\Repositories\TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('inputs')->andReturn([
            [
                'name' => 'posts',
                'type' => 'select',
            ]
        ]);
        $inputs = $mock->getInputs();
        $this->assertCount(1, $inputs);
        $this->assertEquals('posts', $inputs[0]['name']);
        $this->assertEquals('select', $inputs[0]['type']);
    }

    public function test_schema_get_schema(): void
    {
        $mock = \Mockery::mock(\Unusualify\Modularity\Tests\Repositories\TestRepository::class, [new \Unusualify\Modularity\Tests\Repositories\TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $mock->setSchema([
            [
                'name' => 'posts',
                'type' => 'select',
            ]
        ]);
        $schema = $mock->getSchema();
        $this->assertCount(1, $schema);
        $this->assertEquals('posts', $schema[0]['name']);
        $this->assertEquals('select', $schema[0]['type']);

        $schema = $mock->getInputs();
        $this->assertCount(1, $schema);
        $this->assertEquals('posts', $schema[0]['name']);
        $this->assertEquals('select', $schema[0]['type']);
    }


    public function test_schema_get_chunked_inputs(): void
    {
        $mock = \Mockery::mock(\Unusualify\Modularity\Tests\Repositories\TestRepository::class, [new \Unusualify\Modularity\Tests\Repositories\TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mock->setSchema([
            [
                'name' => 'name',
                'type' => 'text',
            ],
            [
                'type' => 'group',
                'name' => 'content',
                'schema' => [
                    [
                        'name' => 'title',
                        'type' => 'text',
                    ]
                ]
            ],
            [
                'type' => 'wrap',
                'name' => 'wrap-1',
                'schema' => [
                    [
                        'name' => 'notes',
                        'type' => 'select',
                    ],
                ]
            ]
        ]);

        $inputs = $mock->getChunkedInputs();
        $this->assertCount(3, $inputs);
        $this->assertEquals('name', $inputs['name']['name']);
        $this->assertEquals('text', $inputs['name']['type']);
        $this->assertEquals('content.title', $inputs['content.title']['name']);
        $this->assertEquals('text', $inputs['content.title']['type']);
        $this->assertEquals('content', $inputs['content.title']['parentName']);
        $this->assertEquals('notes', $inputs['notes']['name']);
        $this->assertEquals('select', $inputs['notes']['type']);


        $inputs = $mock->getChunkedInputs(all: false, noGroupChunk: true);
        $this->assertCount(3, $inputs);
        $this->assertEquals('name', $inputs['name']['name']);
        $this->assertEquals('text', $inputs['name']['type']);
        $this->assertEquals('content', $inputs['content']['name']);
        $this->assertEquals('group', $inputs['content']['type']);
        $this->assertEquals('title', $inputs['content']['schema'][0]['name']);
        $this->assertEquals('text', $inputs['content']['schema'][0]['type']);
        $this->assertEquals('notes', $inputs['notes']['name']);
        $this->assertEquals('select', $inputs['notes']['type']);
    }

    public function test_schema_get_raw_chunked_inputs(): void
    {
        $mock = \Mockery::mock(\Unusualify\Modularity\Tests\Repositories\TestRepository::class, [new \Unusualify\Modularity\Tests\Repositories\TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('inputs')->andReturn([
            [
                'name' => 'name',
                'type' => 'text',
            ],
            [
                'type' => 'group',
                'name' => 'content',
                'schema' => [
                    [
                        'name' => 'title',
                        'type' => 'text',
                    ]
                ]
            ],
            [
                'type' => 'wrap',
                'name' => 'wrap-1',
                'schema' => [
                    [
                        'name' => 'notes',
                        'type' => 'select',
                    ]
                ]
            ]
        ]);

        $inputs = $mock->getRawChunkedInputs();
        $this->assertCount(3, $inputs);
        $this->assertEquals('name', $inputs['name']['name']);
        $this->assertEquals('text', $inputs['name']['type']);
        $this->assertEquals('content.title', $inputs['content.title']['name']);
        $this->assertEquals('text', $inputs['content.title']['type']);
        $this->assertEquals('content', $inputs['content.title']['parentName']);
        $this->assertEquals('notes', $inputs['notes']['name']);
        $this->assertEquals('select', $inputs['notes']['type']);

        $inputs = $mock->getRawChunkedInputs(all: false, noGroupChunk: true);
        $this->assertCount(3, $inputs);
        $this->assertEquals('name', $inputs['name']['name']);
        $this->assertEquals('text', $inputs['name']['type']);
        $this->assertEquals('content', $inputs['content']['name']);
        $this->assertEquals('group', $inputs['content']['type']);
        $this->assertEquals('title', $inputs['content']['schema'][0]['name']);
        $this->assertEquals('text', $inputs['content']['schema'][0]['type']);
        $this->assertEquals('notes', $inputs['notes']['name']);
        $this->assertEquals('select', $inputs['notes']['type']);
    }
}
