<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Unusualify\Modularity\Entities\File;
use Unusualify\Modularity\Repositories\FileRepository;
use Unusualify\Modularity\Tests\RepositoryTestCase;

class FileRepositoryTest extends RepositoryTestCase
{
    use RefreshDatabase;

    protected FileRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new FileRepository(new File);
    }

    public function test_filter_searches_in_filename(): void
    {
        File::create(['uuid' => 'u1', 'filename' => 'alpha.pdf', 'size' => 10]);
        File::create(['uuid' => 'u2', 'filename' => 'bravo.txt', 'size' => 20]);
        File::create(['uuid' => 'u3', 'filename' => 'charlie.doc', 'size' => 30]);

        $query = $this->repository->newQuery();
        $scopes = ['search' => 'bravo'];
        $ids = $this->repository->filter($query, $scopes)->pluck('id')->all();

        $this->assertCount(1, $ids);
    }

    public function test_prepare_fields_before_create_sets_size_from_storage_when_missing(): void
    {
        Config::set('twill.file_library.disk', 'files');
        Config::set('filesystems.disks.twill_file_library.root', 'uploads/files/');
        Storage::fake('files');

        // Create a file on the fake disk
        Storage::disk('files')->put('abc.pdf', str_repeat('x', 1234));

        $object = $this->repository->create([
            // repository strips configured root from uuid before querying size
            'uuid' => 'uploads/files/abc.pdf',
            'filename' => 'abc.pdf',
        ], [
            ['name' => 'uuid', 'type' => 'text'],
            ['name' => 'filename', 'type' => 'text'],
            ['name' => 'size', 'type' => 'number'],
        ]);

        $this->assertSame(1234, (int) $object->size);
    }

    public function test_after_delete_cascades_when_enabled(): void
    {
        Config::set('twill.file_library.disk', 'files');
        Config::set('twill.file_library.cascade_delete', true);
        Storage::fake('files');

        // Put a file with same UUID
        Storage::disk('files')->put('uuid-file', 'content');
        $f = File::create(['uuid' => 'uuid-file', 'filename' => 'x.bin', 'size' => 5]);

        $this->repository->afterDelete($f);
        $this->assertFalse(Storage::disk('files')->exists('uuid-file'));
    }
}
