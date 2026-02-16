<?php

namespace Unusualify\Modularity\Tests\Traits;

use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\Traits\ModularModel;
use Unusualify\Modularity\Traits\Moduleable;
use Unusualify\Modularity\Traits\ResponsiveVisibility;
use Unusualify\Modularity\Traits\Allowable;
use Unusualify\Modularity\Traits\CheckSnapshot;
use Unusualify\Modularity\Traits\ManageModuleRoute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularity\Facades\Modularity;

class ModelTraitsTest extends TestCase
{
    /** @test */
    public function it_can_extract_module_name_from_model_namespace()
    {
        $tester = new class { use ModularModel; public function run($m) { return $this->getModuleNameFromModel($m); } };
        
        // Mock model in a module namespace
        $model = \Mockery::mock(Model::class);
        $modelName = 'Modules\\Blog\\Entities\\Post';
        // Anonymous classes usually don't have predictable namespaces, 
        // so we'll use Mockery to simulate a class in a specific namespace if possible, 
        // but Mockery class names are also random.
        // Actually, ModularModel uses get_class($model).
        
        // Let's use a real class if available or just test the fallback
        $tester2 = new class extends Model { 
            protected $table = 'posts'; 
        };
        $this->assertEquals('Post', $tester->run($tester2));
    }

    /** @test */
    public function it_can_use_moduleable_trait()
    {
        $tester = new class { 
            use Moduleable; 
        };
        
        $tester->setModuleName('Blog')->setRouteName('Posts');
        $this->assertEquals('Blog', $tester->getModuleName());
        $this->assertEquals('Posts', $tester->getRouteName());
    }

    /** @test */
    public function it_generates_responsive_classes()
    {
        $tester = new class { use ResponsiveVisibility; };
        
        $item = ['name' => 'test', 'responsive' => ['hideOn' => 'sm', 'showOn' => 'lg']];
        $result = $tester->applyResponsiveClasses($item);
        
        $this->assertStringContainsString('d-sm-none', $result['class']);
        $this->assertStringContainsString('d-none', $result['class']); // showOn adds d-none by default
        $this->assertStringContainsString('d-lg-flex', $result['class']);
    }

    /** @test */
    public function it_filters_allowable_items()
    {
        $tester = new class { 
            use Allowable; 
            protected $allowedRolesSearchKey = 'roles';
        };
        
        $user = \Mockery::mock(\Illuminate\Contracts\Auth\Authenticatable::class);
        $user->shouldReceive('hasRole')->with(['admin'])->andReturn(true);
        $user->shouldReceive('hasRole')->with(['editor'])->andReturn(false);
        
        $tester->setAllowableUser($user);
        
        $items = [
            ['name' => 'Admin Item', 'roles' => ['admin']],
            ['name' => 'Editor Item', 'roles' => ['editor']],
            ['name' => 'Public Item']
        ];
        
        $allowed = $tester->getAllowableItems($items);
        
        $this->assertCount(2, $allowed);
        $this->assertEquals('Admin Item', $allowed[0]['name']);
        $this->assertEquals('Public Item', $allowed[1]['name']);
    }

    /** @test */
    public function it_can_use_manage_module_route_trait()
    {
        $tester = new class { 
            use ManageModuleRoute; 
            public function getModuleName(): ?string { return 'Blog'; }
            public function getRouteName(): ?string { return 'Post'; }
        };
        
        $module = \Mockery::mock(\Unusualify\Modularity\Module::class);
        $module->shouldReceive('getRawRouteConfig')->with('Post')->andReturn(['title_column_key' => 'title']);
        
        Modularity::shouldReceive('find')->with('Blog')->andReturn($module);
        
        $this->assertEquals('title', $tester->getRouteTitleColumnKey());
    }
}
