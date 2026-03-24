<?php

namespace Unusualify\Modularity\Tests\Entities\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularity\Entities\Observers\CacheObserver;
use Unusualify\Modularity\Tests\TestCase;

class CacheObserverTest extends TestCase
{
    protected CacheObserver $observer;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('modularity.cache.enabled', false);

        $this->observer = new CacheObserver;
    }

    public function test_created_does_not_throw_when_cache_disabled()
    {
        $model = $this->createTestModel();

        $this->observer->created($model);

        $this->assertTrue(true);
    }

    public function test_updated_does_not_throw_when_cache_disabled()
    {
        $model = $this->createTestModel();

        $this->observer->updated($model);

        $this->assertTrue(true);
    }

    public function test_deleted_does_not_throw_when_cache_disabled()
    {
        $model = $this->createTestModel();

        $this->observer->deleted($model);

        $this->assertTrue(true);
    }

    public function test_restored_does_not_throw_when_cache_disabled()
    {
        $model = $this->createTestModel();

        $this->observer->restored($model);

        $this->assertTrue(true);
    }

    public function test_force_deleted_does_not_throw_when_cache_disabled()
    {
        $model = $this->createTestModel();

        $this->observer->forceDeleted($model);

        $this->assertTrue(true);
    }

    private function createTestModel(): Model
    {
        $model = new class extends Model
        {
            protected $table = 'test_models';

            public function getKey()
            {
                return 1;
            }
        };
        $model->setRawAttributes(['id' => 1]);

        return $model;
    }
}
