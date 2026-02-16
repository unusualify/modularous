<?php

namespace Unusualify\Modularity\Tests\Support\Decomposers;

use Unusualify\Modularity\Support\Decomposers\SchemaParser;
use Unusualify\Modularity\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularity\Facades\UFinder;

class SchemaParserTest extends TestCase
{
    protected $parser;

    protected function setUp(): void
    {
        parent::setUp();
        
        Config::set('modularity.schemas.default_inputs', []);
        Config::set('modularity.schemas.default_pre_headers', []);
        Config::set('modularity.schemas.default_post_headers', []);
        Config::set('modularity.default_header', []);
        
        // Mock UFinder to return null for repository lookups by default
        UFinder::shouldReceive('getRouteRepository')->andReturn(null);
    }

    /** @test */
    public function it_can_parse_columns_from_schema()
    {
        $parser = new SchemaParser('name:string,age:integer', false);
        $columns = $parser->getColumns();
        
        $this->assertContains('name', $columns);
        $this->assertContains('age', $columns);
    }

    /** @test */
    public function it_can_get_fillables()
    {
        $parser = new SchemaParser('name:string,age:integer', false);
        $fillables = $parser->getFillables();
        
        $this->assertContains('name', $fillables);
        $this->assertContains('age', $fillables);
    }

    /** @test */
    public function it_can_generate_input_formats()
    {
        $parser = new SchemaParser('name:string,description:text,active:boolean', false);
        $inputs = $parser->getInputFormats();
        
        $this->assertCount(3, $inputs);
        
        $this->assertEquals('text', $inputs[0]['type']);
        $this->assertEquals('name', $inputs[0]['name']);
        
        $this->assertEquals('textarea', $inputs[1]['type']);
        $this->assertEquals('description', $inputs[1]['name']);
    }

    /** @test */
    public function it_can_handle_belongs_to_in_schema()
    {
        $parser = new SchemaParser('user:belongsTo', false);
        $columns = $parser->getColumns();
        
        // belongsTo should convert to foreign key
        $this->assertContains('user_id', $columns);
    }

    /** @test */
    public function it_can_check_for_soft_delete()
    {
        $parser = new SchemaParser('name:string,soft_delete:boolean', false);
        $this->assertTrue($parser->hasSoftDelete());
        
        $parser = new SchemaParser('name:string', false);
        $this->assertFalse($parser->hasSoftDelete());
    }
}
