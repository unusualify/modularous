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
        $this->assertStringContainsString('$table->softDeletes()', $rendered);
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

    /** @test */
    public function it_handles_remember_token_custom_attribute()
    {
        $schema = 'remember_token:boolean';
        $parser = new SchemaParser($schema);
        $rendered = $parser->up();

        $this->assertStringContainsString('$table->rememberToken()', $rendered);
    }

    /** @test */
    public function get_schemas_returns_empty_array_for_null_schema()
    {
        $parser = new SchemaParser(null);

        $schemas = $parser->getSchemas();

        $this->assertIsArray($schemas);
        $this->assertEmpty($schemas);
    }

    /** @test */
    public function get_schemas_returns_empty_array_for_empty_string()
    {
        $parser = new SchemaParser('');

        $schemas = $parser->getSchemas();

        $this->assertIsArray($schemas);
        $this->assertEmpty($schemas);
    }

    /** @test */
    public function get_column_extracts_column_name()
    {
        $parser = new SchemaParser('name:string');

        $column = $parser->getColumn('name:string');

        $this->assertEquals('name', $column);
    }

    /** @test */
    public function get_attributes_extracts_attributes()
    {
        $parser = new SchemaParser('age:integer:nullable');

        $attributes = $parser->getAttributes('age', 'age:integer:nullable');

        $this->assertIsArray($attributes);
        $this->assertContains('integer', $attributes);
        $this->assertContains('nullable', $attributes);
    }

    /** @test */
    public function has_custom_attribute_returns_true_for_known_custom()
    {
        $parser = new SchemaParser('soft_delete:boolean');

        $this->assertTrue($parser->hasCustomAttribute('soft_delete'));
    }

    /** @test */
    public function has_custom_attribute_returns_false_for_unknown()
    {
        $parser = new SchemaParser('name:string');

        $this->assertFalse($parser->hasCustomAttribute('name'));
    }

    /** @test */
    public function get_custom_attribute_returns_mapped_value()
    {
        $parser = new SchemaParser('soft_delete:boolean');

        $attr = $parser->getCustomAttribute('soft_delete');

        $this->assertIsArray($attr);
        $this->assertContains('softDeletes()', $attr);
    }

    /** @test */
    public function parse_updates_schema_and_returns_parsed_array()
    {
        $parser = new SchemaParser;

        $parsed = $parser->parse('title:string,body:text');

        $this->assertArrayHasKey('title', $parsed);
        $this->assertArrayHasKey('body', $parsed);
        $this->assertEquals(['string'], $parsed['title']);
        $this->assertEquals(['text'], $parsed['body']);
    }

    /** @test */
    public function render_skips_belongs_to_many_and_has_one()
    {
        $schema = 'name:string,tags:belongsToMany';
        $parser = new SchemaParser($schema);
        $rendered = $parser->up();

        $this->assertStringContainsString("\$table->string('name')", $rendered);
        $this->assertStringNotContainsString('tags', $rendered);
    }
}
