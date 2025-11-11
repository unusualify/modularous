<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

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

            $table->string('description')->nullable();
            $table->boolean('is_active');
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

        Schema::create('test_roles', function (Blueprint $table) {
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
            $table->unsignedBigInteger('external_id');
            $table->timestamps();
        });

        $this->repository = App::make(TestRepository::class);

        $this->laravelRepository = App::make(LaravelTestRepository::class);

        \Modules\SystemPricing\Entities\PriceType::updateOrCreate([
            'name' => 'Default Price Type',
        ], [
            'name' => 'Default Price Type',
        ]);

        \Modules\SystemPricing\Entities\VatRate::updateOrCreate([
            'name' => 'Standard',
        ], [
            'rate' => 20,
        ]);

        \Modules\SystemPricing\Entities\Currency::updateOrCreate([
            'iso_4217' => 'EUR',
        ], [
            'name' => 'Euro',
            'symbol' => '€',
            'iso_4217_number' => 978,
        ]);

        \Modules\SystemPricing\Entities\Currency::updateOrCreate([
            'iso_4217' => 'USD',
        ], [
            'name' => 'US Dollar',
            'symbol' => '$',
            'iso_4217_number' => 840,
        ]);
    }
}
