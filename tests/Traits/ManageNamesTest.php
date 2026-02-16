<?php

namespace Unusualify\Modularity\Tests\Traits;

use Unusualify\Modularity\Traits\ManageNames;
use Unusualify\Modularity\Tests\TestCase;

class ManageNamesTest extends TestCase
{
    protected $target;

    protected function setUp(): void
    {
        parent::setUp();
        $this->target = new class {
            use ManageNames;
            
            // Expose protected methods for testing
            public function callProtected($method, ...$args) {
                return $this->{$method}(...$args);
            }
        };
    }

    /** @test */
    public function it_can_format_names()
    {
        $this->assertEquals('TestModule', $this->target->getStudlyName('test-module'));
        $this->assertEquals('testmodule', $this->target->getLowerName('TestModule'));
        $this->assertEquals('Items', $this->target->getPlural('Item'));
        $this->assertEquals('Item', $this->target->getSingular('Items'));
        $this->assertEquals('Test Case', $this->target->getHeadline('test_case'));
        $this->assertEquals('testCase', $this->target->getCamelCase('test-case'));
        $this->assertEquals('test-case', $this->target->getKebabCase('TestCase'));
        $this->assertEquals('test_case', $this->target->getSnakeCase('TestCase'));
        $this->assertEquals('TestCase', $this->target->getPascalCase('test-case'));
    }

    /** @test */
    public function it_can_generate_db_table_name()
    {
        $this->assertEquals('test_modules', $this->target->getDBTableName('TestModule'));
        $this->assertEquals('items', $this->target->getDBTableName('Item'));
        $this->assertEquals('complex_model_names', $this->target->getDBTableName('ComplexModelName'));
    }

    /** @test */
    public function it_can_handle_foreign_keys()
    {
        $this->assertEquals('User', $this->target->callProtected('getStudlyNameFromForeignKey', 'user_id'));
        $this->assertEquals('user', $this->target->callProtected('getCamelNameFromForeignKey', 'user_id'));
        $this->assertEquals('user', $this->target->callProtected('getSnakeNameFromForeignKey', 'user_id'));
        
        $this->assertNull($this->target->callProtected('getStudlyNameFromForeignKey', 'invalid_key'));
        
        $this->assertEquals('user_id', $this->target->callProtected('getForeignKeyFromName', 'User'));
        $this->assertEquals('users', $this->target->callProtected('getTableNameFromName', 'User'));
    }

    /** @test */
    public function it_can_generate_pivot_table_names()
    {
        $this->assertEquals('post_tag', $this->target->callProtected('getPivotTableName', 'Post', 'Tag'));
        $this->assertEquals('blog_post_category', $this->target->callProtected('getPivotTableName', 'BlogPost', 'Category'));
    }
}
