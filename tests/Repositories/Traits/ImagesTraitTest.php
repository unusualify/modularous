<?php

namespace Unusualify\Modularity\Tests\Repositories\Traits;

use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Entities\Media;
use Unusualify\Modularity\Entities\Traits\HasImages;
use Unusualify\Modularity\Tests\Repositories\RepositorySources;
use Unusualify\Modularity\Tests\RepositoryTestCase;

class ImagesTraitTest extends RepositoryTestCase
{
    use RepositorySources;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();

        $this->repository = App::make(ImagesTestRepository::class);
    }

    public function test_set_columns_images_trait_collects_image_inputs()
    {
        $repo = new class
        {
            use \Unusualify\Modularity\Repositories\Traits\ImagesTrait;
        };

        $columns = $repo->setColumnsImagesTrait([], [
            ['name' => 'photo', 'type' => 'file'],
            ['name' => 'avatar', 'type' => 'image'],
            ['name' => 'gallery', 'type' => 'image'],
        ]);

        $this->assertSame(['avatar', 'gallery'], $columns['ImagesTrait']);
    }

    public function test_attach_uploaded_media_to_model_non_translated_role()
    {
        $media = Media::create([
            'uuid' => 'uploads/folder/img-1.jpg',
            'filename' => 'img-1.jpg',
            'alt_text' => 'img-1.jpg',
            'caption' => 'img-1.jpg',
            'width' => 100,
            'height' => 100,
        ]);

        $model = $this->repository->create(['name' => 'With Image']);

        $schema = [
            'image-1' => [
                'type' => 'image',
                'name' => 'image-1',
                'translated' => false,
            ],
        ];

        $fields = [
            'image-1' => [
                [
                    'id' => $media->id,
                    'metadatas' => ['default' => ['caption' => null, 'altText' => null, 'video' => null]],
                ],
            ],
        ];

        $this->repository->update($model->id, $fields, $schema);

        $this->assertTrue($model->fresh()->medias->contains('id', $media->id));
    }

    public function test_detach_media_when_payload_omits_role_after_previous_attachment()
    {
        $media = Media::create([
            'uuid' => 'uploads/folder/img-2.jpg',
            'filename' => 'img-2.jpg',
            'alt_text' => 'img-2.jpg',
            'caption' => 'img-2.jpg',
            'width' => 100,
            'height' => 100,
        ]);

        $model = $this->repository->create(['name' => 'With Image']);

        $schema = [
            'image-1' => [
                'type' => 'image',
                'name' => 'image-1',
                'translated' => false,
            ],
        ];

        $this->repository->update($model->id, [
            'image-1' => [['id' => $media->id, 'metadatas' => ['default' => ['caption' => null, 'altText' => null, 'video' => null]]]],
        ], $schema);
        $this->assertTrue($model->fresh()->medias->contains('id', $media->id));

        $this->repository->update($model->id, [], $schema);
        $this->assertSame(0, $model->fresh()->medias()->count());
    }

    public function test_attach_translated_media_role_attaches_with_locale_pivot()
    {
        $media = Media::create([
            'uuid' => 'uploads/folder/img-3.jpg',
            'filename' => 'img-3.jpg',
            'alt_text' => 'img-3.jpg',
            'caption' => 'img-3.jpg',
            'width' => 100,
            'height' => 100,
        ]);

        $model = $this->repository->create(['name' => 'Translated Image']);

        $schema = [
            'image-1' => [
                'type' => 'image',
                'name' => 'image-1',
                'translated' => true,
            ],
        ];

        $fields = [
            'image-1' => [
                'en' => [['id' => $media->id, 'metadatas' => ['default' => ['caption' => null, 'altText' => null, 'video' => null]]]],
            ],
        ];

        $this->repository->update($model->id, $fields, $schema);

        $pivot = $model->fresh()->medias->where('id', $media->id)->first()->pivot;
        $this->assertSame('image-1', $pivot->role);
        $this->assertSame('en', $pivot->locale);
    }

    public function test_ignored_medias_field_skips_sync_and_attachment()
    {
        $media = Media::create([
            'uuid' => 'uploads/folder/img-4.jpg',
            'filename' => 'img-4.jpg',
            'alt_text' => 'img-4.jpg',
            'caption' => 'img-4.jpg',
            'width' => 100,
            'height' => 100,
        ]);

        $model = $this->repository->create(['name' => 'Ignored Medias']);

        $schema = [
            'image-1' => [
                'type' => 'image',
                'name' => 'image-1',
                'translated' => false,
            ],
        ];

        $this->repository->addIgnoreFieldsBeforeSave('medias');

        $this->repository->update($model->id, [
            'image-1' => [['id' => $media->id, 'metadatas' => ['default' => ['caption' => null, 'altText' => null, 'video' => null]]]],
        ], $schema);

        $this->assertSame(0, $model->fresh()->medias()->count());
    }
}

class ImagesTestModel extends \Unusualify\Modularity\Tests\Repositories\TestModel
{
    use HasImages;
}

class ImagesTestRepository extends \Unusualify\Modularity\Tests\Repositories\TestRepository
{
    use \Unusualify\Modularity\Repositories\Traits\ImagesTrait;

    public function __construct(ImagesTestModel $model)
    {
        $this->model = $model;
    }
}
