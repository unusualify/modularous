<?php

namespace Unusualify\Modularous\Tests\Repositories;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Modules\SystemPricing\Entities\Currency;
use Modules\SystemPricing\Entities\PriceType;
use Modules\SystemPricing\Entities\VatRate;

trait RepositorySources
{
    use RefreshDatabase;

    protected $repository;

    protected $laravelRepository;

    public function loadRepositorySources()
    {
        Schema::create('owners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // $table->foreignId('owner_id')->constrained('owners');
            $table->unsignedBigInteger('owner_id')->nullable(); // Foreign key column

            $table->unsignedBigInteger('test_modelable_id')->nullable();
            $table->string('test_modelable_type')->nullable();

            $table->string('description')->nullable();
            $table->boolean('published')->nullable();
            $table->boolean('public')->nullable();
            $table->timestamp('publish_start_date')->nullable();
            $table->timestamp('publish_end_date')->nullable();
            $table->boolean('is_active');
            $table->integer('position')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('test_model_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'test_model');
        });

        Schema::create('test_model_repo_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, 'test_model');
            $table->string('context')->nullable();
        });

        Schema::create('test_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('position')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('test_model_test_role', function (Blueprint $table) {
            $table->foreignId('test_model_id')
                ->constrained('test_models')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreignId('test_role_id')
                ->constrained('test_roles')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->primary(['test_model_id', 'test_role_id']);
        });

        Schema::create('first_morphs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('second_morphs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('laravel_test_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_model_id')->constrained('test_models')->onDelete('cascade');
            $table->unsignedBigInteger('external_id')->nullable();
            $table->text('content')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->uuidMorphs('postable');
            $table->integer('position')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('post_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, 'post');
            $table->string('title');
            $table->text('content');
        });

        $this->repository = App::make(TestRepository::class);

        $this->laravelRepository = App::make(LaravelTestRepository::class);

        PriceType::updateOrCreate([
            'name' => 'Default Price Type',
        ], [
            'name' => 'Default Price Type',
        ]);

        VatRate::updateOrCreate([
            'name' => 'Standard',
        ], [
            'rate' => 20,
        ]);

        Currency::updateOrCreate([
            'iso_4217' => 'EUR',
        ], [
            'name' => 'Euro',
            'symbol' => '€',
            'iso_4217_number' => 978,
        ]);

        Currency::updateOrCreate([
            'iso_4217' => 'USD',
        ], [
            'name' => 'US Dollar',
            'symbol' => '$',
            'iso_4217_number' => 840,
        ]);
    }

    /**
     * Seed owners and test models for filter tests.
     */
    protected function seedFilterFixtures(): void
    {
        $o1 = Owner::create(['name' => 'Owner A']);
        $o2 = Owner::create(['name' => 'Owner B']);

        $o1->posts()->create([
            'en' => [
                'title' => 'Post of Owner A',
                'content' => 'Content of Post of Owner A',
            ],
        ]);
        $o2->posts()->create([
            'en' => [
                'title' => 'Post of Owner B',
                'content' => 'Content of Post of Owner B',
            ],
        ]);

        $tm1 = TestModel::create([
            'name' => 'Alice',
            'owner_id' => $o1->id,
            'is_active' => true,
            'description' => 'hello',
            'en' => [
                'context' => 'Alice Context',
            ],
        ]);
        $tm2 = TestModel::create([
            'name' => 'Alice B',
            'owner_id' => $o1->id,
            'is_active' => false,
            'description' => 'world',
            // 'en' => [
            //     'context' => 'Alice B Context',
            // ],
        ]);
        $tm3 = TestModel::create([
            'name' => 'Bob',
            'owner_id' => $o2->id,
            'is_active' => true,
            'description' => 'hello',
            // 'en' => [
            //     'context' => 'Bob Context',
            // ],
        ]);
        $tm4 = TestModel::create([
            'name' => 'Carla',
            'owner_id' => $o2->id,
            'is_active' => true,
            'description' => 'abc',
            // 'en' => [
            //     'context' => 'Carla Context',
            // ],
        ]);
        $tm5 = TestModel::create([
            'name' => 'John',
            'owner_id' => $o1->id,
            'is_active' => false,
            'description' => null,
            // 'en' => [
            //     'context' => 'John Context',
            // ],
        ]);

        $tm1->notes()->create([
            'content' => 'Note of Test Model 1',
        ]);
        $tm2->notes()->create([
            'content' => 'Note of Test Model 2',
        ]);
        $tm3->notes()->create([
            'content' => 'Note of Test Model 3',
        ]);
        $tm4->notes()->create([
            'content' => 'Note of Test Model 4',
        ]);
        $tm5->notes()->create([
            'content' => 'Note of Test Model 5',
        ]);

        $tm1->posts()->create([
            'en' => [
                'title' => 'Post of Test Model 1',
                'content' => 'Content of Post of Test Model 1',
            ],
        ]);
        $tm2->posts()->create([
            'en' => [
                'title' => 'Post of Test Model 2',
                'content' => 'Content of Post of Test Model 2',
            ],
        ]);
        $tm3->posts()->create([
            'en' => [
                'title' => 'Post of Test Model 3',
                'content' => 'Content of Post of Test Model 3',
            ],
        ]);
        $tm4->posts()->create([
            'en' => [
                'title' => 'Post of Test Model 4',
                'content' => 'Content of Post of Test Model 4',
            ],
        ]);
        $tm5->posts()->create([
            'en' => [
                'title' => 'Post of Test Model 5',
                'content' => 'Content of Post of Test Model 5',
            ],
        ]);
    }
}
