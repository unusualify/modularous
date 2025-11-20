<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Unusualify\Modularity\Entities\Media;
use Unusualify\Modularity\Repositories\MediaRepository;
use Unusualify\Modularity\Services\MediaLibrary\ImageService;
use Unusualify\Modularity\Tests\RepositoryTestCase;

class MediaRepositoryTest extends RepositoryTestCase
{
    use RefreshDatabase;

    protected MediaRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new MediaRepository(new Media);
    }

    public function test_filter_searches_in_filename_alt_and_caption(): void
    {
        ImageService::shouldReceive('getDimensions')->andReturn(['width' => 100, 'height' => 100]);
        $this->repository->create(['uuid' => 'u1', 'filename' => 'alpha.jpg', 'caption' => 'none']);
        $this->repository->create(['uuid' => 'u2', 'filename' => 'bravo.jpg', 'caption' => 'x']);
        $this->repository->create(['uuid' => 'u3', 'filename' => 'charlie.jpg', 'caption' => 'find me']);

        $query = $this->repository->newQuery();
        $scopes = ['search' => 'bravo'];
        $ids = $this->repository->filter($query, $scopes)->pluck('id')->all();

        $this->assertCount(1, $ids);

        $query = $this->repository->newQuery();
        $scopes = ['search' => 'charlie'];
        $ids = $this->repository->filter($query, $scopes)->pluck('id')->all();
        $this->assertCount(1, $ids);

        $query = $this->repository->newQuery();
        $scopes = ['search' => 'find me'];
        $ids = $this->repository->filter($query, $scopes)->pluck('id')->all();
        $this->assertCount(1, $ids);
    }

    public function test_prepare_fields_before_create_sets_alt_text_and_skips_dimensions_if_present(): void
    {
        Config::set('twill.media_library.init_alt_text_from_filename', true);

        $obj = $this->repository->create([
            'uuid' => 'folder/image-1.jpg',
            'filename' => 'image-1.jpg',
            'width' => 100,
            'height' => 200,
            'alt_text' => 'Image 1',
            'caption' => 'Caption 1',
        ], [
            ['name' => 'uuid', 'type' => 'text'],
            ['name' => 'filename', 'type' => 'text'],
            ['name' => 'alt_text', 'type' => 'text'],
            ['name' => 'caption', 'type' => 'text'],
            ['name' => 'width', 'type' => 'number'],
            ['name' => 'height', 'type' => 'number'],
        ]);

        $this->assertSame('Image 1', $obj->alt_text);
        $this->assertSame(100, $obj->width);
        $this->assertSame(200, $obj->height);
    }

    public function test_after_delete_cascades_when_enabled(): void
    {
        Config::set('twill.media_library.disk', 'media');
        Config::set('twill.media_library.cascade_delete', true);
        Storage::fake('media');

        // Put a file with same UUID
        Storage::disk('media')->put('uuid-123', 'content');
        $m = Media::create(['uuid' => 'uuid-123', 'filename' => 'x.jpg', 'width' => 1, 'height' => 1]);

        $this->repository->afterDelete($m);

        $this->assertFalse(Storage::disk('media')->exists('uuid-123'));
    }
}


