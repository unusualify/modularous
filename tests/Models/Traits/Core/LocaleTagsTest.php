<?php

namespace Unusualify\Modularous\Tests\Models\Traits\Core;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Traits\Core\LocaleTags;
use Unusualify\Modularous\Tests\ModelTestCase;

class LocaleTagsTest extends ModelTestCase
{
    use RefreshDatabase;

    protected $model;

    protected $testModel;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('locale_tags_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

    }

    public function test_save_locale_tags()
    {
        config(['translatable.locales' => ['en', 'tr']]);
        $object = LocaleTagsModel::create(['name' => 'Test Model', 'locale_tags_payload' => [
            'en' => ['test', 'test2'],
            'tr' => ['test', 'test2'],
        ]]);

        $object = LocaleTagsModel::find($object->id);
        $this->assertEquals(['test', 'test2'], $object->locale_tags_payload['en']);
        $this->assertEquals(['test', 'test2'], $object->locale_tags_payload['tr']);
    }

    public function test_add_locale_tag()
    {
        $object = LocaleTagsModel::create(['name' => 'Test Model']);
        $object->addLocaleTag('test', 'en');
        $object->addLocaleTag('test2', 'en');

        $this->assertDatabaseHas(modularousConfig('tables.tags', 'tags'), [
            'name' => 'test',
            'locale' => 'en',
        ]);
        $this->assertDatabaseHas(modularousConfig('tables.tags', 'tags'), [
            'name' => 'test2',
            'locale' => 'en',
        ]);

        $this->assertEquals(2, $object->tags->where('locale', 'en')->count());
        $this->assertEquals(0, $object->tags->where('locale', 'tr')->count());

        $object->addLocaleTag('test', 'tr');
        $object->addLocaleTag('test2', 'tr');

        $this->assertEquals(2, $object->tags->where('locale', 'tr')->count());
        $this->assertEquals(4, $object->tags->count());
    }

    public function test_untag_locale_tag()
    {
        $object = LocaleTagsModel::create(['name' => 'Test Model']);
        $object->addLocaleTag('test', 'en');
        $object->addLocaleTag('test2', 'en');

        $object->untagLocale('test2', 'en');
        $this->assertEquals(1, $object->tags->count());
        $this->assertEquals('test', $object->tags->first()->name);

        $object->addLocaleTag('test', 'tr');
        $object->addLocaleTag('test2', 'tr');
        $object->untagLocale('test', 'tr');

        $this->assertEquals(2, $object->tags->count());
        $this->assertEquals('test2', $object->tags()->where('locale', 'tr')->first()->name);
    }

    public function test_tag_locale_tag()
    {
        $object = LocaleTagsModel::create(['name' => 'Test Model']);
        $object->addLocaleTag('test', 'en');
        $object->addLocaleTag('test2', 'en');
        $object->tagLocale('test3,test4', 'en');

        $this->assertEquals(4, $object->tags->count());

        $object->addLocaleTag('test', 'tr');
        $object->addLocaleTag('test2', 'tr');

        $this->assertEquals(6, $object->tags->count());
        $this->assertEquals(2, $object->tags->where('locale', 'tr')->count());
    }

    public function test_set_locale_tags()
    {
        $object = LocaleTagsModel::create(['name' => 'Test Model']);
        $object->setLocaleTags(['test', 'test2'], locale: 'en');

        $this->assertEquals(2, $object->tags->count());
        $this->assertEquals('test', $object->tags->first()->name);
        $this->assertEquals('test2', $object->tags()->where('locale', 'en')->get()[1]->name);

        $object->setLocaleTags(['test', 'test2'], locale: 'tr');
        $this->assertEquals(4, $object->tags->count());
        $this->assertEquals('test', $object->tags()->where('locale', 'tr')->first()->name);
        $this->assertEquals('test2', $object->tags()->where('locale', 'tr')->get()[1]->name);

        $object->setLocaleTags(['test', 'test3'], locale: 'en');
        $this->assertEquals(4, $object->tags->count());
        $this->assertEquals('test', $object->tags()->where('locale', 'en')->first()->name);
        $this->assertEquals('test3', $object->tags()->where('locale', 'en')->get()[1]->name);
    }

    public function test_all_locale_tags()
    {
        $object = LocaleTagsModel::create(['name' => 'Test Model']);
        $object->setLocaleTags(['test', 'test2'], locale: 'en');
        $object->setLocaleTags(['test', 'test2'], locale: 'tr');
        $object->setLocaleTags(['test', 'test3'], locale: 'en');

        $this->assertEquals(4, $object->tags->count());
        $this->assertEquals(3, $object->allLocaleTags('en')->get()->count());
        $this->assertEquals(2, $object->allLocaleTags('tr')->get()->count());

        $this->assertEquals('test', $object->allLocaleTags('en')->get()[0]->name);
        $this->assertEquals('test2', $object->allLocaleTags('en')->get()[1]->name);
        $this->assertEquals('test3', $object->allLocaleTags('en')->get()[2]->name);

        $this->assertEquals('test', $object->allLocaleTags('tr')->get()[0]->name);
        $this->assertEquals('test2', $object->allLocaleTags('tr')->get()[1]->name);
    }

    public function test_where_locale_tag()
    {
        $object = LocaleTagsModel::create(['name' => 'Test Model']);
        $object2 = LocaleTagsModel::create(['name' => 'Test Model 2']);
        $object3 = LocaleTagsModel::create(['name' => 'Test Model 3']);
        $object4 = LocaleTagsModel::create(['name' => 'Test Model 4']);

        $object->setLocaleTags(['test', 'test2'], locale: 'en');
        $object2->setLocaleTags(['test', 'test2'], locale: 'tr');
        $object3->setLocaleTags(['test', 'test3'], locale: 'en');
        $object4->setLocaleTags(['test', 'test4'], locale: 'en');

        $this->assertEquals(3, LocaleTagsModel::whereLocaleTag('test', locale: 'en')->get()->count());
        $this->assertEquals(1, LocaleTagsModel::whereLocaleTag('test', locale: 'tr')->get()->count());
        $this->assertEquals(1, LocaleTagsModel::whereLocaleTag('test3', locale: 'en')->get()->count());
        $this->assertEquals(1, LocaleTagsModel::whereLocaleTag('test4', locale: 'en')->get()->count());
    }

    public function test_with_locale_tag()
    {
        $object = LocaleTagsModel::create(['name' => 'Test Model']);
        $object2 = LocaleTagsModel::create(['name' => 'Test Model 2']);
        $object3 = LocaleTagsModel::create(['name' => 'Test Model 3']);
        $object4 = LocaleTagsModel::create(['name' => 'Test Model 4']);

        $object->setLocaleTags(['test', 'test2'], locale: 'en');
        $object2->setLocaleTags(['test', 'test2'], locale: 'tr');
        $object3->setLocaleTags(['test', 'test3'], locale: 'en');
        $object4->setLocaleTags(['test', 'test4'], locale: 'en');

        $this->assertEquals(3, LocaleTagsModel::withLocaleTag(['test', 'test2', 'test3'], locale: 'en')->get()->count());
        $this->assertEquals(1, LocaleTagsModel::withLocaleTag(['test', 'test2'], locale: 'tr')->get()->count());
        $this->assertEquals(2, LocaleTagsModel::withLocaleTag(['test2', 'test3'], locale: 'en')->get()->count());
        $this->assertEquals(2, LocaleTagsModel::withLocaleTag(['test2', 'test4'], locale: 'en')->get()->count());
    }

    public function test_without_locale_tag()
    {
        $object = LocaleTagsModel::create(['name' => 'Test Model']);
        $object2 = LocaleTagsModel::create(['name' => 'Test Model 2']);
        $object3 = LocaleTagsModel::create(['name' => 'Test Model 3']);
        $object4 = LocaleTagsModel::create(['name' => 'Test Model 4']);

        $object->setLocaleTags(['test', 'test2'], locale: 'en');
        $object2->setLocaleTags(['test', 'test2'], locale: 'tr');
        $object3->setLocaleTags(['test', 'test3'], locale: 'en');
        $object4->setLocaleTags(['test', 'test4'], locale: 'en');

        $this->assertEquals(1, LocaleTagsModel::withoutLocaleTag(['test', 'test2'], locale: 'en')->get()->count());
        $this->assertEquals(3, LocaleTagsModel::withoutLocaleTag(['test', 'test2'], locale: 'tr')->get()->count());
    }

    public function test_locale_tags()
    {
        $object = LocaleTagsModel::create(['name' => 'Test Model']);
        $object->setLocaleTags(['test', 'test2'], locale: 'en');
        $object->setLocaleTags(['test', 'test2'], locale: 'tr');
        $object->setLocaleTags(['test', 'test3'], locale: 'en');

        $this->assertEquals(2, $object->localeTags('en')->get()->count());
        $this->assertEquals(2, $object->localeTags('tr')->get()->count());
        $this->assertEquals('test', $object->localeTags('en')->get()[0]->name);
        $this->assertEquals('test3', $object->localeTags('en')->get()[1]->name);
        $this->assertEquals('test', $object->localeTags('tr')->get()[0]->name);
        $this->assertEquals('test2', $object->localeTags('tr')->get()[1]->name);
    }

    public function test_locale_tags_list()
    {
        config(['translatable.locales' => ['en', 'tr']]);
        $object = LocaleTagsModel::create(['name' => 'Test Model']);
        $object->setLocaleTags(['test', 'test2'], locale: 'en');
        $object->setLocaleTags(['test', 'test2'], locale: 'tr');
        $object->setLocaleTags(['test', 'test3'], locale: 'en');

        $this->assertEquals(['test', 'test2', 'test3'], $object->localeTagsList()['en']->pluck('name')->toArray());
        $this->assertEquals(['test', 'test2'], $object->localeTagsList()['tr']->pluck('name')->toArray());
    }
}

class LocaleTagsModel extends Model
{
    use LocaleTags;

    protected static $loadLocalizedTags = true;

    protected $allowLocaleTagsFillable = true;

    protected $table = 'locale_tags_models';

    protected $fillable = ['name'];
}
