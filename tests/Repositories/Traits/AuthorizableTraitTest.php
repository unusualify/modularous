<?php

namespace Unusualify\Modularous\Tests\Repositories\Traits;

use Illuminate\Support\Facades\App;
use Mockery\MockInterface;
use Unusualify\Modularous\Entities\Traits\HasAuthorizable;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Repositories\Traits\AuthorizableTrait;
use Unusualify\Modularous\Tests\Repositories\RepositorySources;
use Unusualify\Modularous\Tests\Repositories\TestModel;
use Unusualify\Modularous\Tests\Repositories\TestRepository;
use Unusualify\Modularous\Tests\RepositoryTestCase;

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

    public function test_get_form_fields_authorizable_trait_returns_expected_fields()
    {
        $user = User::create([
            'name' => 'Authorized User',
            'email' => 'authorized@example.com',
            'published' => true,
        ]);

        $object = $this->repository->create([
            'name' => 'Test Model Authorizable',
            'authorized_id' => $user->id,
            'authorized_type' => get_class($user),
        ]);
        $object = $this->repository->getById($object->id);

        $fields = $this->repository->getFormFieldsAuthorizableTrait($object, [], [
            'authorized_id' => [
                'type' => 'input',
                'name' => 'authorized_id',
            ],
        ]);

        $this->assertArrayHasKey('authorized_id', $fields);
        $this->assertArrayHasKey('authorized_type', $fields);
    }
}

class TestModelAuthorizable extends TestModel
{
    use HasAuthorizable;

    public function hasAuthorizationUsage()
    {
        return true;
    }
}

class AuthorizableTestRepository extends TestRepository
{
    use AuthorizableTrait;

    public function __construct(TestModelAuthorizable $model)
    {
        $this->model = $model;
    }
}
