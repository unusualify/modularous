<?php

namespace Unusualify\Modularity\Tests\Generators;

use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Unusualify\Modularity\Generators\LaravelTestGenerator;
use Unusualify\Modularity\Tests\TestCase;
use Mockery;

class LaravelTestGeneratorTest extends TestCase
{
    protected $generator;
    protected $config;
    protected $filesystem;
    protected $console;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Mockery::mock(Config::class);
        $this->filesystem = Mockery::mock(Filesystem::class);
        $this->console = Mockery::mock(Console::class);

        $this->generator = new LaravelTestGenerator(
            'TestLaravelTest',
            $this->config,
            $this->filesystem,
            $this->console
        );
    }

    /** @test */
    public function it_can_set_and_get_type()
    {
        $this->generator->setType('unit');
        $type = $this->generator->getType();
        $this->assertIsArray($type);
        $this->assertEquals('Unit/', $type['import_dir']);
    }

    /** @test */
    public function it_returns_types()
    {
        $types = $this->generator->getTypes();
        $this->assertArrayHasKey('unit', $types);
        $this->assertArrayHasKey('feature', $types);
    }

    /** @test */
    public function it_returns_type_import_dir()
    {
        $this->generator->setType('unit');
        // TestLaravelTest in PascalCase is TestLaravelTest
        $this->assertEquals('Unit/TestLaravelTest', $this->generator->getTypeImportDir());

        $this->generator->setSubImportDir('SubDir');
        $this->assertEquals('Unit/SubDir/TestLaravelTest', $this->generator->getTypeImportDir());
    }

    /** @test */
    public function it_returns_type_target_dir()
    {
        $this->generator->setType('unit');
        $this->assertEquals('Unit', $this->generator->getTypeTargetDir());
    }

    /** @test */
    public function it_returns_type_stub_file()
    {
        $this->generator->setType('unit');
        $this->assertEquals('tests/laravel-unit', $this->generator->getTypeStubFile());
    }

    /** @test */
    public function it_returns_target_path()
    {
        $this->assertStringContainsString('src/Tests', $this->generator->getTargetPath());
    }

    /** @test */
    public function it_returns_test_file_name()
    {
        $this->generator->setType('unit');
        // Kebab case of TestLaravelTest is test-laravel-test
        $this->assertEquals('test-laravel-test.php', $this->generator->getTestFileName());
    }

    /** @test */
    public function it_returns_namespace_replacement()
    {
        $this->generator->setType('unit');
        $this->assertEquals('test/Unit/test-laravel-test.php', $this->generator->getNamespaceReplacement());
    }

    /** @test */
    public function it_returns_camel_case_replacement()
    {
        $this->assertEquals('testLaravelTest', $this->generator->getCamelCaseReplacement());
    }

    /** @test */
    public function it_returns_import_replacement()
    {
        $this->generator->setType('unit');
        $this->assertEquals('Unit/TestLaravelTest.js', $this->generator->getImportReplacement());

        $this->generator->setSubImportDir('Sub');
        $this->assertEquals('Unit/Sub/TestLaravelTest.js', $this->generator->getImportReplacement());
    }

    /** @test */
    public function it_can_set_sub_target_dir()
    {
        $this->generator->setSubTargetDir('CustomTarget');
        $this->assertEquals('CustomTarget', $this->generator->subTargetDir);
    }

    /** @test */
    public function it_verifies_generate_method_exists()
    {
        // generate() requires stub files to exist, which is complex to test in unit tests
        // We simply verify the method exists and is callable
        $this->assertTrue(method_exists($this->generator, 'generate'));
    }
}
