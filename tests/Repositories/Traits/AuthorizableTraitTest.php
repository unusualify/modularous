<?php

namespace Unusualify\Modularity\Tests\Repositories\Traits;

use Illuminate\Support\Facades\App;
use Mockery\MockInterface;
use Unusualify\Modularity\Tests\Repositories\RepositorySources;
use Unusualify\Modularity\Tests\RepositoryTestCase;

class AuthorizableTraitTest extends RepositoryTestCase
{
    use RepositorySources;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();

        $this->repository = App::make(AuthorizableTestRepository::class);
    }

    public function test_get_table_filters_authorizable_trait_returns_expected_filters()
    {
        $filters = $this->repository->getTableFiltersAuthorizableTrait([]);

        $slugs = array_column($filters, 'slug');
        $this->assertContains('authorized', $slugs);
        $this->assertContains('unauthorized', $slugs);
        $this->assertContains('your-authorizations', $slugs);

        $mock = $this->partialMock(AuthorizableTestRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('getModel')->andReturn(new class
            {
                public function hasAuthorizationUsage()
                {
                    return false;
                }
            });
        });

        $filters = $mock->getTableFilters([]);

        $this->assertCount(1, $filters);
        $slugs = array_column($filters, 'slug');
        $this->assertContains('your-authorizations', $slugs);
        $this->assertNotContains('authorized', $slugs);
        $this->assertNotContains('unauthorized', $slugs);
    }
}

class TestModelAuthorizable extends \Unusualify\Modularity\Tests\Repositories\TestModel
{
    public function hasAuthorizationUsage()
    {
        return true;
    }
}

class AuthorizableTestRepository extends \Unusualify\Modularity\Tests\Repositories\TestRepository
{
    use \Unusualify\Modularity\Repositories\Traits\AuthorizableTrait;

    public function __construct(TestModelAuthorizable $model)
    {
        $this->model = $model;
    }
}
