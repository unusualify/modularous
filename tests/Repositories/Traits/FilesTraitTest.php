<?php

namespace Unusualify\Modularity\Tests\Repositories\Traits;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Mockery\MockInterface;
use Unusualify\Modularity\Entities\Traits\HasFiles;
use Unusualify\Modularity\Entities\File as LibraryFile;
use Unusualify\Modularity\Tests\Repositories\RepositorySources;
use Unusualify\Modularity\Tests\RepositoryTestCase;

class FilesTraitTest extends RepositoryTestCase
{
    use RepositorySources;

    /**
     * input schema for the files trait
     *
     * @var array
     */
    protected $schema = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();

        $this->repository = App::make(FilesTestRepository::class);

        $this->schema = [
            'file-1' => [
                'type' => 'input-file',
                'name' => 'file-1',
                'translated' => false
            ]
        ];
    }

    public function test_set_columns_files_trait_collects_file_inputs()
    {
        $repo = new class {
            use \Unusualify\Modularity\Repositories\Traits\FilesTrait;
        };

        $columns = $repo->setColumnsFilesTrait([], [
            'photo' => ['name' => 'photo', 'type' => 'file'],
            'avatar' => ['name' => 'avatar', 'type' => 'image'],
            'title' => ['name' => 'title', 'type' => 'text'],
        ]);

        $this->assertSame(['photo'], $columns['FilesTrait']);
    }

    public function test_hydrate_files_trait_hydrates_files()
    {
        // $temporaryFile = TemporaryFilepond::create([
        //     'file_name' => 'test.jpg',
        //     'input_role' => 'photo',
        // ]);

        $fields = [
            'photo' => [

            ],
            'name' => 'test',
        ];

        $mock = $this->partialMock(FilesTestRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('getColumns')->andReturn(['photo']);
        });

        $object = new FilesTestModel();
        $object = $mock->hydrateFilesTrait($object, $fields);

        $this->assertCount(0, $object->files);
    }

    public function test_attach_uploaded_file_to_model_via_files_trait_non_translated_role()
    {
        // Arrange: create a file record as if uploaded to the file library
        $file = LibraryFile::create([
            'uuid' => 'uploads/folder/file-1.pdf',
            'filename' => 'file-1.pdf',
            'size' => 100,
        ]);

        // Arrange: create a model to attach files to
        $model = $this->repository->create(['name' => 'With File']);

        // Schema defines a non-translated file role 'file-1'
        $schema = [
            'file-1' => [
                'type' => 'input-file',
                'name' => 'file-1',
                'translated' => false,
            ],
        ];

        // Payload mimicking files attach: role points to uploaded file id
        $fields = [
            'file-1' => [
                [
                    'id' => $file->id,
                ],
            ],
        ];

        // Act: update triggers FilesTrait afterSave to sync pivot and attach
        $this->repository->update($model->id, $fields, $schema);

        // Assert: pivot attached
        $this->assertTrue($model->fresh()->files->contains('id', $file->id));
    }

    public function test_detach_files_when_payload_omits_role_after_previous_attachment()
    {
        // Arrange: create a library file and a model
        $file = LibraryFile::create([
            'uuid' => 'uploads/folder/file-2.pdf',
            'filename' => 'file-2.pdf',
            'size' => 100,
        ]);

        $model = $this->repository->create(['name' => 'With File']);

        $schema = [
            'file-1' => [
                'type' => 'input-file',
                'name' => 'file-1',
                'translated' => false,
            ],
        ];

        // First attach one file
        $this->repository->update($model->id, [
            'file-1' => [[ 'id' => $file->id ]],
        ], $schema);
        $this->assertTrue($model->fresh()->files->contains('id', $file->id));

        // Act: second update without role should detach all for that role (sync([]))
        $this->repository->update($model->id, [], $schema);

        // Assert: no files attached
        $this->assertSame(0, $model->fresh()->files()->count());
    }

    public function test_attach_translated_file_role_attaches_with_locale_pivot()
    {
        // Arrange: uploaded library file
        $file = LibraryFile::create([
            'uuid' => 'uploads/folder/file-3.pdf',
            'filename' => 'file-3.pdf',
            'size' => 100,
        ]);

        $model = $this->repository->create(['name' => 'Translated File']);

        $schema = [
            'file-1' => [
                'type' => 'input-file',
                'name' => 'file-1',
                'translated' => true,
            ],
        ];

        $fields = [
            'file-1' => [
                'en' => [[ 'id' => $file->id ]],
                // another locale intentionally empty
            ],
        ];

        // Act
        $this->repository->update($model->id, $fields, $schema);

        // Assert: attached with pivot locale 'en'
        $pivot = $model->fresh()->files->where('id', $file->id)->first()->pivot;
        $this->assertSame('file-1', $pivot->role);
        $this->assertSame('en', $pivot->locale);
    }

    public function test_ignored_files_field_skips_sync_and_attachment()
    {
        $file = LibraryFile::create([
            'uuid' => 'uploads/folder/file-4.pdf',
            'filename' => 'file-4.pdf',
            'size' => 100,
        ]);

        $model = $this->repository->create(['name' => 'Ignored Files']);

        $schema = [
            'file-1' => [
                'type' => 'input-file',
                'name' => 'file-1',
                'translated' => false,
            ],
        ];

        $this->repository->addIgnoreFieldsBeforeSave('files');

        $this->repository->update($model->id, [
            'file-1' => [[ 'id' => $file->id ]],
        ], $schema);

        $this->assertSame(0, $model->fresh()->files()->count());
    }
}

class FilesTestModel extends \Unusualify\Modularity\Tests\Repositories\TestModel
{
    use HasFiles;
}

class FilesTestRepository extends \Unusualify\Modularity\Tests\Repositories\TestRepository
{
    use \Unusualify\Modularity\Repositories\Traits\FilesTrait;

    public function __construct(FilesTestModel $model)
    {
        $this->model = $model;
    }
}


