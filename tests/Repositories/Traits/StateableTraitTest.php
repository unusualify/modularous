<?php

namespace Unusualify\Modularous\Tests\Repositories\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Entities\State;
use Unusualify\Modularous\Entities\Traits\HasStateable;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\StateableTrait;
use Unusualify\Modularous\Tests\RepositoryTestCase;

class StateableTraitTest extends RepositoryTestCase
{
    use RefreshDatabase;

    protected RepoStateableTestRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('repo_stateable_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable();
            $table->timestamps();
        });

        $this->repository = new RepoStateableTestRepository(new RepoStateableModel);
    }

    public function test_get_stateable_filter_list_only_returns_states_with_counts(): void
    {
        // Ensure states exist
        State::truncate();
        // Create one draft and one published via model creation
        $draft = $this->repository->create(['name' => 'Drafty']); // default initial is draft
        $published = $this->repository->create(['name' => 'Pub', 'initial_stateable' => 'published']);

        $filters = $this->repository->getStateableFilterList();

        $this->assertIsArray($filters);
        // Expect both states present
        $codes = array_column($filters, 'code');
        $this->assertContains('draft', $codes);
        $this->assertContains('published', $codes);

        // Counts should be > 0
        foreach ($filters as $f) {
            $this->assertGreaterThan(0, $f['number']);
            $this->assertStringStartsWith('isStateable', $f['slug']);
        }
    }

    public function test_get_table_filters_returns_configured_items_with_methods_and_params(): void
    {
        $this->repository->create(['name' => 'A']); // seed at least one
        $scope = ['owner_id' => 10];

        $filters = $this->repository->getTableFiltersStateableTrait($scope);

        $this->assertIsArray($filters);
        $this->assertNotEmpty($filters);
        foreach ($filters as $filter) {
            $this->assertArrayHasKey('name', $filter);
            $this->assertArrayHasKey('code', $filter);
            $this->assertArrayHasKey('slug', $filter);
            $this->assertArrayHasKey('methods', $filter);
            $this->assertArrayHasKey('params', $filter);
            $this->assertEquals('getCountByStatusSlug', $filter['methods']);
            $this->assertEquals($scope, $filter['params'][1]);
        }

        // allowedRoles present from repository static property
        $this->assertArrayHasKey('allowedRoles', $filters[0]);
        $this->assertEquals(['admin'], $filters[0]['allowedRoles']);
    }

    public function test_get_count_by_status_slug_stateable_trait_counts_by_code(): void
    {
        State::truncate();
        // two draft
        $this->repository->create(['name' => 'D1']);
        $this->repository->create(['name' => 'D2']);
        // one published
        $this->repository->create(['name' => 'P1', 'initial_stateable' => 'published']);

        $this->assertSame(2, $this->repository->getCountByStatusSlug('draft'));
        $this->assertSame(1, $this->repository->getCountByStatusSlug('published'));
        $this->assertFalse($this->repository->getCountByStatusSlugStateableTrait('nope'));
    }

    public function test_get_stateable_list_returns_hydrated_names(): void
    {
        // Ensure states are present
        State::truncate();
        $this->repository->create(['name' => 'Any']);

        $list = $this->repository->getStateableList(); // default item 'name'
        $this->assertIsArray($list);
        $this->assertNotEmpty($list);
        $this->assertArrayHasKey('id', $list[0]);
        $this->assertArrayHasKey('name', $list[0]);

        // Alternate item key
        $listAlt = $this->repository->getStateableList('title');
        $this->assertArrayHasKey('title', $listAlt[0]);
    }

    public function test_scope_is_stateable_filters_by_code(): void
    {
        State::truncate();
        $this->repository->create(['name' => 'Drafty']);
        $this->repository->create(['name' => 'Pub', 'initial_stateable' => 'published']);

        $draftCount = RepoStateableModel::isStateable('draft')->count();
        $publishedCount = RepoStateableModel::isStateable('published')->count();

        $this->assertSame(1, $draftCount);
        $this->assertSame(1, $publishedCount);
    }

    public function test_scope_is_stateables_filters_by_multiple_codes(): void
    {
        State::truncate();
        $this->repository->create(['name' => 'D1']);
        $this->repository->create(['name' => 'D2']);
        $this->repository->create(['name' => 'P1', 'initial_stateable' => 'published']);

        $count = RepoStateableModel::isStateables(['draft', 'published'])->count();
        $this->assertSame(3, $count);

        $count = RepoStateableModel::isStateables('draft,published')->count();
        $this->assertSame(3, $count);
    }

    public function test_scope_is_stateable_count_returns_count(): void
    {
        State::truncate();
        $this->repository->create(['name' => 'D1']);
        $this->repository->create(['name' => 'D2']);

        $count = RepoStateableModel::isStateableCount('draft');
        $this->assertSame(2, $count);
    }

    public function test_scope_is_stateables_count_returns_count(): void
    {
        State::truncate();
        $this->repository->create(['name' => 'D1']);
        $this->repository->create(['name' => 'P1', 'initial_stateable' => 'published']);

        $count = RepoStateableModel::isStateablesCount(['draft', 'published']);
        $this->assertSame(2, $count);
    }
}

class RepoStateableModel extends Model
{
    use HasStateable;

    protected $table = 'repo_stateable_models';

    protected $fillable = ['name', 'title', 'initial_stateable', 'stateable_id'];

    protected static $default_states = [
        [
            'code' => 'draft',
            'name' => 'Draft',
            'icon' => '$edit',
            'color' => 'warning',
        ],
        [
            'code' => 'published',
            'name' => 'Published',
            'icon' => '$publish',
            'color' => 'success',
        ],
    ];
}

final class RepoStateableTestRepository extends Repository
{
    use StateableTrait;

    protected static $stateableFilterUserRoles = ['admin'];

    public function __construct(RepoStateableModel $model)
    {
        $this->model = $model;
    }
}
