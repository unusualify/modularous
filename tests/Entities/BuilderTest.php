<?php

namespace Unusualify\Modularous\Tests\Entities;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Entities\Builder;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Tests\ModelTestCase;

class BuilderTest extends ModelTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_batch_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        TestBatchModel::query()->insert([
            ['name' => 'First'],
            ['name' => 'Second'],
        ]);

        TestBatchModel::$log = [];
    }

    public function test_models_use_the_modularous_builder()
    {
        $this->assertInstanceOf(Builder::class, TestBatchModel::query());
    }

    public function test_batch_hooks_run_before_deferred_retrieved_events()
    {
        $models = TestBatchModel::query()->orderBy('id')->get();

        $this->assertCount(2, $models);
        $this->assertSame(['batch:2', 'retrieved:1:prepared', 'retrieved:2:prepared'], TestBatchModel::$log);
    }

    public function test_single_rows_and_cursors_fire_retrieved_immediately()
    {
        TestBatchModel::query()->find(1);
        TestBatchModel::query()->orderBy('id')->cursor()->each(fn () => null);

        $this->assertSame(['retrieved:1', 'retrieved:1', 'retrieved:2'], TestBatchModel::$log);
    }
}

trait TracksRetrievedBatch
{
    public static array $log = [];

    public bool $batchPrepared = false;

    public static function bootTracksRetrievedBatch(): void
    {
        static::retrieved(function (Model $model) {
            static::$log[] = 'retrieved:' . $model->id . ($model->batchPrepared ? ':prepared' : '');
        });
    }

    public function retrievedBatchTracksRetrievedBatch(Collection $models): void
    {
        static::$log[] = 'batch:' . $models->count();

        $models->each(fn ($model) => $model->batchPrepared = true);
    }
}

class TestBatchModel extends Model
{
    use TracksRetrievedBatch;

    protected $table = 'test_batch_models';

    protected $fillable = ['name'];
}
