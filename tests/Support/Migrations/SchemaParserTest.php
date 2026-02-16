<?php

namespace Unusualify\Modularity\Tests\Support\Migrations;

use Nwidart\Modules\Support\Migrations\SchemaParser;
use Unusualify\Modularity\Tests\TestCase;

class SchemaParserTest extends TestCase
{
    /** @test */
    public function it_can_parse_migration_schema_string()
    {
        $schema = 'name:string,age:integer:nullable';
        $parser = new SchemaParser($schema);
        $parsed = $parser->toArray();

        $this->assertArrayHasKey('name', $parsed);
        $this->assertArrayHasKey('age', $parsed);
        $this->assertEquals(['string'], $parsed['name']);
        $this->assertEquals(['integer', 'nullable'], $parsed['age']);
    }

    /** @test */
    public function it_renders_up_migration_fields()
    {
        $schema = 'name:string,active:boolean:default(1)';
        $parser = new SchemaParser($schema);
        $rendered = $parser->up();

        $this->assertStringContainsString("\$table->string('name')", $rendered);
        $this->assertStringContainsString("\$table->boolean('active')->default(1)", $rendered);
    }

    /** @test */
    public function it_renders_down_migration_fields()
    {
        $schema = 'name:string,active:boolean';
        $parser = new SchemaParser($schema);
        $rendered = $parser->down();

        $this->assertStringContainsString("\$table->dropColumn('name')", $rendered);
        $this->assertStringContainsString("\$table->dropColumn('active')", $rendered);
    }

    /** @test */
    public function it_handles_custom_attributes()
    {
        $schema = 'soft_delete:boolean';
        $parser = new SchemaParser($schema);
        $rendered = $parser->up();

        // soft_delete is a custom attribute mapped to softDeletes()
        $this->assertStringContainsString("\$table->softDeletes()", $rendered);
    }

    /** @test */
    public function it_handles_belongs_to_relation()
    {
        $schema = 'user:belongsTo';
        $parser = new SchemaParser($schema);
        $rendered = $parser->up();

        // belongsTo should render foreignId constraint
        $this->assertStringContainsString("\$table->foreignId('user_id')->constrained()", $rendered);
    }

    /** @test */
    public function it_handles_morph_to_relation()
    {
        $schema = 'image:morphTo';
        $parser = new SchemaParser($schema);
        $rendered = $parser->up();

        // morphTo should render both _id and _type columns
        $this->assertStringContainsString("\$table->string('imageable_type')->nullable()", $rendered);
        $this->assertStringContainsString("\$table->unsignedBigInteger('imageable_id')->nullable()", $rendered);
    }
}
