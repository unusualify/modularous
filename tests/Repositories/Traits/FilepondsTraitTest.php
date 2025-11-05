<?php

namespace Unusualify\Modularity\Tests\Repositories\Traits;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Unusualify\Modularity\Facades\Filepond;
use Unusualify\Modularity\Tests\Repositories\RepositorySources;
use Unusualify\Modularity\Tests\RepositoryTestCase;

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
        $columns = $this->repository->getColumns('Unusualify\\Modularity\\Repositories\\Traits\\FilepondsTrait');

        $this->assertSame(['pond-1'], $columns);
    }

    public function test_after_save_fileponds_trait_saves_non_translated_role(): void
    {
        $schema = [
            'pond-1' => ['type' => 'filepond', 'name' => 'pond-1', 'translated' => false],
        ];
        $this->repository->setColumns($schema);

        $fields = [
            'pond-1' => [
                ['id' => 10, 'name' => 'a.pdf'],
                ['id' => 12, 'name' => 'b.pdf'],
            ],
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
                'en' => [ ['id' => 21] ],
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
            'sections.*.pond' => ['type' => 'filepond', 'name' => 'sections.*.pond', 'translated' => false],
        ];
        $this->repository->setColumns($schema);

        $fields = [
            'sections' => [
                ['pond' => [ ['id' => 31] ]],
                ['pond' => []],
            ],
        ];

        Filepond::shouldReceive('saveFile')->once()->withArgs(function ($object, $files, $role, $locale = null) {
            return $role === 'sections.0.pond' && is_array($files) && count($files) === 1 && $locale === null;
        });

        $this->repository->afterSaveFilepondsTrait((object) ['id' => 3], $fields);
    }

    public function test_get_form_fields_fileponds_trait_maps_translated_role(): void
    {
        $schema = [
            'pond-1' => ['type' => 'filepond', 'name' => 'pond-1', 'translated' => true],
        ];
        $this->repository->setColumns($schema);

        $fileponds = collect([
            (object) [
                'role' => 'pond-1',
                'locale' => 'en',
                'mediableFormat' => function () { return ['name' => 'x.pdf']; },
            ],
        ])->map(function ($item) {
            return new class($item) {
                public $role; public $locale; private $base;
                public function __construct($base) { $this->role = $base->role; $this->locale = $base->locale; $this->base = $base; }
                public function mediableFormat() { $fn = $this->base->mediableFormat; return $fn(); }
            };
        });

        $object = new class($fileponds) {
            public $id = 5; public $fileponds;
            public function __construct($f) { $this->fileponds = $f; }
            public function has($rel) { return $rel === 'fileponds'; }
        };

        $mapped = $this->repository->getFormFieldsFilepondsTrait($object, [], $schema);

        $this->assertArrayHasKey('pond-1', $mapped);
        $this->assertArrayHasKey('en', $mapped['pond-1']);
        $this->assertSame('x.pdf', $mapped['pond-1']['en']->first()['name']);
    }
}

class FilepondTestRepository extends \Unusualify\Modularity\Tests\Repositories\TestRepository
{
    use \Unusualify\Modularity\Repositories\Traits\FilepondsTrait;

    public function __construct(\Unusualify\Modularity\Tests\Repositories\TestModel $model)
    {
        $this->model = $model;
    }
}

