<?php

namespace Unusualify\Modularity\Tests\Repositories\Traits;

use Illuminate\Support\Facades\App;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularity\Tests\Repositories\RepositorySources;
use Unusualify\Modularity\Tests\RepositoryTestCase;

class CreatorTraitTest extends RepositoryTestCase
{
    use RefreshDatabase, RepositorySources;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();

        $this->repository = App::make(CreatorTestRepository::class);
    }

    public function test_filter_creator_trait_sets_scope_flag(): void
    {
        $scopes = [];
        $this->repository->filterCreatorTrait(null, $scopes);

        $this->assertArrayHasKey('hasAccessToCreation', $scopes);
        $this->assertTrue($scopes['hasAccessToCreation']);
    }

    public function test_get_form_fields_sets_custom_creator_id_when_creator_exists_without_roles(): void
    {
        $repo = new class {
            use \Unusualify\Modularity\Repositories\Traits\CreatorTrait;
        };

        $object = new class {
            public $creator;
            public function __construct() { $this->creator = (object) ['id' => 77]; }
            public function creator() { return new class { public function exists() { return true; } }; }
        };

        $schema = [
            'custom_creator_id' => [
                'type' => 'creator',
                'name' => 'custom_creator_id',
            ],
        ];

        $fields = [];
        $mapped = $repo->getFormFieldsCreatorTrait($object, $fields, $schema);

        $this->assertArrayHasKey('custom_creator_id', $mapped);
        $this->assertSame(77, $mapped['custom_creator_id']);
    }

    public function test_get_form_fields_respects_allowed_roles_and_sets_id_when_authorized(): void
    {
        $repo = new class {
            use \Unusualify\Modularity\Repositories\Traits\CreatorTrait;
        };

        // Auth user with role
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn(new class {
            public function hasRole($roles) { return in_array('admin', (array) $roles, true); }
        });

        $object = new class {
            public $creator;
            public function __construct() { $this->creator = (object) ['id' => 99]; }
            public function creator() { return new class { public function exists() { return true; } }; }
        };

        $schema = [
            'custom_creator_id' => [
                'type' => 'creator',
                'name' => 'custom_creator_id',
                'allowedRoles' => ['admin', 'editor'],
            ],
        ];

        $mapped = $repo->getFormFieldsCreatorTrait($object, [], $schema);
        $this->assertSame(99, $mapped['custom_creator_id']);
    }

    public function test_get_form_fields_respects_allowed_roles_and_skips_when_unauthorized(): void
    {
        $repo = new class {
            use \Unusualify\Modularity\Repositories\Traits\CreatorTrait;
        };

        // Auth user without required role
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn(new class {
            public function hasRole($roles) { return false; }
        });

        $object = new class {
            public $creator;
            public function __construct() { $this->creator = (object) ['id' => 55]; }
            public function creator() { return new class { public function exists() { return true; } }; }
        };

        $schema = [
            'custom_creator_id' => [
                'type' => 'creator',
                'name' => 'custom_creator_id',
                'allowedRoles' => ['manager'],
            ],
        ];

        $mapped = $repo->getFormFieldsCreatorTrait($object, [], $schema);
        $this->assertArrayNotHasKey('custom_creator_id', $mapped);
    }

    public function test_prepend_form_schema_returns_creator_component(): void
    {
        $repo = new class {
            use \Unusualify\Modularity\Repositories\Traits\CreatorTrait;
        };

        $schema = $repo->prependFormSchemaCreatorTrait();
        $this->assertIsArray($schema);
        $this->assertTrue(isset($schema[0]) && is_object($schema[0]));
        $this->assertSame('creator', $schema[0]->type);
    }
}

class CreatorTestRepository extends \Unusualify\Modularity\Tests\Repositories\TestRepository
{
    use \Unusualify\Modularity\Repositories\Traits\CreatorTrait;

    public function __construct(\Unusualify\Modularity\Tests\Repositories\TestModel $model)
    {
        $this->model = $model;
    }
}


