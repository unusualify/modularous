<?php

namespace Unusualify\Modularity\Tests\Traits;

use Illuminate\Support\Facades\Hash;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Module;
use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\Traits\ManageTraits;

class ManageTraitsTest extends TestCase
{
    protected $target;

    protected function setUp(): void
    {
        parent::setUp();
        $this->target = new class
        {
            use ManageTraits;

            // Mocking required methods from other traits/classes
            public function getModuleName()
            {
                return 'TestModule';
            }

            public function getRouteName()
            {
                return 'TestRoute';
            }
        };
    }

    /** @test */
    public function it_can_prepare_fields_before_save()
    {
        $fields = [
            'name' => 'John',
            'password' => 'secret',
            'settings->theme' => 'dark',
            'profile.bio' => 'Hello',
        ];

        $object = (object) ['settings' => ['font' => 'sans']];

        $prepared = $this->target->prepareFieldsBeforeSaveManageTraits($object, $fields);

        $this->assertEquals('John', $prepared['name']);
        $this->assertTrue(Hash::check('secret', $prepared['password']));
        $this->assertEquals('dark', $prepared['settings']['theme']);
        $this->assertEquals('sans', $prepared['settings']['font']);
        $this->assertEquals('Hello', $prepared['profile']['bio']);
    }

    /** @test */
    public function it_can_detect_translated_inputs()
    {
        $schema = [
            ['name' => 'title', 'translated' => true],
            ['name' => 'description', 'translated' => false],
        ];

        $this->assertTrue($this->target->hasTranslatedInput($schema));
        $this->assertFalse($this->target->hasTranslatedInput([['name' => 'test']]));
    }

    /** @test */
    public function it_can_chunk_inputs()
    {
        $schema = [
            'title' => ['name' => 'title', 'type' => 'text'],
            'group1' => [
                'name' => 'group1',
                'type' => 'group',
                'schema' => [
                    ['name' => 'sub1', 'type' => 'text'],
                ],
            ],
        ];

        $chunked = $this->target->chunkInputs($schema);

        $this->assertArrayHasKey('title', $chunked);
        $this->assertArrayHasKey('group1.sub1', $chunked);
        $this->assertEquals('group1.sub1', $chunked['group1.sub1']['name']);
        $this->assertEquals('group1', $chunked['group1.sub1']['parentName']);
    }

    /** @test */
    public function it_can_resolve_model()
    {
        $module = \Mockery::mock(Module::class);
        $module->shouldReceive('hasRoute')->with('TestRoute')->andReturn(true);
        $module->shouldReceive('getModel')->with('TestRoute')->once()->andReturn('TestModel');

        // isModuleRouteClass() and getModule() each call Modularity::find() with the same name.
        Modularity::shouldReceive('find')->with('TestModule')->andReturn($module);

        $this->assertEquals('TestModel', $this->target->model());
    }
}
