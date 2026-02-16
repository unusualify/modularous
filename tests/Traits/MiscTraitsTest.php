<?php

namespace Unusualify\Modularity\Tests\Traits;

use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\Traits\Pretending;
use Unusualify\Modularity\Traits\Verbosity;
use Unusualify\Modularity\Traits\Traitify;
use Unusualify\Modularity\Traits\ReplacementTrait;
use Unusualify\Modularity\Traits\ResolveConnector;
use Unusualify\Modularity\Traits\SerializeModel;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MiscTraitsTest extends TestCase
{
    /** @test */
    public function it_can_use_pretending_trait()
    {
        $tester = new class { use Pretending; };
        
        $this->assertFalse($tester->pretending());
        $tester->setPretending(true);
        $this->assertTrue($tester->pretending());
    }

    /** @test */
    public function it_can_use_verbosity_trait()
    {
        $tester = new class { use Verbosity; };
        
        $this->assertEquals(OutputInterface::VERBOSITY_NORMAL, $tester->getVerbosity());
        
        $tester->setVerbosity('vv');
        $this->assertEquals(OutputInterface::VERBOSITY_VERY_VERBOSE, $tester->getVerbosity());
        $this->assertTrue($tester->isVeryVerbose());
        $this->assertFalse($tester->isDebug());
        
        $tester->setVerbosity('quiet');
        $this->assertTrue($tester->isQuiet());
    }

    /** @test */
    public function it_can_use_traitify_trait()
    {
        // Using a named class to have a predictable class_basename
        $tester = new class extends \stdClass { 
            use Traitify;
            public function testMethodTraitify() { return 'success'; }
            public function run() { return $this->traitsMethods('testMethod'); }
        };

        $methods = $tester->run();
        $this->assertContains('testMethodTraitify', $methods);
    }

    /** @test */
    public function it_can_use_replacement_trait()
    {
        Config::set('modularity.stubs.files', ['file1', 'file2']);
        Config::set('modularity.stubs.replacements', ['json' => ['NAME', 'LOWER_NAME']]);
        
        $tester = new class { 
            use ReplacementTrait; 
            public function setName($name) { $this->name = $name; }
            public function getName() { return $this->name; }
            protected function getNameReplacement() { return 'TestModule'; }
            protected function getLowerNameReplacement() { return 'testmodule'; }
        };
        $tester->setName('TestModule');

        $this->assertEquals(['file1', 'file2'], $tester->getFiles());
        $this->assertEquals(['json' => ['NAME', 'LOWER_NAME']], $tester->getReplacements());
        
        $replaces = $tester->makeReplaces(['LOWER_NAME', 'STUDLY_NAME']);
        $this->assertEquals('testmodule', $replaces['LOWER_NAME']);
        $this->assertEquals('TestModule', $replaces['STUDLY_NAME']);

        $replaced = $tester->replaceString('Hello $LOWER_NAME$');
        $this->assertEquals('Hello testmodule', $replaced);
    }

    /** @test */
    public function it_can_serialize_and_unserialize_models()
    {
        $tester = new class { use SerializeModel; };
        
        $model = new class extends Model {
            protected $guarded = [];
            protected $attributes = ['name' => 'Original'];
        };

        $serialized = $tester->serializeModel($model);
        
        $this->assertEquals('Original', $serialized['attributes']['name']);
        $this->assertEquals(get_class($model), $serialized['class']);

        $unserialized = $tester->unserializeModel($serialized);
        $this->assertInstanceOf(get_class($model), $unserialized);
        $this->assertEquals('Original', $unserialized->name);
        $this->assertTrue($unserialized->exists);
    }
}
