<?php

namespace Unusualify\Modularity\Tests\Repositories\Traits;

use Astrotomic\Translatable\Locales;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasSlug;
use Unusualify\Modularity\Entities\Traits\HasTranslation;
use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\SlugsTrait;
use Unusualify\Modularity\Repositories\Traits\TranslationsTrait;
use Unusualify\Modularity\Tests\Repositories\RepositorySources;
use Unusualify\Modularity\Tests\Repositories\TestModel;
use Unusualify\Modularity\Tests\Repositories\TestModelSlug;
use Unusualify\Modularity\Tests\RepositoryTestCase;

class SlugsTraitTest extends RepositoryTestCase
{
    use RefreshDatabase, RepositorySources;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();

        $this->repository = App::make(SlugsTestRepository::class, [new SlugsTestModel]);
    }

    public function test_after_save_slugs_trait_creates_slug_for_single_locale(): void
    {
        config(['translatable.locales' => ['en']]);

        // dd(
        //     get_class($this->repository),
        //     get_class($this->repository->getModel()),
        //     classHasTrait(SlugsTestModel::class, HasTranslation::class),
        //     classHasTrait(SlugsTestRepository::class, TranslationsTrait::class),
        // );
        $object = $this->repository->create([
            'name' => 'Test Model Name',
            'is_active' => true,
        ]);

        $object->refresh();
        $this->assertCount(1, $object->slugs);
        $this->assertEquals('test-model-name', $object->slugs->first()->slug);
        $this->assertEquals('en', $object->slugs->first()->locale);
        $this->assertTrue((bool) $object->slugs->first()->active);
    }

    public function test_after_save_slugs_trait_creates_slug_for_multiple_locales(): void
    {
        config(['translatable.locales' => ['en', 'tr']]);

        $object = $this->repository->create([
            'name' => 'Test Model Name',
            'is_active' => true,
        ]);

        $object->refresh();
        $this->assertCount(2, $object->slugs);

        $enSlug = $object->slugs->where('locale', 'en')->first();
        $trSlug = $object->slugs->where('locale', 'tr')->first();

        $this->assertEquals('test-model-name', $enSlug->slug);
        $this->assertEquals('test-model-name', $trSlug->slug);
    }

    public function test_after_save_slugs_trait_updates_slug_with_custom_value(): void
    {
        config(['translatable.locales' => ['en']]);

        $object = $this->repository->create([
            'name' => 'Test Model',
            'is_active' => true,
        ]);

        $this->repository->update($object->id, [
            'name' => 'Test Model Updated',
            'slugs' => ['en' => 'custom-slug'],
        ]);

        $object->refresh();
        $activeSlug = $object->slugs->where('active', true)->where('locale', 'en')->first();
        $this->assertEquals('custom-slug', $activeSlug->slug);
    }

    public function test_after_save_slugs_trait_disables_old_slugs_when_new_one_created(): void
    {
        config(['translatable.locales' => ['en']]);

        $object = $this->repository->create([
            'name' => 'First Slug',
            'is_active' => true,
        ]);

        $this->repository->update($object->id, [
            'name' => 'First Slug',
            'slugs' => ['en' => 'second-slug'],
        ]);

        $object->refresh();
        $this->assertCount(2, $object->slugs);

        $activeSlug = $object->slugs->where('active', true)->first();
        $inactiveSlug = $object->slugs->where('active', false)->first();

        $this->assertEquals('second-slug', $activeSlug->slug);
        $this->assertEquals('first-slug', $inactiveSlug->slug);
    }

    public function test_after_delete_slugs_trait_soft_deletes_slugs(): void
    {
        config(['translatable.locales' => ['en']]);

        $object = $this->repository->create([
            'name' => 'To Be Deleted',
            'is_active' => true,
        ]);

        $slugId = $object->slugs->first()->id;

        $this->repository->delete($object->id);

        $this->assertSoftDeleted('test_model_slugs', ['id' => $slugId]);
    }

    public function test_after_restore_slugs_trait_restores_slugs(): void
    {
        config(['translatable.locales' => ['en']]);

        $object = $this->repository->create([
            'name' => 'To Be Restored',
            'is_active' => true,
        ]);

        $slugId = $object->slugs->first()->id;

        $this->repository->delete($object->id);
        $this->assertSoftDeleted('test_model_slugs', ['id' => $slugId]);

        $this->repository->restore($object->id);

        $restoredSlug = TestModelSlug::find($slugId);
        $this->assertNotNull($restoredSlug);
        $this->assertNull($restoredSlug->deleted_at);
    }

    public function test_get_form_fields_slugs_trait_returns_active_slug(): void
    {
        config(['translatable.locales' => ['en']]);

        $object = $this->repository->create([
            'name' => 'Form Field Test',
            'is_active' => true,
        ]);

        $fields = $this->repository->getFormFields($object);

        $this->assertArrayHasKey('translations', $fields);
        $this->assertArrayHasKey('name', $fields['translations']);
        $this->assertEquals('form-field-test', $fields['translations']['name']['en']);
    }

    public function test_get_form_fields_slugs_trait_returns_slugs_for_multiple_locales(): void
    {
        config(['translatable.locales' => ['en', 'tr']]);

        $object = $this->repository->create([
            'name' => 'Multi Locale Test',
            'is_active' => true,
        ]);

        $fields = $this->repository->getFormFields($object);

        $this->assertArrayHasKey('name', $fields['translations']);
        $this->assertEquals('multi-locale-test', $fields['translations']['name']['en']);
        $this->assertEquals('multi-locale-test', $fields['translations']['name']['tr']);
    }

    public function test_get_slug_parameters_returns_base_slug_params(): void
    {
        config(['translatable.locales' => ['en']]);

        $object = $this->repository->create([
            'name' => 'Slug Params Test',
            'is_active' => true,
        ]);

        $mockRepository = \Mockery::mock(SlugsTestRepository::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mockRepository->shouldReceive('getSlugParams')->andReturn([
            'active' => 1,
            'slug' => 'slug-params-test',
            'locale' => 'en',
            'name' => 'slug-params-test',
        ]);

        $slug = [
            'slug' => 'custom-slug',
            'locale' => 'en',
            'active' => true,
        ];

        $result = $mockRepository->getSlugParameters($object, ['name' => 'Updated Name'], $slug);

        $this->assertArrayHasKey('slug', $result);
        $this->assertArrayHasKey('locale', $result);
        $this->assertArrayHasKey('active', $result);
        $this->assertEquals('custom-slug', $result['slug']);
        $this->assertEquals('en', $result['locale']);
        $this->assertTrue($result['active']);

        $result = $mockRepository->getSlugParameters($object, [], $slug);

        $this->assertArrayHasKey('slug', $result);
        $this->assertArrayHasKey('locale', $result);
        $this->assertArrayHasKey('active', $result);
        $this->assertEquals('slug-params-test', $result['name']);
        $this->assertEquals('custom-slug', $result['slug']);
        $this->assertEquals('en', $result['locale']);
        $this->assertTrue($result['active']);
    }

    public function test_exists_slug_preview_returns_model_by_inactive_slug(): void
    {
        config(['translatable.locales' => ['en']]);
        app()->setLocale('en');

        $object = $this->repository->create([
            'name' => 'Preview Test',
            'is_active' => true,
        ]);

        // Create new slug making old one inactive
        $this->repository->update($object->id, [
            'name' => 'Preview Test',
            'slugs' => ['en' => 'new-preview-slug'],
        ]);

        $found = $this->repository->existsSlugPreview('preview-test');

        $this->assertNotNull($found);
        $this->assertEquals($object->id, $found->id);
    }

    public function test_exists_slug_preview_returns_model_by_active_slug(): void
    {
        config(['translatable.locales' => ['en']]);
        app()->setLocale('en');

        $object = $this->repository->create([
            'name' => 'Active Preview Test',
            'is_active' => true,
        ]);

        $found = $this->repository->existsSlugPreview('active-preview-test');

        $this->assertNotNull($found);
        $this->assertEquals($object->id, $found->id);
    }

    public function test_exists_slug_preview_returns_null_for_non_existent(): void
    {
        $found = $this->repository->existsSlugPreview('non-existent-slug');

        $this->assertNull($found);
    }

    public function test_slug_is_generated_from_slug_attributes(): void
    {
        config(['translatable.locales' => ['en']]);

        $object = $this->repository->create([
            'name' => 'Attribute Based Slug',
            'is_active' => true,
        ]);

        $object->refresh();
        $this->assertEquals('attribute-based-slug', $object->slugs->first()->slug);
    }

    public function test_duplicate_slugs_get_suffix(): void
    {
        config(['translatable.locales' => ['en']]);

        $object1 = $this->repository->create([
            'name' => 'Duplicate Slug',
            'is_active' => true,
        ]);

        $object2 = $this->repository->create([
            'name' => 'Duplicate Slug',
            'is_active' => true,
        ]);

        $object1->refresh();
        $object2->refresh();

        $this->assertEquals('duplicate-slug', $object1->slugs->first()->slug);
        $this->assertEquals('duplicate-slug-2', $object2->slugs->first()->slug);
    }

    public function test_get_active_slug_returns_correct_locale_slug(): void
    {
        config(['translatable.locales' => ['en', 'tr']]);
        app()->setLocale('tr');

        $object = $this->repository->create([
            'name' => 'Locale Slug Test',
            'is_active' => true,
        ]);

        // Update with custom Turkish slug
        $this->repository->update($object->id, [
            'name' => 'Locale Slug Test',
            'slugs' => ['tr' => 'turkce-slug'],
        ]);

        $object->refresh();
        $activeSlug = $object->getActiveSlug('tr');

        $this->assertNotNull($activeSlug);
        $this->assertEquals('turkce-slug', $activeSlug->slug);
    }

    public function test_get_slug_attribute_returns_active_slug_string(): void
    {
        config(['translatable.locales' => ['en']]);
        app()->setLocale('en');

        $object = $this->repository->create([
            'name' => 'Slug Attribute Test',
            'is_active' => true,
        ]);

        $object->refresh();

        $this->assertEquals('slug-attribute-test', $object->slug);
    }

    public function test_scope_exists_slug_filters_by_active_slug_and_locale(): void
    {
        app()->setLocale('en');
        config(['translatable.locales' => ['en', 'tr']]);

        config(['translatable.locales' => ['en', 'tr']]);
        app()->forgetInstance(Locales::class);
        app()->make(Locales::class); // Force new instance now

        $object = $this->repository->create([
            'name' => 'Scope For Slug Test',
            'is_active' => true,
            'published' => true,
            'public' => true,
            'publish_start_date' => now(),
            'publish_end_date' => now()->addDay(),

            'context' => [
                'en' => 'Scope For Slug Test Content English',
                'tr' => 'Scope For Slug Test Content Turkish',
            ],
            'active' => [
                'en' => true,
                'tr' => true,
            ],
        ]);

        $item = $this->repository->existsSlug('scope-for-slug-test');

        $this->assertNotNull($item);
        $this->assertEquals($object->id, $item->id);

        config(['translatable.use_property_fallback' => true]);
        config(['translatable.fallback_locale' => 'tr']);

        $this->repository->update($object->id, [
            'published' => false,
            'slugs' => ['en' => [
                'slug' => 'scope-for-slug-test',
            ], 'tr' => [
                'slug' => 'scope-for-slug-test',
                'active' => true,
            ]],
        ]);
        $item->refresh();

        $item = $this->repository->existsSlug('scope-for-slug-test');

        $this->assertNotNull($item);
        $this->assertEquals($object->id, $item->id);

        $object->slugs()->where('locale', 'en')->delete();

        $item = $this->repository->existsSlug('scope-for-slug-test');

        $this->assertEquals($object->id, $item->id);
        $this->assertEquals('scope-for-slug-test', $item->slugs->where('locale', 'tr')->first()->slug);
        $this->assertNull($item->slugs->where('locale', 'en')->first());
    }

    public function test_scope_for_inactive_slug_includes_inactive_slugs(): void
    {
        config(['translatable.locales' => ['en']]);
        app()->setLocale('en');

        $object = $this->repository->create([
            'name' => 'Inactive Scope Test',
            'is_active' => true,
        ]);

        // Create new slug making old one inactive
        $this->repository->update($object->id, [
            'name' => 'Inactive Scope Test',
            'slug' => ['en' => 'new-active-slug'],
        ]);

        $query = SlugsTestModel::existsInactiveSlug('inactive-scope-test');
        $found = $query->first();

        $this->assertNotNull($found);
        $this->assertEquals($object->id, $found->id);
    }

    public function test_utf8_slug_generation(): void
    {
        config(['translatable.locales' => ['tr']]);
        config(['modularity.slug_utf8_languages' => ['tr']]);
        app()->setLocale('tr');

        $object = $this->repository->create([
            'name' => 'Türkçe İçerik Öğesi',
            'is_active' => true,
        ]);

        $object->refresh();

        // UTF8 slug should handle Turkish characters
        $this->assertNotEmpty($object->slugs->first()->slug);
    }

    public function test_slugs_table_returns_correct_table_name(): void
    {
        $object = $this->repository->create([
            'name' => 'Table Name Test',
            'is_active' => true,
        ]);

        $this->assertEquals('test_model_slugs', $object->getSlugsTable());
    }

    public function test_get_foreign_key_returns_correct_key(): void
    {
        $object = $this->repository->create([
            'name' => 'Foreign Key Test',
            'is_active' => true,
        ]);

        $this->assertEquals('test_model_id', $object->getForeignKey());
    }

    public function test_get_existing_slug_finds_matching_slug(): void
    {
        config(['translatable.locales' => ['en']]);

        $object = $this->repository->create([
            'name' => 'Existing Slug Test',
            'is_active' => true,
        ]);

        $object->refresh();

        $existingSlug = $object->getExistingSlug([
            'slug' => 'existing-slug-test',
            'locale' => 'en',
        ]);

        $this->assertNotNull($existingSlug);
        $this->assertEquals('existing-slug-test', $existingSlug->slug);
    }

    public function test_get_existing_slug_returns_null_for_non_matching(): void
    {
        config(['translatable.locales' => ['en']]);

        $object = $this->repository->create([
            'name' => 'Some Slug',
            'is_active' => true,
        ]);

        $object->refresh();

        $existingSlug = $object->getExistingSlug([
            'slug' => 'completely-different-slug',
            'locale' => 'en',
        ]);

        $this->assertNull($existingSlug);
    }

    public function test_get_fallback_active_slug(): void
    {
        config(['translatable.locales' => ['en', 'tr']]);
        config(['translatable.fallback_locale' => 'en']);

        $object = $this->repository->create([
            'name' => 'Fallback Test',
            'is_active' => true,
        ]);

        $object->refresh();

        $fallbackSlug = $object->getFallbackActiveSlug();

        $this->assertNotNull($fallbackSlug);
        $this->assertEquals('en', $fallbackSlug->locale);
        $this->assertEquals('fallback-test', $fallbackSlug->slug);
    }

    public function test_get_slug_returns_empty_string_when_no_active_slug(): void
    {
        config(['translatable.locales' => ['en']]);
        config(['translatable.use_property_fallback' => false]);
        app()->setLocale('tr'); // Set to a locale that has no slug

        $object = $this->repository->create([
            'name' => 'No Slug For Locale',
            'is_active' => true,
        ]);

        $object->refresh();

        // Try to get slug for a locale that doesn't exist
        $slug = $object->getSlug('fr');

        $this->assertEquals('', $slug);
    }

    public function test_slugs_relationship_returns_has_many(): void
    {
        config(['translatable.locales' => ['en']]);

        $object = $this->repository->create([
            'name' => 'Relationship Test',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(
            HasMany::class,
            $object->slugs()
        );
    }

    public function test_scope_exists_fallback_locale_slug(): void
    {
        config(['translatable.locales' => ['en', 'tr']]);
        config(['translatable.fallback_locale' => 'en']);
        app()->setLocale('en');

        $object = $this->repository->create([
            'name' => 'Fallback Locale Scope Test',
            'is_active' => true,
        ]);

        $query = SlugsTestModel::existsFallbackLocaleSlug('fallback-locale-scope-test');
        $found = $query->first();

        $this->assertNotNull($found);
        $this->assertEquals($object->id, $found->id);
    }

    public function test_reactivate_existing_slug_when_matching(): void
    {
        config(['translatable.locales' => ['en']]);

        $object = $this->repository->create([
            'name' => 'Original Slug',
            'is_active' => true,
        ]);

        // Create a new slug, making original inactive
        $this->repository->update($object->id, [
            'name' => 'Original Slug',
            'slugs' => ['en' => 'new-slug'],
        ]);

        $object->refresh();
        $this->assertCount(2, $object->slugs);

        // Now update back to original slug
        $this->repository->update($object->id, [
            'name' => 'Original Slug',
            'slugs' => ['en' => 'original-slug'],
        ]);

        $object->refresh();

        // Should reactivate existing slug instead of creating new one
        $activeSlug = $object->slugs->where('active', true)->first();
        $this->assertEquals('original-slug', $activeSlug->slug);
    }

    public function test_get_form_fields_removes_slugs_key(): void
    {
        config(['translatable.locales' => ['en']]);

        $object = $this->repository->create([
            'name' => 'Fields Test',
            'is_active' => true,
        ]);

        $fields = $this->repository->getFormFields($object);

        // The raw 'slugs' key should be removed; slug data is under translations.{slugAttribute}
        $this->assertArrayNotHasKey('slugs', $fields);
        $this->assertArrayHasKey('translations', $fields);
        $this->assertArrayHasKey('name', $fields['translations']);
    }
}

class SlugsTestModel extends TestModel
{
    use HasTranslation, HasSlug;

    protected $table = 'test_models';

    protected $slugModelClass = TestModelSlug::class;

    protected $slugForeignKey = 'test_model_id';

    /**
     * The slug attributes that are assignable for hasSlug Trait.
     *
     * @var array<int, string>
     */
    protected $slugAttributes = [
        'name',
    ];

    protected $translationModel = SlugsTestModelTranslation::class;

    public $translationForeignKey = 'test_model_id';

    protected $translatedAttributes = ['context', 'active'];
}

class SlugsTestModelTranslation extends Model
{
    protected $table = 'test_model_repo_translations';

    protected $baseModuleModel = SlugsTestModel::class;
}

class SlugsTestRepository extends Repository
{
    use TranslationsTrait, SlugsTrait;

    public function __construct(SlugsTestModel $model)
    {
        $this->model = $model;
    }
}
