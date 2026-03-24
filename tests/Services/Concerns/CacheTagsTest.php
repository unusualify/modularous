<?php

namespace Unusualify\Modularity\Tests\Services\Concerns;

use Unusualify\Modularity\Services\Concerns\CacheTags;
use Unusualify\Modularity\Tests\TestCase;

class CacheTagsTest extends TestCase
{
    protected $tagService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagService = new ConcreteTagService;
    }

    /** @test */
    public function it_can_generate_module_tags()
    {
        $tags = $this->tagService->getModuleTags('test-module');

        $this->assertIsArray($tags);
        $this->assertContains('modularity', $tags);
        $this->assertContains('modularity:TestModule', $tags);
        $this->assertCount(2, $tags);
    }

    /** @test */
    public function it_can_generate_module_tags_with_only_module_flag()
    {
        $tags = $this->tagService->getModuleTags('test-module', onlyModule: true);

        $this->assertIsArray($tags);
        $this->assertNotContains('modularity', $tags);
        $this->assertContains('modularity:TestModule', $tags);
        $this->assertCount(1, $tags);
    }

    /** @test */
    public function it_can_generate_module_route_tags()
    {
        $tags = $this->tagService->getModuleRouteTags('test-module', 'test-route');

        $this->assertIsArray($tags);
        $this->assertContains('modularity', $tags);
        $this->assertContains('modularity:TestModule', $tags);
        $this->assertContains('modularity:TestModule:TestRoute', $tags);
        $this->assertCount(3, $tags);
    }

    /** @test */
    public function it_can_generate_module_route_tags_with_only_route_flag()
    {
        $tags = $this->tagService->getModuleRouteTags('test-module', 'test-route', onlyRoute: true);

        $this->assertIsArray($tags);
        $this->assertNotContains('modularity', $tags);
        $this->assertNotContains('modularity:TestModule', $tags);
        $this->assertContains('modularity:TestModule:TestRoute', $tags);
        $this->assertCount(1, $tags);
    }

    /** @test */
    public function it_converts_module_names_to_studly_case()
    {
        $tags = $this->tagService->getModuleTags('test-module-name');

        $this->assertContains('modularity:TestModuleName', $tags);
    }

    /** @test */
    public function it_converts_route_names_to_studly_case()
    {
        $tags = $this->tagService->getModuleRouteTags('test-module', 'test-route-name');

        $this->assertContains('modularity:TestModule:TestRouteName', $tags);
    }

    /** @test */
    public function it_can_generate_type_tags()
    {
        $tags = $this->tagService->getTypeTags('test-module', 'test-route', 'count');

        $this->assertIsArray($tags);
        $this->assertContains('modularity', $tags);
        $this->assertContains('modularity:TestModule', $tags);
        $this->assertContains('modularity:TestModule:TestRoute', $tags);
        $this->assertContains('modularity:TestModule:TestRoute:count', $tags);
        $this->assertCount(4, $tags);
    }

    /** @test */
    public function it_can_generate_relation_tag()
    {
        $tag = $this->tagService->generateRelationTag('Company', 1);

        $this->assertEquals('modularity:rel:Company:1', $tag);
    }

    /** @test */
    public function it_extracts_base_name_from_full_class()
    {
        $tag = $this->tagService->generateRelationTag('App\\Models\\Company', 1);

        $this->assertEquals('modularity:rel:Company:1', $tag);
    }

    /** @test */
    public function it_can_generate_multiple_relation_tags()
    {
        $tags = $this->tagService->generateRelationTags([
            'Company' => 1,
            'User' => 2,
        ]);

        $this->assertIsArray($tags);
        $this->assertCount(2, $tags);
        $this->assertContains('modularity:rel:Company:1', $tags);
        $this->assertContains('modularity:rel:User:2', $tags);
    }

    /** @test */
    public function it_can_generate_relation_tags_with_array_of_ids()
    {
        $tags = $this->tagService->generateRelationTags([
            'Company' => [1, 2, 3],
        ]);

        $this->assertIsArray($tags);
        $this->assertCount(3, $tags);
        $this->assertContains('modularity:rel:Company:1', $tags);
        $this->assertContains('modularity:rel:Company:2', $tags);
        $this->assertContains('modularity:rel:Company:3', $tags);
    }

    /** @test */
    public function it_skips_null_ids_in_relation_tags()
    {
        $tags = $this->tagService->generateRelationTags([
            'Company' => [1, null, 3],
            'User' => null,
        ]);

        $this->assertIsArray($tags);
        $this->assertCount(2, $tags);
        $this->assertContains('modularity:rel:Company:1', $tags);
        $this->assertContains('modularity:rel:Company:3', $tags);
    }

    /** @test */
    public function it_returns_empty_array_for_empty_relations()
    {
        $tags = $this->tagService->generateRelationTags([]);

        $this->assertIsArray($tags);
        $this->assertCount(0, $tags);
    }

    /** @test */
    public function it_handles_mixed_single_and_array_ids()
    {
        $tags = $this->tagService->generateRelationTags([
            'Company' => 1,
            'User' => [2, 3],
            'Product' => 4,
        ]);

        $this->assertCount(4, $tags);
        $this->assertContains('modularity:rel:Company:1', $tags);
        $this->assertContains('modularity:rel:User:2', $tags);
        $this->assertContains('modularity:rel:User:3', $tags);
        $this->assertContains('modularity:rel:Product:4', $tags);
    }
}

/**
 * Concrete implementation for testing
 */
class ConcreteTagService
{
    use CacheTags;

    protected $prefix = 'modularity';

    protected function getPrefix(): string
    {
        return $this->prefix;
    }
}
