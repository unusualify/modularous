<?php

namespace Unusualify\Modularous\Tests\Repositories\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Unusualify\Modularous\Repositories\Traits\TagsTrait;
use Unusualify\Modularous\Tests\Repositories\RepositorySources;
use Unusualify\Modularous\Tests\Repositories\TestModel;
use Unusualify\Modularous\Tests\Repositories\TestRepository;
use Unusualify\Modularous\Tests\RepositoryTestCase;

class TagsTraitTest extends RepositoryTestCase
{
    use RefreshDatabase, RepositorySources;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();

        $this->repository = App::make(TagsTestRepository::class);
    }

    public function test_set_columns_tags_trait_collects_tagger_inputs(): void
    {
        $repo = new class
        {
            use TagsTrait;
        };

        $columns = $repo->setColumnsTagsTrait([], [
            ['name' => 'tags', 'type' => 'tagger'],
            ['name' => 'title', 'type' => 'text'],
        ]);

        $this->assertSame(['tags'], $columns['TagsTrait']);
    }

    public function test_after_save_sets_tags_when_not_ignored(): void
    {
        $repo = $this->partialMock(TagsTestRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('shouldIgnoreFieldBeforeSave')->with('tags')->andReturn(false);
        });

        $object = new class
        {
            public array $tagsSet = [];

            public function setTags($tags)
            {
                $this->tagsSet = $tags;
            }

            public function has($rel)
            {
                return false;
            }
        };

        $repo->afterSaveTagsTrait($object, ['tags' => ['a', 'b']]);
        $this->assertSame(['a', 'b'], $object->tagsSet);

        // translated tags
        config(['translatable.locales' => ['en', 'tr']]);
        $mock = \Mockery::mock(TagsTestRepository::class, [new TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $object = $mock->create([
            'name' => 'Test',
        ]);

        $mock->shouldReceive('getRawInputs')->andReturn([
            'tags' => [
                'type' => 'tag',
                'name' => 'tags',
                'translated' => true,
            ],
        ]);

        $mock->update($object->id, ['tags' => ['en' => ['a'], 'tr' => ['b']]]);
        $object->refresh();

        $fields = $mock->getFormFields($object, [
            'tags' => [
                'type' => 'tagger',
                'name' => 'tags',
                'translated' => true,
            ],
        ]);

        $this->assertSame(['a'], $fields['tags']['en']->toArray());
        $this->assertSame(['b'], $fields['tags']['tr']->toArray());
    }

    public function test_after_save_bulk_tags_updates_and_respects_previous_common_tags(): void
    {
        $repo = $this->partialMock(TagsTestRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('shouldIgnoreFieldBeforeSave')->with('bulk_tags')->andReturn(false);
        });

        $object = new class
        {
            public array $untagged = [];

            public array $tagged = [];

            public function untag($diff)
            {
                $this->untagged = $diff;
            }

            public function tag($tags)
            {
                $this->tagged = $tags;
            }
        };

        $repo->afterSaveTagsTrait($object, [
            'bulk_tags' => ['a', 'c'],
            'previous_common_tags' => Collection::make([['name' => 'a'], ['name' => 'b']]),
        ]);

        $this->assertSame(['b'], array_values($object->untagged));
        $this->assertSame(['a', 'c'], array_values($object->tagged));
    }

    public function test_get_form_fields_tags_trait_maps_tag_names(): void
    {
        $repo = $this->partialMock(TagsTestRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('getColumns')->andReturn(['tags']);
        });

        $object = new class
        {
            public function has($rel)
            {
                return $rel === 'tags';
            }

            public $tags;

            public function __construct()
            {
                $this->tags = Collection::make([(object) ['name' => 't1'], (object) ['name' => 't2']]);
            }
        };

        $fields = [];
        $result = $repo->getFormFieldsTagsTrait($object, $fields, []);

        $this->assertSame(['t1', 't2'], $result['tags']->toArray());
    }

    public function test_filter_tags_trait_calls_add_relation_filter_scope(): void
    {
        $this->repository->getModel()->createTagsModel()->create([
            'name' => 't1',
            'slug' => Str::slug('t1'),
            'namespace' => get_class($this->repository->getModel()),
        ]);
        $this->repository->getModel()->createTagsModel()->create([
            'name' => 't2',
            'slug' => Str::slug('t2'),
            'namespace' => get_class($this->repository->getModel()),
        ]);

        $this->repository->create([
            'name' => 'Test 1',
            'tags' => ['t1'],
        ]);

        $this->repository->create([
            'name' => 'Test 2',
            'tags' => ['t2'],
        ]);

        $query = $this->repository->newQuery();
        $this->assertCount(1, $this->repository->filter($query, ['tag_id' => [1]])->get());
        $query = $this->repository->newQuery();
        $this->assertCount(1, $this->repository->filter($query, ['tag_id' => [2]])->get());
        $query = $this->repository->newQuery();
        $this->assertCount(2, $this->repository->filter($query, ['tag_id' => [1, 2]])->get());
    }

    public function test_get_tags_query_orders_by_count_desc(): void
    {

        $this->repository->getModel()->createTagsModel()->create([
            'name' => 't1',
            'slug' => Str::slug('t1'),
            'namespace' => get_class($this->repository->getModel()),
        ]);
        $this->repository->getModel()->createTagsModel()->create([
            'name' => 't2',
            'slug' => Str::slug('t2'),
            'namespace' => get_class($this->repository->getModel()),
        ]);
        $this->repository->getModel()->createTagsModel()->create([
            'name' => 't3',
            'slug' => Str::slug('t3'),
            'namespace' => get_class($this->repository->getModel()),
        ]);

        $this->repository->create([
            'name' => 'Test 1',
            'tags' => ['t2', 't3'],
        ]);

        $this->repository->create([
            'name' => 'Test 2',
            'tags' => ['t2'],
        ]);
        $this->repository->create([
            'name' => 'Test 3',
            'tags' => ['t1'],
        ]);

        $mock = \Mockery::mock(TagsTestRepository::class, [new TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $query = $mock->getTagsQuery();

        $this->assertEquals(2, $query->get()->first()->id);
        $this->assertEquals(3, $query->get()->last()->id);
    }

    public function test_get_tags_applies_slug_and_ids_filters(): void
    {
        $mock = \Mockery::mock(TagsTestRepository::class, [new TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->repository->getModel()->createTagsModel()->create([
            'name' => 't1',
            'slug' => Str::slug('t1'),
            'namespace' => get_class($this->repository->getModel()),
        ]);
        $this->repository->getModel()->createTagsModel()->create([
            'name' => 't2',
            'slug' => Str::slug('t2'),
            'namespace' => get_class($this->repository->getModel()),
        ]);
        $this->repository->getModel()->createTagsModel()->create([
            'name' => 't3',
            'slug' => Str::slug('t3'),
            'namespace' => get_class($this->repository->getModel()),
        ]);

        $object1 = $this->repository->create([
            'name' => 'Test 1',
            'tags' => ['t2', 't3'],
        ]);
        $object2 = $this->repository->create([
            'name' => 'Test 2',
            'tags' => ['t2'],
        ]);
        $object3 = $this->repository->create([
            'name' => 'Test 3',
            'tags' => ['t1'],
        ]);

        $tags = $mock->getTags('t2');

        $this->assertCount(1, $tags);
        $this->assertEquals('t2', $tags->first()->name);

        $tags = $mock->getTags(ids: [$object1->id]);
        $this->assertCount(2, $tags);
        $this->assertEquals('t2', $tags[0]['name']);
        $this->assertEquals('t3', $tags[1]['name']);

        $tags = $mock->getTags(ids: [$object2->id]);
        $this->assertCount(1, $tags);
        $this->assertEquals('t2', $tags[0]['name']);

        $tags = $mock->getTags(ids: [$object3->id]);
        $this->assertCount(1, $tags);
        $this->assertEquals('t1', $tags[0]['name']);

        $tags = $mock->getTags(ids: [$object1->id], map: fn ($tag) => $tag['name']);
        $this->assertCount(2, $tags);
        $this->assertEquals('t2', $tags[0]);
        $this->assertEquals('t3', $tags[1]);

        // translated tags
        config(['translatable.locales' => ['en', 'tr']]);
        $mock = \Mockery::mock(TagsTestRepository::class, [new TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('getRawInputs')->andReturn([
            'tags' => [
                'type' => 'tag',
                'name' => 'tags',
                'translated' => true,
            ],
        ]);

        $object1 = $mock->create([
            'name' => 'Test 1',
            'tags' => ['en' => ['t2'], 'tr' => ['t3']],
        ]);
        $object2 = $mock->create([
            'name' => 'Test 2',
            'tags' => ['en' => ['t2'], 'tr' => ['t3']],
        ]);
        $object3 = $mock->create([
            'name' => 'Test 3',
            'tags' => ['en' => ['t1'], 'tr' => ['t2']],
        ]);

        $tags = $mock->getTags(query: 't2', translated: true);
        $this->assertCount(2, $tags);
        $this->assertEquals('t2', $tags['en'][0]['name']);
        $this->assertEquals('t2', $tags['tr'][0]['name']);

        $tags = $mock->getTags(ids: [$object1->id], translated: true);
        $this->assertCount(1, $tags['en']);
        $this->assertEquals('t2', $tags['en'][0]['name']);
        $this->assertCount(1, $tags['tr']);
        $this->assertEquals('t3', $tags['tr'][0]['name']);

        $tags = $mock->getTags(ids: [$object1->id], translated: true, map: fn ($tag) => $tag['name']);
        $this->assertCount(1, $tags['en']);
        $this->assertEquals('t2', $tags['en'][0]);
        $this->assertCount(1, $tags['tr']);
        $this->assertEquals('t3', $tags['tr'][0]);

    }

    public function test_get_tags_list_returns_label_value_pairs(): void
    {
        $mock = \Mockery::mock(TagsTestRepository::class, [new TestModel])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->repository->getModel()->createTagsModel()->create([
            'name' => 't1',
            'slug' => Str::slug('t1'),
            'namespace' => get_class($this->repository->getModel()),
        ]);
        $this->repository->getModel()->createTagsModel()->create([
            'name' => 't2',
            'slug' => Str::slug('t2'),
            'namespace' => get_class($this->repository->getModel()),
        ]);
        $this->repository->getModel()->createTagsModel()->create([
            'name' => 't3',
            'slug' => Str::slug('t3'),
            'namespace' => get_class($this->repository->getModel()),
        ]);

        $this->repository->create([
            'name' => 'Test 1',
            'tags' => ['t2', 't3'],
        ]);
        $this->repository->create([
            'name' => 'Test 2',
            'tags' => ['t2'],
        ]);
        $this->repository->create([
            'name' => 'Test 3',
            'tags' => ['t1'],
        ]);

        $tags = $mock->getTagsList();

        $this->assertCount(3, $tags);
        $this->assertEquals('t2', $tags->first()['label']);
        $this->assertEquals(2, $tags->first()['value']);
        $this->assertEquals('t3', $tags->last()['label']);
        $this->assertEquals(3, $tags->last()['value']);
    }
}

class TagsTestRepository extends TestRepository
{
    use TagsTrait;

    public function __construct(TestModel $model)
    {
        $this->model = $model;
    }
}
