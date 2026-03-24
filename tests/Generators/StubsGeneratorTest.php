<?php

namespace Unusualify\Modularity\Tests\Generators;

use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Mockery;
use Unusualify\Modularity\Generators\StubsGenerator;
use Unusualify\Modularity\Module;
use Unusualify\Modularity\Tests\TestCase;

class StubsGeneratorTest extends TestCase
{
    protected $generator;

    protected $config;

    protected $filesystem;

    protected $console;

    protected $module;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Mockery::mock(Config::class);
        $this->filesystem = Mockery::mock(Filesystem::class);
        $this->console = Mockery::mock(Console::class);
        $this->module = Mockery::mock(Module::class);
        $this->module->shouldReceive('getName')->andReturn('TestModule');

        $this->generator = new StubsGenerator(
            'TestStubs',
            $this->config,
            $this->filesystem,
            $this->console,
            $this->module
        );
    }

    /** @test */
    public function it_can_set_only_stubs()
    {
        $this->generator->setOnly(['stub1', 'stub2']);
        // onlyStubs is public
        $this->assertEquals(['stub1', 'stub2'], $this->generator->onlyStubs);
    }

    /** @test */
    public function it_can_set_except_stubs()
    {
        $this->generator->setExcept(['stub3']);
        // exceptStubs is public
        $this->assertEquals(['stub3'], $this->generator->exceptStubs);
    }

    /** @test */
    public function it_verifies_forcible_stub_with_force_true()
    {
        $this->generator->setForce(true);
        $this->assertTrue($this->generator->forcibleStub('any_stub'));
    }

    /** @test */
    public function it_verifies_forcible_stub_with_fix_true()
    {
        $this->generator->setFix(true);

        // No only/except set, should return true (it falls through to return true after dd removal)
        // Wait, looking at the code:
        /*
        if ($this->fix) {
            if (! empty($this->onlyStubs)) {
                return in_array($stub, $this->onlyStubs);
            }
            if (! empty($this->exceptStubs)) {
                return ! in_array($stub, $this->exceptStubs);
            }
            return true;
        }
        */
        $this->assertTrue($this->generator->forcibleStub('any_stub'));

        $this->generator->setOnly(['only_this']);
        $this->assertTrue($this->generator->forcibleStub('only_this'));
        $this->assertFalse($this->generator->forcibleStub('other'));

        $this->generator->setOnly([]);
        $this->generator->setExcept(['not_this']);
        $this->assertFalse($this->generator->forcibleStub('not_this'));
        $this->assertTrue($this->generator->forcibleStub('anything_else'));
    }

    /** @test */
    public function it_returns_zero_on_generate_when_no_existing_config()
    {
        // Use anonymous class to avoid real stub loading and Mockery issues with protected trait methods
        $generator = new class('TestStubs', $this->config, $this->filesystem, $this->console, $this->module) extends StubsGenerator
        {
            protected function getStubContents($stub)
            {
                return 'stub content';
            }
        };

        $this->module->shouldReceive('getRawRouteConfig')->with('TestStubs')->andReturn([]);
        $this->module->shouldReceive('getPath')->andReturn('/tmp/module');

        $this->config->shouldReceive('get')->with('modularity.stubs.files')->andReturn(['stub' => 'file.php']);
        $this->filesystem->shouldReceive('isDirectory')->andReturn(true);
        $this->filesystem->shouldReceive('put')->atLeast()->once();

        $this->console->shouldReceive('info');

        $this->assertEquals(0, $generator->generate());
    }

    /** @test */
    public function it_returns_error_when_config_exists_without_force_or_fix()
    {
        $this->module->shouldReceive('getRawRouteConfig')->with('TestStubs')->andReturn(['existing' => 'config']);
        $this->console->shouldReceive('error')->with('Module Route [TestStubs] files already exist!');

        $result = $this->generator->generate();
        $this->assertEquals(E_ERROR, $result);
    }

    /** @test */
    public function it_returns_zero_when_config_exists_with_force_flag()
    {
        $generator = new class('TestStubs', $this->config, $this->filesystem, $this->console, $this->module) extends StubsGenerator
        {
            protected function getStubContents($stub)
            {
                return 'stub content';
            }
        };

        $generator->setForce(true);

        $this->module->shouldReceive('getRawRouteConfig')->with('TestStubs')->andReturn(['existing' => 'config']);
        $this->module->shouldReceive('getPath')->andReturn('/tmp/module');
        $this->config->shouldReceive('get')->with('modularity.stubs.files')->andReturn(['stub' => 'file.php']);
        $this->filesystem->shouldReceive('isDirectory')->andReturn(true);
        $this->filesystem->shouldReceive('put')->atLeast()->once();
        $this->console->shouldReceive('info');

        $result = $generator->generate();
        $this->assertEquals(0, $result);
    }
}
