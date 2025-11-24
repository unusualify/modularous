<?php

namespace Unusualify\Modularity\Tests\Repositories\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Entities\Enums\ProcessStatus;
use Unusualify\Modularity\Entities\Process;
use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Tests\RepositoryTestCase;

class ProcessableTraitTest extends RepositoryTestCase
{
    use RefreshDatabase;

    protected RepoProcessableTestRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('repo_processable_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('processable_status')->nullable();
            $table->text('processable_reason')->nullable();
            $table->timestamps();
        });

        $this->repository = new RepoProcessableTestRepository(new RepoProcessableModel);
    }

    public function test_set_columns_collects_process_inputs(): void
    {
        $schema = [
            ['name' => 'name', 'type' => 'text'],
            ['name' => 'main_process', 'type' => 'process'],
            ['name' => 'other', 'type' => 'text'],
        ];

        $this->repository->setColumns($this->repository->chunkInputs($schema));

        $cols = $this->repository->getColumns(\Unusualify\Modularity\Repositories\Traits\ProcessableTrait::class);
        $this->assertContains('main_process', $cols);
        $this->assertNotContains('name', $cols);
    }

    public function test_get_process_id_creates_when_absent_and_returns_existing_afterwards(): void
    {
        $model = $this->repository->create(['name' => 'Proc A']);

        // Ensure an initial process exists (created by model trait)
        $initialId = $this->repository->getProcessId($model);
        $this->assertNotNull($initialId);
        $this->assertSame($model->process->id, $initialId);

        // Delete the process to simulate missing record
        $model->process()->delete();
        $this->assertNull($model->fresh()->process);

        // Calling getProcessId should create a new one
        $newId = $this->repository->getProcessId($model->fresh());
        $this->assertNotNull($newId);
        $this->assertEquals($newId, $model->fresh()->process->id);

        // New process defaults
        $this->assertEquals(ProcessStatus::from('preparing'), $model->fresh()->process->status);
    }

    public function test_get_form_fields_includes_process_id_for_process_inputs(): void
    {
        $model = $this->repository->create(['name' => 'Proc Fields']);
        $schema = [
            ['name' => 'main_process', 'type' => 'process', 'schema' => [
                ['name' => 'virtual_field', 'type' => 'text'],
            ]],
            ['name' => 'name', 'type' => 'text'],
        ];

        $fields = $this->repository->getFormFields($model, $schema);

        // Contains process field with ID
        $this->assertArrayHasKey('main_process', $fields);
        $this->assertSame($model->process->id, $fields['main_process']);

        // Leaves existing attributes present
        $this->assertArrayHasKey('name', $fields);
        $this->assertSame('Proc Fields', $fields['name']);
    }

    public function test_get_form_fields_noop_for_non_existing_object(): void
    {
        $new = new RepoProcessableModel(['name' => 'Unsaved']);
        $schema = [
            ['name' => 'p', 'type' => 'process'],
        ];

        $fields = $this->repository->getFormFields($new, $schema);
        // Since object doesn't exist yet, trait should not add process id
        $this->assertArrayNotHasKey('p', $fields);
    }
}

class RepoProcessableModel extends \Illuminate\Database\Eloquent\Model
{
    use \Unusualify\Modularity\Entities\Traits\Processable;

    protected $table = 'repo_processable_models';

    protected $fillable = ['name', 'processable_status', 'processable_reason'];
}

final class RepoProcessableTestRepository extends Repository
{
    use \Unusualify\Modularity\Repositories\Traits\ProcessableTrait;

    public function __construct(RepoProcessableModel $model)
    {
        $this->model = $model;
    }
}


