<?php

namespace Unusualify\Modularity\Tests\Repositories\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Modules\SystemUser\Entities\Role;
use Unusualify\Modularity\Entities\Assignment;
use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\Assignable;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Tests\Repositories\RepositorySources;
use Unusualify\Modularity\Tests\RepositoryTestCase;

class AssignmentTraitTest extends RepositoryTestCase
{
    use RepositorySources;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();

        $this->repository = App::make(TestRepository::class);
    }

    public function test_set_columns_assignment_trait_collects_assignment_inputs()
    {
        $repo = new class {
            use \Unusualify\Modularity\Repositories\Traits\AssignmentTrait;
            public array $traitColumns = [];
        };

        $columns = $repo->setColumnsAssignmentTrait([], [
            ['name' => 'assignee', 'type' => 'assignment'],
            ['name' => 'title', 'type' => 'text'],
        ]);

        $this->assertNotEmpty($columns['AssignmentTrait'] ?? []);
        $this->assertSame(['assignee'], $columns['AssignmentTrait']);
    }

    public function test_get_form_fields_assignment_trait_sets_model_key_on_fields()
    {
        $repo = new class {
            use \Unusualify\Modularity\Repositories\Traits\AssignmentTrait;
            public function getColumns($trait) { return ['assignee']; }
        };

        $object = new class {
            public function getKey() { return 42; }
        };

        $fields = $repo->getFormFieldsAssignmentTrait($object, [], []);
        $this->assertSame(42, $fields['assignee']);
    }

    public function test_filter_assignment_trait_sets_scope_flag()
    {
        $repo = new class {
            use \Unusualify\Modularity\Repositories\Traits\AssignmentTrait;
        };

        $scopes = [];
        $repo->filterAssignmentTrait(null, $scopes);
        $this->assertArrayHasKey('everAssignedToYourRoleOrHasAuthorization', $scopes);
        $this->assertTrue($scopes['everAssignedToYourRoleOrHasAuthorization']);
    }

    public function test_get_table_filters_assignment_trait()
    {
        $repo = new class {
            use \Unusualify\Modularity\Repositories\Traits\AssignmentTrait;
        };

        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'modularity']);
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'modularity']);

        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com']);
        $admin->assignRole('admin');
        $editor = User::create(['name' => 'Editor', 'email' => 'editor@example.com']);
        $editor->assignRole('editor');

        $this->actingAs($admin);
        $filters = $repo->getTableFiltersAssignmentTrait();

        $this->assertCount(8, $filters);
        $this->assertEquals([
            'my-assignments',
            'your-role-assignments',
            'completed-assignments',
            'pending-assignments',
            'your-completed-assignments',
            'team-completed-assignments',
            'your-pending-assignments',
            'team-pending-assignments'
        ], array_column($filters, 'slug'));

        $this->assertEquals([
            ['isActiveAssignee'],
            ['isActiveAssigneeForYourRole'],
            ['completedAssignments'],
            ['pendingAssignments'],
            ['yourCompletedAssignments'],
            ['teamCompletedAssignments'],
            ['yourPendingAssignments'],
            ['teamPendingAssignments']
        ], array_column($filters, 'params'));


        $this->actingAs($editor);
        $repo->setAllowableUser($editor);
        $filters = $repo->getTableFiltersAssignmentTrait();
        $this->assertCount(6, $filters);
        $this->assertEquals([
            'my-assignments',
            'your-role-assignments',
            'your-completed-assignments',
            'team-completed-assignments',
            'your-pending-assignments',
            'team-pending-assignments'
        ], array_column($filters, 'slug'));

    }

    public function test_get_assignments()
    {
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'modularity']);
        $managerRole = Role::create(['name' => 'manager', 'guard_name' => 'modularity']);
        $editorRole = Role::create(['name' => 'editor', 'guard_name' => 'modularity']);

        $model = $this->repository->create(['name' => 'Test']);

        $assigner = User::create(['name' => 'Assigner', 'email' => 'assigner@example.com']);
        $assigner->assignRole('admin');
        $assignee = User::create(['name' => 'Assignee', 'email' => 'assignee@example.com']);
        $assignee->assignRole('editor');

        $assignment = $model->assignments()->create([
            'assignee_id' => $assignee->id,
            'assignee_type' => get_class($assignee),
            'assigner_id' => $assigner->id,
            'assigner_type' => get_class($assigner),
            'description' => 'Test assignment description',
            'due_at' => now()->addDays(7),
        ]);

        $this->assertEquals($assignment->id, $this->repository->getAssignments($model->id)->first()->id);
    }
}

class TestModel extends \Unusualify\Modularity\Tests\Repositories\TestModel
{
    use Assignable;
}

class TestRepository extends \Unusualify\Modularity\Tests\Repositories\TestRepository
{
    use \Unusualify\Modularity\Repositories\Traits\AssignmentTrait;

    public function __construct(TestModel $model)
    {
        $this->model = $model;
    }
}


