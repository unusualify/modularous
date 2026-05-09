<?php

namespace Unusualify\Modularous\Tests\Repositories\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Traits\HasTranslation;
use Unusualify\Modularous\Repositories\Traits\TranslationsTrait;
use Unusualify\Modularous\Tests\Repositories\RepositorySources;
use Unusualify\Modularous\Tests\Repositories\TestModel;
use Unusualify\Modularous\Tests\Repositories\TestRepository;
use Unusualify\Modularous\Tests\RepositoryTestCase;

class TranslationsTraitTest extends RepositoryTestCase
{
    use RefreshDatabase, RepositorySources;

    protected function setUp(): void
    {
        parent::setUp();

        config(['translatable.locales' => ['en', 'tr']]);

        $this->loadRepositorySources();

        Schema::create('translations_test_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('owner_id')->nullable(); // Foreign key column
            $table->string('description')->nullable();
            $table->boolean('is_active');
            $table->integer('position')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('translations_test_model_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translations_test_model_id')->constrained('translations_test_models')->onDelete('cascade');
            $table->string('locale');
            $table->string('title');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        $this->repository = App::make(TranslationsTestRepository::class);
    }

    public function test_set_columns_translations_trait_sets_translated_columns()
    {
        $schema = [
            'title' => [
                'type' => 'text',
                'name' => 'title',
                'label' => 'Title',
                'translated' => true,
            ],
            'active' => [
                'type' => 'checkbox',
                'name' => 'active',
                'label' => 'Active',
                'translated' => true,
            ],
        ];

        $this->repository->setColumns($schema);
        $columns = $this->repository->getColumns('Unusualify\Modularous\Repositories\Traits\TranslationsTrait');

        $this->assertContains('title', $columns);
        $this->assertContains('active', $columns);
    }

    public function test_prepare_fields_before_save_translations_trait_restructures_translated_fields()
    {
        $fields = [
            'translations' => [
                'title' => ['en' => 'Hello', 'tr' => 'Merhaba'],
                'active' => ['en' => true, 'tr' => false],
            ],
            'translationLanguages' => [
                ['value' => 'en', 'published' => true],
                ['value' => 'tr', 'published' => false],
            ],
            'title' => ['en' => 'Hello', 'tr' => 'Merhaba'],
        ];

        // Use repository with real TestModel/TestModelTranslation
        // Fake translated attributes via model property for this test
        $result = $this->repository->prepareFieldsBeforeSaveTranslationsTrait(null, $fields);

        $this->assertArrayHasKey('en', $result);
        $this->assertArrayHasKey('tr', $result);
        $this->assertSame('Hello', $result['en']['title']);
        $this->assertSame('Merhaba', $result['tr']['title']);
        $this->assertArrayNotHasKey('translationLanguages', $result);
        $this->assertArrayNotHasKey('title', $result);
        $this->assertArrayNotHasKey('content', $result);
    }

    public function test_get_form_fields_translations_trait_maps_model_translations_into_fields()
    {
        // Create base model row
        $model = $this->repository->create(['name' => 'Mapped', 'title' => ['en' => 'Hello', 'tr' => 'Merhaba'], 'active' => ['en' => true, 'tr' => false]]);
        // Reload with translations relation
        $object = $this->repository->getModel()->with('translations')->find($model->id);

        $fields = [];

        $mapped = $this->repository->getFormFieldsTranslationsTrait($object, $fields);

        // Expect translations array keyed by attribute then locale
        $this->assertArrayHasKey('translations', $mapped);
        $this->assertArrayHasKey('title', $mapped['translations']);
        $this->assertArrayHasKey('active', $mapped['translations']);
        $this->assertSame('Hello', $mapped['translations']['title']['en']);
        $this->assertSame('Merhaba', $mapped['translations']['title']['tr']);
        $this->assertEquals(1, $mapped['translations']['active']['en']);
        $this->assertEquals(0, $mapped['translations']['active']['tr']);
    }

    public function test_filter_translations_trait_searches_by_translated_field()
    {
        // Create base models
        $a = $this->repository->create([
            'name' => 'A',
            'title' => ['en' => 'Hello', 'tr' => 'Merhaba'],
            'active' => ['en' => true, 'tr' => true],
        ]);
        $b = $this->repository->create([
            'name' => 'B',
            'title' => ['en' => 'World', 'tr' => 'Dünya'],
            'active' => ['en' => true, 'tr' => true],
        ]);

        // Insert translations
        // DB::table('translations_test_model_translations')->insert([
        //     ['translations_test_model_id' => $a->id, 'locale' => 'en', 'title' => 'Hello', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        //     ['translations_test_model_id' => $b->id, 'locale' => 'en', 'title' => 'World', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        // ]);

        $query = $this->repository->newQuery();

        $scopes = [
            'searches' => ['title'],
            'title' => 'Hello',
        ];

        $result = $this->repository->filter($query, $scopes)->get();

        $this->assertCount(1, $result);
        $this->assertEquals($a->id, $result->first()->id);
    }

    public function test_order_translations_trait_orders_by_translated_field_and_locale()
    {
        $x = $this->repository->create(['name' => 'X']);
        $y = $this->repository->create(['name' => 'Y']);

        DB::table('translations_test_model_translations')->insert([
            ['translations_test_model_id' => $x->id, 'locale' => 'en', 'title' => 'Beta', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['translations_test_model_id' => $y->id, 'locale' => 'en', 'title' => 'Alpha', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $query = $this->repository->newQuery();
        $ordered = $this->repository->order($query, ['title' => 'asc', 'locale' => 'en'])->get();
        $this->assertEquals([$y->id, $x->id], $ordered->pluck('id')->toArray());
    }

    public function test_get_published_scopes_translations_trait()
    {
        $this->assertSame(['withActiveTranslations'], $this->repository->getPublishedScopesTranslationsTrait());
    }
}

class TranslationsTestModel extends TestModel
{
    use HasTranslation;

    protected $table = 'translations_test_models';

    protected $translationModel = TranslationsTestModelTranslation::class;

    public $translationForeignKey = 'translations_test_model_id';

    protected $translatedAttributes = ['title', 'active'];
}

class TranslationsTestModelTranslation extends Model
{
    protected $table = 'translations_test_model_translations';

    protected $baseModuleModel = TranslationsTestModel::class;
}

class TranslationsTestRepository extends TestRepository
{
    use TranslationsTrait;

    public function __construct(TranslationsTestModel $model)
    {
        $this->model = $model;
    }
}
