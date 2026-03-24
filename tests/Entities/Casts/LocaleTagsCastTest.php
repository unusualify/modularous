<?php

namespace Unusualify\Modularity\Tests\Entities\Casts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Unusualify\Modularity\Entities\Casts\LocaleTagsCast;
use Unusualify\Modularity\Tests\TestCase;

class LocaleTagsCastTest extends TestCase
{
    protected LocaleTagsCast $cast;

    protected function setUp(): void
    {
        parent::setUp();
        config(['translatable.locales' => ['en', 'tr']]);
        $this->cast = new LocaleTagsCast;
    }

    public function test_set_returns_value_as_is()
    {
        $model = $this->createMock(Model::class);
        $value = ['en' => ['tag1'], 'tr' => ['tag2']];
        $attributes = [];

        $result = $this->cast->set($model, 'locale_tags_payload', $value, $attributes);

        $this->assertSame($value, $result);
    }

    public function test_set_returns_null_when_value_is_null()
    {
        $model = $this->createMock(Model::class);

        $result = $this->cast->set($model, 'locale_tags_payload', null, []);

        $this->assertNull($result);
    }

    public function test_get_returns_array_keyed_by_locale()
    {
        $tag1 = (object) ['name' => 'tag1'];
        $tag2 = (object) ['name' => 'tag2'];
        $tag3 = (object) ['name' => 'tag3'];

        $model = new class extends Model
        {
            public $singularLocaleTags = false;

            public $localeTagsMap = [];

            public function localeTags(string $locale)
            {
                return $this->localeTagsMap[$locale] ?? collect([]);
            }
        };
        $model->localeTagsMap = [
            'en' => $this->createRelationMock(collect([$tag1, $tag2])),
            'tr' => $this->createRelationMock(collect([$tag3])),
        ];

        $result = $this->cast->get($model, 'locale_tags_payload', null, []);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('en', $result);
        $this->assertArrayHasKey('tr', $result);
        $this->assertEquals(['tag1', 'tag2'], $result['en']);
        $this->assertEquals(['tag3'], $result['tr']);
    }

    public function test_get_with_singular_locale_tags_returns_first_tag_name()
    {
        $tag1 = (object) ['name' => 'single-tag'];

        $model = new class extends Model
        {
            public $singularLocaleTags = true;

            public $localeTagsMap = [];

            public function localeTags(string $locale)
            {
                return $this->localeTagsMap[$locale] ?? collect([]);
            }
        };
        $model->localeTagsMap = [
            'en' => $this->createRelationMock(collect([$tag1])),
            'tr' => $this->createRelationMock(collect([])),
        ];

        $result = $this->cast->get($model, 'locale_tags_payload', null, []);

        $this->assertEquals('single-tag', $result['en']);
        $this->assertNull($result['tr']);
    }

    public function test_get_with_empty_tags_returns_empty_array_per_locale()
    {
        $model = new class extends Model
        {
            public $singularLocaleTags = false;

            public $localeTagsMap = [];

            public function localeTags(string $locale)
            {
                return $this->localeTagsMap[$locale] ?? collect([]);
            }
        };
        $model->localeTagsMap = [
            'en' => $this->createRelationMock(collect([])),
            'tr' => $this->createRelationMock(collect([])),
        ];

        $result = $this->cast->get($model, 'locale_tags_payload', null, []);

        $this->assertEquals([], $result['en']);
        $this->assertEquals([], $result['tr']);
    }

    private function createRelationMock(Collection $tags): object
    {
        $relation = new class
        {
            public $collection;

            public function get()
            {
                return $this->collection;
            }
        };
        $relation->collection = $tags;

        return $relation;
    }
}
