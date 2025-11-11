<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Entities\Repeater;
use Unusualify\Modularity\Entities\Traits\Core\ModelHelpers;
use Unusualify\Modularity\Entities\Traits\HasRepeaters;
use Unusualify\Modularity\Tests\ModelTestCase;

class HasRepeatersTest extends ModelTestCase
{
    use RefreshDatabase;

    protected TestRepeatableModel $model;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test table
        Schema::create('test_repeaters_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->model = new TestRepeatableModel([
            'name' => 'Test Repeaters Model',
        ]);
        $this->model->save();
    }

    public function test_model_uses_has_repeaters_trait()
    {
        $traits = class_uses_recursive($this->model);
        $this->assertContains('Unusualify\Modularity\Entities\Traits\HasRepeaters', $traits);
    }

    public function test_has_repeaters_uses_required_traits()
    {
        $traits = class_uses_recursive($this->model);

        // HasRepeaters trait should use these other traits
        $this->assertContains('Unusualify\Modularity\Entities\Traits\HasFiles', $traits);
        $this->assertContains('Unusualify\Modularity\Entities\Traits\HasImages', $traits);
        $this->assertContains('Unusualify\Modularity\Entities\Traits\HasPriceable', $traits);
        $this->assertContains('Unusualify\Modularity\Entities\Traits\HasFileponds', $traits);
    }

    public function test_repeaters_relationship_exists()
    {
        $this->assertTrue(method_exists($this->model, 'repeaters'));

        $relation = $this->model->repeaters();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $relation);
    }

    public function test_repeaters_relationship_configuration()
    {
        $relation = $this->model->repeaters();

        $this->assertEquals('repeatable_id', $relation->getForeignKeyName());
        $this->assertEquals('repeatable_type', $relation->getMorphType());
        $this->assertEquals(Repeater::class, $relation->getRelated()::class);
    }

    public function test_can_create_repeater()
    {
        $repeaterData = [
            'repeatable_id' => $this->model->id,
            'repeatable_type' => get_class($this->model),
            'content' => ['field1' => 'value1', 'field2' => 'value2'],
            'role' => 'test',
            'locale' => 'en',
        ];

        $repeater = new Repeater($repeaterData);
        $this->model->repeaters()->save($repeater);

        $this->assertDatabaseHas('um_repeaters', [
            'repeatable_id' => $this->model->id,
            'repeatable_type' => get_class($this->model),
            'content' => json_encode(['field1' => 'value1', 'field2' => 'value2']),
            'role' => 'test',
            'locale' => 'en',
        ]);
    }

    public function test_can_retrieve_repeaters()
    {
        // Create multiple repeaters
        $repeater1 = new Repeater([
            'repeatable_id' => $this->model->id,
            'repeatable_type' => get_class($this->model),
            'content' => ['field1' => 'value1', 'field2' => 'value2'],
            'role' => 'test',
            'locale' => 'en',
        ]);
        $this->model->repeaters()->save($repeater1);

        $repeater2 = new Repeater([
            'repeatable_id' => $this->model->id,
            'repeatable_type' => get_class($this->model),
            'content' => ['field1' => 'value1', 'field2' => 'value2'],
            'role' => 'test',
            'locale' => 'en',
        ]);
        $this->model->repeaters()->save($repeater2);

        // Refresh model to load relationships
        $this->model->refresh();

        $repeaters = $this->model->repeaters;
        $this->assertCount(2, $repeaters);

        // Check first repeater
        $this->assertEquals($this->model->id, $repeaters->first()->repeatable_id);
        $this->assertEquals(get_class($this->model), $repeaters->first()->repeatable_type);
        $this->assertEquals('test', $repeaters->first()->role);

        // Check second repeater
        $this->assertEquals('test', $repeaters->get(1)->role);
    }

    public function test_repeaters_have_correct_content_structure()
    {
        $contentData = [
            'title' => 'Test Title',
            'description' => 'Test Description',
            'settings' => ['option1' => true, 'option2' => false],
        ];

        $repeater = new Repeater([
            'repeatable_id' => $this->model->id,
            'repeatable_type' => get_class($this->model),
            'content' => $contentData,
            'role' => 'test',
            'locale' => 'en',
        ]);
        $this->model->repeaters()->save($repeater);

        $savedRepeater = $this->model->repeaters()->first();

        $this->assertEquals($contentData, $savedRepeater->content);
        $this->assertEquals('Test Title', $savedRepeater->content['title']);
        $this->assertEquals('Test Description', $savedRepeater->content['description']);
        $this->assertTrue($savedRepeater->content['settings']['option1']);
        $this->assertFalse($savedRepeater->content['settings']['option2']);
    }

    public function test_repeaters_can_have_empty_content()
    {
        $repeater = new Repeater([
            'repeatable_id' => $this->model->id,
            'repeatable_type' => get_class($this->model),
            'content' => [], // Use empty array instead of null since content is cast to array
            'role' => 'test',
            'locale' => 'en',
        ]);
        $this->model->repeaters()->save($repeater);

        $savedRepeater = $this->model->repeaters()->first();

        $this->assertEquals([], $savedRepeater->content); // Expect empty array, not null
        // Remove the name assertion as it doesn't seem to be defined in the model
    }

    public function test_repeater_content_cannot_be_null()
    {
        $repeater = new Repeater([
            'repeatable_id' => $this->model->id,
            'repeatable_type' => get_class($this->model),
            'content' => null, // Explicitly set to null to test constraint violation
            'role' => 'test',
            'locale' => 'en',
        ]);

        // Expect a database constraint violation when trying to save null content
        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->expectExceptionMessage('NOT NULL constraint failed: um_repeaters.content');

        $this->model->repeaters()->save($repeater);
    }

    public function test_multiple_models_can_have_different_repeaters()
    {
        // Create another model
        $model2 = new TestRepeatableModel(['name' => 'Second Model']);
        $model2->save();

        // Add repeaters to first model
        $repeater1 = new Repeater([
            'repeatable_id' => $this->model->id,
            'repeatable_type' => get_class($this->model),
            'content' => [],
            'role' => 'test',
            'locale' => 'en',
        ]);
        $this->model->repeaters()->save($repeater1);

        // Add repeaters to second model
        $repeater2 = new Repeater([
            'repeatable_id' => $model2->id,
            'repeatable_type' => get_class($model2),
            'content' => [],
            'role' => 'test',
            'locale' => 'en',
        ]);
        $model2->repeaters()->save($repeater2);

        // Verify each model has its own repeaters
        $this->assertCount(1, $this->model->repeaters);
        $this->assertCount(1, $model2->repeaters);

        $this->assertEquals('test', $this->model->repeaters->first()->role);
        $this->assertEquals('test', $model2->repeaters->first()->role);
    }

    public function test_repeaters_relationship_is_morph_many()
    {
        $relation = $this->model->repeaters();

        // Verify it's a MorphMany relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $relation);

        // Verify the morph configuration
        $this->assertEquals('repeatable_type', $relation->getMorphType());
        $this->assertEquals('repeatable_id', $relation->getForeignKeyName());
    }

    public function test_repeaters_can_be_filtered_by_role_and_locale()
    {
        $this->model->repeaters()->saveMany([
            new Repeater([
                'repeatable_id' => $this->model->id,
                'repeatable_type' => get_class($this->model),
                'content' => ['name' => 'Feature EN'],
                'role' => 'features',
                'locale' => 'en',
            ]),
            new Repeater([
                'repeatable_id' => $this->model->id,
                'repeatable_type' => get_class($this->model),
                'content' => ['name' => 'Feature TR'],
                'role' => 'features',
                'locale' => 'tr',
            ]),
            new Repeater([
                'repeatable_id' => $this->model->id,
                'repeatable_type' => get_class($this->model),
                'content' => ['name' => 'Specs EN'],
                'role' => 'specs',
                'locale' => 'en',
            ]),
        ]);

        $this->assertEquals(1, $this->model->repeaters('features', 'en')->count());
        $this->assertEquals(1, $this->model->repeaters('features', 'tr')->count());
        $this->assertEquals(1, $this->model->repeaters('specs', 'en')->count());
        $this->assertEquals(0, $this->model->repeaters('specs', 'tr')->count());
    }

    public function test_get_repeater_field_returns_values_and_default()
    {
        $this->model->repeaters()->save(new Repeater([
            'repeatable_id' => $this->model->id,
            'repeatable_type' => get_class($this->model),
            'content' => [
                'title' => 'Hello',
                'meta' => ['description' => 'Desc'],
            ],
            'role' => 'seo',
            'locale' => 'en',
        ]));

        $this->model->refresh();

        $this->assertSame('Hello', $this->model->getRepeaterField('seo.title', 'en'));
        $this->assertSame('Desc', $this->model->getRepeaterField('seo.meta.description', 'en'));
        $this->assertSame('N/A', $this->model->getRepeaterField('seo.meta.key', 'en', 'N/A'));
        $this->assertSame('Default', $this->model->getRepeaterField('seo.title', 'fr', 'Default'));
    }

    public function test_repeater_roles_and_locale_roles_populated_on_retrieved()
    {
        $this->model->repeaters()->saveMany([
            new Repeater([
                'repeatable_id' => $this->model->id,
                'repeatable_type' => get_class($this->model),
                'content' => [],
                'role' => 'seo',
                'locale' => 'en',
            ]),
            new Repeater([
                'repeatable_id' => $this->model->id,
                'repeatable_type' => get_class($this->model),
                'content' => [],
                'role' => 'seo',
                'locale' => 'tr',
            ]),
            new Repeater([
                'repeatable_id' => $this->model->id,
                'repeatable_type' => get_class($this->model),
                'content' => [],
                'role' => 'features',
                'locale' => 'en',
            ]),
        ]);

        $this->model->refresh();

        $this->model = TestRepeatableModel::find(1);

        $this->assertEqualsCanonicalizing(['seo', 'features'], $this->model->getRepeaterRoles());

        $localeRoles = $this->model->getRepeaterLocaleRoles();
        $this->assertArrayHasKey('en', $localeRoles);
        $this->assertArrayHasKey('tr', $localeRoles);
        $this->assertEqualsCanonicalizing(['seo', 'features'], $localeRoles['en']);
        $this->assertEqualsCanonicalizing(['seo'], $localeRoles['tr']);
    }

    public function test_has_repeater_role_and_has_repeater_locale_role()
    {
        $this->model->repeaters()->saveMany([
            new Repeater([
                'repeatable_id' => $this->model->id,
                'repeatable_type' => get_class($this->model),
                'content' => [],
                'role' => 'seo',
                'locale' => 'en',
            ]),
            new Repeater([
                'repeatable_id' => $this->model->id,
                'repeatable_type' => get_class($this->model),
                'content' => [],
                'role' => 'features',
                'locale' => 'tr',
            ]),
        ]);

        $this->model->refresh();

        $this->model = TestRepeatableModel::find(1);

        $this->assertTrue($this->model->hasRepeaterRole('seo'));
        $this->assertTrue($this->model->hasRepeaterRole('features'));
        $this->assertFalse($this->model->hasRepeaterRole('unknown'));

        $this->assertTrue($this->model->hasRepeaterLocaleRole('seo', 'en'));
        $this->assertTrue($this->model->hasRepeaterLocaleRole('features', 'tr'));
        $this->assertFalse($this->model->hasRepeaterLocaleRole('seo', 'tr'));
        $this->assertFalse($this->model->hasRepeaterLocaleRole('features', 'en'));
    }

    public function test_is_repeater_value_equal_loaded_and_unloaded_relations()
    {
        $this->model->repeaters()->save(new Repeater([
            'repeatable_id' => $this->model->id,
            'repeatable_type' => get_class($this->model),
            'content' => [
                'title' => 'Hello',
                'meta' => ['description' => 'Desc'],
            ],
            'role' => 'seo',
            'locale' => 'en',
        ]));

        $this->model->refresh();

        $this->model = TestRepeatableModel::find(1);
        $this->assertTrue($this->model->isRepeaterValueEqual('seo.title', 'Hello', 'en'));
        $this->assertFalse($this->model->isRepeaterValueEqual('seo.title', 'World', 'en'));
        $this->assertFalse($this->model->isRepeaterValueEqual('seo.title', 'World', 'fr'));
        $this->assertTrue($this->model->isRepeaterValueEqual('seo.meta.description', 'Desc', 'en'));

        $this->model->load('repeaters');
        $this->assertTrue($this->model->isRepeaterValueEqual('seo.title', 'Hello', 'en'));
        $this->assertFalse($this->model->isRepeaterValueEqual('seo.title', 'World', 'en'));
        $this->assertFalse($this->model->isRepeaterValueEqual('seo.title', 'World', 'fr'));
        $this->assertTrue($this->model->isRepeaterValueEqual('seo.meta.description', 'Desc', 'en'));

        $fresh = TestRepeatableModel::find($this->model->id);
        $this->assertTrue($fresh->isRepeaterValueEqual('seo.title', 'Hello', 'en'));
        $this->assertFalse($fresh->isRepeaterValueEqual('seo.title', 'World', 'en'));
        $this->assertTrue($fresh->isRepeaterValueEqual('seo.meta.description', 'Desc', 'en'));
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_repeaters_models');
        Schema::dropIfExists('um_repeaters');
        parent::tearDown();
    }
}

// Test model that uses the HasRepeaters trait
class TestRepeatableModel extends Model
{
    use ModelHelpers, HasRepeaters;

    protected $table = 'test_repeaters_models';

    protected $fillable = ['name'];
}
