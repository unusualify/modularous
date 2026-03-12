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
        
        // Fallback: table name converted to StudlyCase
        $model = new class extends Model {
            protected $table = 'posts';
        };
        $this->assertEquals('Post', $tester->run($model));
    }

    /** @test */
    public function it_can_extract_module_name_from_model_in_modules_namespace()
    {
        $tester = new class { use ModularModel; public function run($m) { return $this->getModuleNameFromModel($m); } };

        $model = new \Modules\TestModule\Entities\StubModel();
        $model->setRawAttributes(['id' => 1]);
        $this->assertEquals('TestModule', $tester->run($model));
    }

    /** @test */
    public function it_can_extract_module_route_name_from_model()
    {
        $tester = new class { use ModularModel; public function run($m) { return $this->getModuleRouteNameFromModel($m); } };

        $model = new class extends Model {
            protected $table = 'posts';
        };
        $model->setRawAttributes(['id' => 1]);
        $routeName = $tester->run($model);
        $this->assertNotNull($routeName);
        $this->assertIsString($routeName);
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
    public function it_gets_responsive_items_from_array()
    {
        $tester = new class { use ResponsiveVisibility; };
        $items = [
            ['name' => 'a', 'responsive' => ['hideOn' => 'sm']],
            ['name' => 'b'],
        ];
        $result = $tester->getResponsiveItems($items);
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertStringContainsString('d-sm-none', $result[0]['class']);
    }

    /** @test */
    public function it_gets_responsive_items_from_collection()
    {
        $tester = new class { use ResponsiveVisibility; };
        $items = collect([
            ['name' => 'a', 'responsive' => ['showOn' => 'md']],
        ]);
        $result = $tester->getResponsiveItems($items);
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertStringContainsString('d-md-flex', $result->first()['class']);
    }

    /** @test */
    public function it_throws_for_invalid_items_type_in_get_responsive_items()
    {
        $tester = new class { use ResponsiveVisibility; };
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid items type');
        $tester->getResponsiveItems('invalid');
    }

    /** @test */
    public function it_checks_has_responsive_settings()
    {
        $tester = new class { use ResponsiveVisibility; };
        $this->assertTrue($tester->hasResponsiveSettings(['responsive' => ['hideOn' => 'sm']]));
        $this->assertFalse($tester->hasResponsiveSettings(['name' => 'foo']));
        $obj = (object) ['responsive' => []];
        $this->assertTrue($tester->hasResponsiveSettings($obj));
    }

    /** @test */
    public function it_applies_responsive_classes_with_custom_search_key()
    {
        $tester = new class { use ResponsiveVisibility; };
        $item = ['name' => 'test', 'visibility' => ['hideOn' => 'md']];
        $result = $tester->applyResponsiveClasses($item, 'visibility');
        $this->assertStringContainsString('d-md-none', $result['class']);
    }

    /** @test */
    public function it_applies_responsive_classes_with_custom_display()
    {
        $tester = new class { use ResponsiveVisibility; };
        $item = ['responsive' => ['showOn' => 'lg']];
        $result = $tester->applyResponsiveClasses($item, null, 'block');
        $this->assertStringContainsString('d-lg-block', $result['class']);
    }

    /** @test */
    public function it_throws_for_invalid_display_value()
    {
        $tester = new class { use ResponsiveVisibility; };
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid display value');
        $tester->applyResponsiveClasses(['responsive' => ['hideOn' => 'sm']], null, 'invalid');
    }

    /** @test */
    public function it_generates_hide_below_classes()
    {
        $tester = new class { use ResponsiveVisibility; };
        $item = ['responsive' => ['hideBelow' => 'lg']];
        $result = $tester->applyResponsiveClasses($item);
        $this->assertStringContainsString('d-none', $result['class']);
        $this->assertStringContainsString('d-lg-flex', $result['class']);
    }

    /** @test */
    public function it_generates_hide_above_classes()
    {
        $tester = new class { use ResponsiveVisibility; };
        $item = ['responsive' => ['hideAbove' => 'md']];
        $result = $tester->applyResponsiveClasses($item);
        $this->assertStringContainsString('d-lg-none', $result['class']);
    }

    /** @test */
    public function it_generates_breakpoints_visibility_classes()
    {
        $tester = new class { use ResponsiveVisibility; };
        $item = ['responsive' => ['breakpoints' => ['sm' => true, 'md' => false]]];
        $result = $tester->applyResponsiveClasses($item);
        $this->assertStringContainsString('d-sm-flex', $result['class']);
        $this->assertStringContainsString('d-md-none', $result['class']);
    }

    /** @test */
    public function it_returns_item_unchanged_when_no_responsive_settings()
    {
        $tester = new class { use ResponsiveVisibility; };
        $item = ['name' => 'plain'];
        $result = $tester->applyResponsiveClasses($item);
        $this->assertEquals($item, $result);
    }

    /** @test */
    public function it_applies_responsive_classes_to_object_item()
    {
        $tester = new class { use ResponsiveVisibility; };
        $item = (object) ['name' => 'test', 'responsive' => ['hideOn' => 'xl'], 'class' => 'existing'];
        $result = $tester->applyResponsiveClasses($item);
        $this->assertStringContainsString('d-xl-none', $result->class);
        $this->assertStringContainsString('existing', $result->class);
    }

    /** @test */
    public function it_applies_responsive_classes_with_custom_class_notation()
    {
        $tester = new class { use ResponsiveVisibility; };
        $item = ['responsive' => ['hideOn' => 'sm'], 'attributes' => ['class' => 'base']];
        $result = $tester->applyResponsiveClasses($item, null, 'flex', 'attributes.class');
        $this->assertStringContainsString('d-sm-none', $result['attributes']['class']);
    }

    /** @test */
    public function it_handles_hide_on_as_array()
    {
        $tester = new class { use ResponsiveVisibility; };
        $item = ['responsive' => ['hideOn' => ['sm', 'md']]];
        $result = $tester->applyResponsiveClasses($item);
        $this->assertStringContainsString('d-sm-none', $result['class']);
        $this->assertStringContainsString('d-md-none', $result['class']);
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
