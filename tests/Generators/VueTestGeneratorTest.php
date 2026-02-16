<?php

namespace Unusualify\Modularity\Tests\Generators;

use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\Support\Stub;
use Unusualify\Modularity\Generators\VueTestGenerator;
use Unusualify\Modularity\Tests\TestCase;
use Mockery;

class VueTestGeneratorTest extends TestCase
{
    protected $generator;
    protected $config;
    protected $filesystem;
    protected $console;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Mockery::mock(Config::class);
        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive("isDirectory")->andReturn(false);
        $filesystem->shouldReceive("makeDirectory")->andReturn(true);
        $filesystem->shouldReceive("put")->andReturn(true);

        $this->filesystem = $filesystem;
        $console = Mockery::mock(Console::class);
        $console->shouldReceive("info")->andReturn(0);
        $this->console = $console;

        $this->generator = new VueTestGenerator(
            'TestVueTest',
            $this->config,
            $this->filesystem,
            $this->console
        );
    }

    /** @test */
    public function it_can_set_and_get_type()
    {
        $this->generator->setType('component');
        $type = $this->generator->getType();
        $this->assertIsArray($type);
        $this->assertEquals('components/', $type['import_dir']);
    }

    /** @test */
    public function it_returns_types()
    {
        $types = $this->generator->getTypes();
        $this->assertArrayHasKey('component', $types);
        $this->assertArrayHasKey('util', $types);
        $this->assertArrayHasKey('hook', $types);
        $this->assertArrayHasKey('store', $types);
    }

    /** @test */
    public function it_returns_type_import_dir()
    {
        $this->generator->setType('component');
        // TestVueTest in PascalCase is TestVueTest
        $this->assertEquals('components/TestVueTest', $this->generator->getTypeImportDir());

        $this->generator->setSubImportDir('SubDir');
        $this->assertEquals('components/SubDir/TestVueTest', $this->generator->getTypeImportDir());
    }

    /** @test */
    public function it_returns_type_target_dir()
    {
        $this->generator->setType('component');
        $this->assertEquals('components', $this->generator->getTypeTargetDir());
    }

    /** @test */
    public function it_returns_type_stub_file()
    {
        $this->generator->setType('component');
        $this->assertEquals('tests/vue-component', $this->generator->getTypeStubFile());
    }

    /** @test */
    public function it_returns_target_path()
    {
        $this->assertStringContainsString('vue/test', $this->generator->getTargetPath());
    }

    /** @test */
    public function it_returns_test_file_name()
    {
        $this->generator->setType('component');
        // Kebab case of TestVueTest is test-vue-test
        $this->assertEquals('test-vue-test.test.js', $this->generator->getTestFileName());
    }

    /** @test */
    public function it_returns_namespace_replacement()
    {
        $this->generator->setType('component');
        $this->assertEquals('test/components/test-vue-test.test.js', $this->generator->getNamespaceReplacement());
    }

    /** @test */
    public function it_returns_camel_case_replacement()
    {
        $this->assertEquals('testVueTest', $this->generator->getCamelCaseReplacement());
    }

    /** @test */
    public function it_returns_import_replacement()
    {
        $this->generator->setType('component');
        $this->assertEquals('components/TestVueTest.vue', $this->generator->getImportReplacement());

        $this->generator->setType('util');
        $this->assertEquals('utils/testVueTest.js', $this->generator->getImportReplacement());
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

        $originalStubPath = Stub::getBasePath();
        Stub::setBasePath(rtrim(modularityConfig('stubs.path', dirname(__FILE__) . '/stubs')));
        
        $tmpDir = sys_get_temp_dir() . '/vue-test-generator';

        $generator = $this->generator->setType('component')
            ->setName('VueComponent')
            ->setTargetPath($tmpDir);

        $generator->generate();

        Stub::setBasePath($originalStubPath);
    }
}
