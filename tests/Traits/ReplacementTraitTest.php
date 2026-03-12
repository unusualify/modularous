<?php

namespace Unusualify\Modularity\Tests\Traits;

use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\Traits\ReplacementTrait;
use Illuminate\Support\Facades\Config;

class ReplacementTraitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('modularity.stubs.files', ['file1', 'file2']);
        Config::set('modularity.stubs.replacements', [
            'json' => ['NAME', 'LOWER_NAME', 'PROVIDER_NAMESPACE'],
            'php' => ['NAME'],
            'unknown_stub' => ['KEY'],
        ]);
        Config::set('modules.namespace', 'Modules');
        Config::set('modularity.composer.vendor', 'acme');
        Config::set('modularity.composer.author.name', 'Jane Doe');
        Config::set('modularity.composer.author.email', 'jane@example.com');
    }

    protected function createTester(): object
    {
        return new class {
            use ReplacementTrait;

            public function setName($name)
            {
                $this->name = $name;
            }

            public function getName()
            {
                return $this->name;
            }

            public function setModuleName($name)
            {
                $this->moduleName = $name;
            }

            public function getReplacementPublic($stub)
            {
                return $this->getReplacement($stub);
            }

            public function getStubContentsPublic($stub)
            {
                return $this->getStubContents($stub);
            }
        };
    }

    public function test_get_files_returns_config_files()
    {
        $tester = $this->createTester();
        $this->assertEquals(['file1', 'file2'], $tester->getFiles());
    }

    public function test_get_replacements_returns_config_replacements()
    {
        $tester = $this->createTester();
        $this->assertArrayHasKey('json', $tester->getReplacements());
    }

    public function test_make_replaces_returns_null_for_unknown_key()
    {
        $tester = $this->createTester();
        $tester->setName('Foo');
        $replaces = $tester->makeReplaces(['UNKNOWN_KEY']);
        $this->assertNull($replaces['UNKNOWN_KEY']);
    }

    public function test_make_replaces_resolves_replacement_methods()
    {
        $tester = $this->createTester();
        $tester->setName('TestModule');
        $replaces = $tester->makeReplaces(['LOWER_NAME', 'STUDLY_NAME']);
        $this->assertEquals('testmodule', $replaces['LOWER_NAME']);
        $this->assertEquals('TestModule', $replaces['STUDLY_NAME']);
    }

    public function test_replace_string_replaces_all_placeholders()
    {
        $tester = $this->createTester();
        $tester->setName('MyModule');
        $result = $tester->replaceString('$LOWER_NAME$ $STUDLY_NAME$ $KEBAB_CASE$ $SNAKE_CASE$ $CAMEL_CASE$');
        $this->assertStringContainsString('mymodule', $result);
        $this->assertStringContainsString('MyModule', $result);
    }

    public function test_get_replacement_returns_empty_for_unknown_stub()
    {
        $tester = $this->createTester();
        $result = $tester->getReplacementPublic('nonexistent');
        $this->assertEquals([], $result);
    }

    public function test_get_replacement_adds_provider_namespace_for_json_stub()
    {
        $tester = $this->createTester();
        $tester->setName('Blog');
        $result = $tester->getReplacementPublic('json');
        $this->assertArrayHasKey('PROVIDER_NAMESPACE', $result);
    }

    public function test_get_replacement_adds_provider_namespace_for_composer_stub()
    {
        Config::set('modularity.stubs.replacements', [
            'composer' => ['NAME'],
        ]);
        $tester = $this->createTester();
        $tester->setName('Blog');
        $result = $tester->getReplacementPublic('composer');
        $this->assertArrayHasKey('PROVIDER_NAMESPACE', $result);
    }

    public function test_module_name_replacements()
    {
        $tester = $this->createTester();
        $tester->setName('Post');
        $tester->setModuleName('Blog');

        $replaces = $tester->makeReplaces([
            'LOWER_MODULE_NAME',
            'KEBAB_MODULE_NAME',
            'STUDLY_MODULE_NAME',
        ]);
        $this->assertEquals('blog', $replaces['LOWER_MODULE_NAME']);
        $this->assertEquals('blog', $replaces['KEBAB_MODULE_NAME']);
        $this->assertEquals('Blog', $replaces['STUDLY_MODULE_NAME']);
    }

    public function test_vendor_replacement()
    {
        $tester = $this->createTester();
        $replaces = $tester->makeReplaces(['VENDOR']);
        $this->assertEquals('acme', $replaces['VENDOR']);
    }

    public function test_module_namespace_replacement()
    {
        $tester = $this->createTester();
        $replaces = $tester->makeReplaces(['MODULE_NAMESPACE']);
        $this->assertEquals('Modules', $replaces['MODULE_NAMESPACE']);
    }

    public function test_author_replacements()
    {
        $tester = $this->createTester();
        $replaces = $tester->makeReplaces(['AUTHOR', 'AUTHOR_EMAIL']);
        $this->assertEquals('Jane Doe', $replaces['AUTHOR']);
        $this->assertEquals('jane@example.com', $replaces['AUTHOR_EMAIL']);
    }

    public function test_get_stub_contents_renders_stub()
    {
        $tester = $this->createTester();
        $tester->setName('TestModule');
        Config::set('modularity.stubs.replacements', ['json' => ['LOWER_NAME', 'STUDLY_NAME']]);

        $contents = $tester->getStubContentsPublic('json');
        $this->assertIsString($contents);
    }
}
