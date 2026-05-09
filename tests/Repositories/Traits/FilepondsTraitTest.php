<?php

namespace Unusualify\Modularous\Tests\Repositories\Traits;

use Illuminate\Support\Facades\App;
use Unusualify\Modularous\Facades\Filepond;
use Unusualify\Modularous\Repositories\Traits\FilepondsTrait;
use Unusualify\Modularous\Tests\Repositories\RepositorySources;
use Unusualify\Modularous\Tests\Repositories\TestModel;
use Unusualify\Modularous\Tests\Repositories\TestRepository;
use Unusualify\Modularous\Tests\RepositoryTestCase;

class FilepondsTraitTest extends RepositoryTestCase
{
    use RepositorySources;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();

        $this->repository = App::make(FilepondTestRepository::class);
    }

    public function test_set_columns_fileponds_trait_collects_inputs(): void
    {
        $schema = [
            'pond-1' => ['type' => 'filepond', 'name' => 'pond-1'],
            'other' => ['type' => 'text', 'name' => 'other'],
        ];

        $this->repository->setColumns($schema);
        $columns = $this->repository->getColumns('Unusualify\\Modularous\\Repositories\\Traits\\FilepondsTrait');

        $this->assertSame(['pond-1'], $columns);
    }

    public function test_after_save_fileponds_trait_saves_non_translated_role(): void
    {
        $schema = [
            'pond-1' => ['type' => 'filepond', 'name' => 'pond-1', 'translated' => false],
            'pond-2' => ['type' => 'filepond', 'name' => 'pond-2', 'translated' => false],
        ];
        $this->repository->setColumns($schema);

        $fields = [
            'pond-1' => [
                ['id' => 10, 'name' => 'a.pdf'],
                ['id' => 12, 'name' => 'b.pdf'],
            ],
            'pond-2' => null,
        ];

        Filepond::shouldReceive('saveFile')->once()->withArgs(function ($object, $files, $role, $locale = null) {
            return $role === 'pond-1' && is_array($files) && count($files) === 2 && $locale === null;
        });

        $this->repository->afterSaveFilepondsTrait((object) ['id' => 1], $fields);
    }

    public function test_after_save_fileponds_trait_saves_translated_role_by_locale(): void
    {
        $schema = [
            'pond-1' => ['type' => 'filepond', 'name' => 'pond-1', 'translated' => true],
        ];
        $this->repository->setColumns($schema);

        $fields = [
            'pond-1' => [
                'en' => [['id' => 21]],
                'tr' => [],
            ],
        ];

        Filepond::shouldReceive('saveFile')->once()->withArgs(function ($object, $files, $role, $locale) {
            return $role === 'pond-1' && $locale === 'en' && is_array($files) && count($files) === 1;
        });

        $this->repository->afterSaveFilepondsTrait((object) ['id' => 2], $fields);
    }

    public function test_after_save_fileponds_trait_saves_wildcard_nested_roles(): void
    {
        $schema = [
            'translated_sections.*.pond' => ['type' => 'filepond', 'name' => 'translated_sections.*.pond', 'translated' => true],
            'sections.*.pond' => ['type' => 'filepond', 'name' => 'sections.*.pond', 'translated' => false],
        ];
        $this->repository->setColumns($schema);

        $fields = [
            'sections' => [
                ['pond' => [['id' => 31]]],
                ['pond' => []],
            ],
            'translated_sections' => [
                ['pond' => ['en' => [['id' => 32]], 'tr' => []]],
                ['pond' => ['tr' => [['id' => 33]], 'en' => []]],
            ],
        ];

        Filepond::shouldReceive('saveFile')->once()->withArgs(function ($object, $files, $role, $locale = null) {
            return ($role === 'sections.0.pond' || $role === 'sections.1.pond') && is_array($files) && count($files) === 1 && $locale === null;
        });

        Filepond::shouldReceive('saveFile')->once()->withArgs(function ($object, $files, $role, $locale) {
            return $role === 'translated_sections.0.pond' && $locale === 'en' && is_array($files) && count($files) === 1;
        });

        Filepond::shouldReceive('saveFile')->once()->withArgs(function ($object, $files, $role, $locale) {
            return $role === 'translated_sections.1.pond' && $locale === 'tr' && is_array($files) && count($files) === 1;
        });

        $this->repository->afterSaveFilepondsTrait((object) ['id' => 3], $fields);

        Filepond::shouldHaveReceived('saveFile')->times(3);
    }

    public function test_get_form_fields_fileponds_trait_maps_translated_role(): void
    {
        config(['translatable.locales' => ['en', 'tr']]);
        $schema = [
            'pond-1' => ['type' => 'filepond', 'name' => 'pond-1', 'translated' => true],
            'pond-2' => ['type' => 'filepond', 'name' => 'pond-2', 'translated' => false],
            'pond-3' => ['type' => 'filepond', 'name' => 'pond-3', 'translated' => false],
            'pond-4' => ['type' => 'filepond', 'name' => 'pond-4', 'translated' => true],
        ];
        $this->repository->setColumns($schema);

        $fileponds = collect([
            (object) [
                'role' => 'pond-1',
                'locale' => 'en',
                'mediableFormat' => function () {
                    return ['name' => 'x.pdf'];
                },
            ],
            (object) [
                'role' => 'pond-2',
                'locale' => 'en',
                'mediableFormat' => function () {
                    return ['name' => 'y.pdf'];
                },
            ],
        ])->map(function ($item) {
            return new class($item)
            {
                public $role;

                public $locale;

                private $base;

                public function __construct($base)
                {
                    $this->role = $base->role;
                    $this->locale = $base->locale;
                    $this->base = $base;
                }

                public function mediableFormat()
                {
                    $fn = $this->base->mediableFormat;

                    return $fn();
                }
            };
        });

        $object = new class($fileponds)
        {
            public $id = 5;

            public $fileponds;

            public function __construct($f)
            {
                $this->fileponds = $f;
            }

            public function has($rel)
            {
                return $rel === 'fileponds';
            }
        };

        $mapped = $this->repository->getFormFieldsFilepondsTrait($object, [], $schema);

        $this->assertArrayHasKey('pond-1', $mapped);
        $this->assertArrayHasKey('en', $mapped['pond-1']);
        $this->assertSame('x.pdf', $mapped['pond-1']['en']->first()['name']);

        $this->assertArrayHasKey('pond-2', $mapped);
        $this->assertCount(1, $mapped['pond-2']);
        $this->assertSame('y.pdf', $mapped['pond-2'][0]['name']);

        $this->assertArrayHasKey('pond-3', $mapped);
        $this->assertCount(0, $mapped['pond-3']);

        $this->assertArrayHasKey('pond-4', $mapped);
        $this->assertCount(2, $mapped['pond-4']);
        $this->assertEmpty($mapped['pond-4']['en']);
        $this->assertEmpty($mapped['pond-4']['tr']);
    }

    // public function test_get_form_fields_fileponds_trait_returns_empty_array_for_null_values(): void
    // {
    //     $schema = [
    //         'pond-1' => ['type' => 'filepond', 'name' => 'pond-1', 'translated' => false],
    //     ];
    //     $this->repository->setColumns($schema);

    // }
}

class FilepondTestRepository extends TestRepository
{
    use FilepondsTrait;

    public function __construct(TestModel $model)
    {
        $this->model = $model;
    }
}
