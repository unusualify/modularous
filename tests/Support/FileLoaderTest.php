<?php

namespace Unusualify\Modularity\Tests\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Unusualify\Modularity\Support\FileLoader;
use Unusualify\Modularity\Tests\TestCase;

class FileLoaderTest extends TestCase
{
    protected $tempDir;

    protected $fileLoader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = storage_path('framework/testing/file_loader');
        if (! File::exists($this->tempDir)) {
            File::makeDirectory($this->tempDir, 0755, true);
        }

        $this->fileLoader = new FileLoader(new Filesystem, $this->tempDir);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempDir)) {
            File::deleteDirectory($this->tempDir);
        }
        parent::tearDown();
    }

    /** @test */
    public function it_can_get_paths()
    {
        $this->assertCount(1, $this->fileLoader->getPaths());
        $this->assertEquals($this->tempDir, $this->fileLoader->getPaths()[0]);
    }

    /** @test */
    public function it_can_add_paths()
    {
        $newPath = $this->tempDir . '/extra';
        $this->fileLoader->addPath($newPath);

        $this->assertCount(2, $this->fileLoader->getPaths());
        $this->assertContains($newPath, $this->fileLoader->getPaths());
    }

    /** @test */
    public function it_can_add_multiple_paths()
    {
        $newPaths = [$this->tempDir . '/1', $this->tempDir . '/2'];
        $this->fileLoader->addPath($newPaths);

        $this->assertCount(3, $this->fileLoader->getPaths());
    }

    /** @test */
    public function it_can_get_groups_from_php_files()
    {
        File::put($this->tempDir . '/auth.php', '<?php return [];');
        File::put($this->tempDir . '/validation.php', '<?php return [];');

        $groups = $this->fileLoader->getGroups();

        $this->assertContains('auth', $groups);
        $this->assertContains('validation', $groups);
        $this->assertCount(2, $groups);
    }

    /** @test */
    public function it_handles_nested_directories_in_groups()
    {
        $subDir = $this->tempDir . '/nested';
        File::makeDirectory($subDir);
        File::put($subDir . '/test.php', '<?php return [];');

        $groups = $this->fileLoader->getGroups();

        $this->assertContains('test', $groups);
    }
}
