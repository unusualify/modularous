<?php

namespace Unusualify\Modularous\Tests\Helpers;

use Unusualify\Modularous\Tests\TestCase;

class ArrayHelpersTest extends TestCase
{
    /** @test */
    public function test_array_merge_recursive_distinct_merges_arrays()
    {
        $array1 = ['key' => 'org value'];
        $array2 = ['key' => 'new value'];

        $result = array_merge_recursive_distinct($array1, $array2);

        $this->assertEquals(['key' => 'new value'], $result);
    }

    /** @test */
    public function test_array_merge_recursive_distinct_merges_nested_arrays()
    {
        $array1 = [
            'user' => [
                'name' => 'John',
                'age' => 30,
            ],
        ];
        $array2 = [
            'user' => [
                'age' => 31,
                'city' => 'NYC',
            ],
        ];

        $result = array_merge_recursive_distinct($array1, $array2);

        $this->assertEquals([
            'user' => [
                'name' => 'John',
                'age' => 31,
                'city' => 'NYC',
            ],
        ], $result);
    }

    /** @test */
    public function test_array_merge_recursive_distinct_handles_deep_nesting()
    {
        $array1 = ['a' => ['b' => ['c' => 1]]];
        $array2 = ['a' => ['b' => ['d' => 2]]];

        $result = array_merge_recursive_distinct($array1, $array2);

        $this->assertEquals(['a' => ['b' => ['c' => 1, 'd' => 2]]], $result);
    }

    /** @test */
    public function test_array_merge_recursive_preserve_with_single_array()
    {
        $result = array_merge_recursive_preserve(['a' => 1]);

        $this->assertEquals(['a' => 1], $result);
    }

    /** @test */
    public function test_array_merge_recursive_preserve_with_empty()
    {
        $result = array_merge_recursive_preserve();

        $this->assertEquals([], $result);
    }

    /** @test */
    public function test_array_merge_recursive_preserve_preserves_first_array_values()
    {
        $array1 = ['a' => 1, 'b' => 2];
        $array2 = ['b' => 3, 'c' => 4];

        $result = array_merge_recursive_preserve($array1, $array2);

        // b should be 3 (from array2)
        // c should be added
        $this->assertEquals(['a' => 1, 'b' => 3, 'c' => 4], $result);
    }

    /** @test */
    public function test_array_merge_recursive_preserve_with_nested_arrays()
    {
        $array1 = ['user' => ['name' => 'John', 'age' => 30]];
        $array2 = ['user' => ['age' => 31, 'city' => 'NYC']];

        $result = array_merge_recursive_preserve($array1, $array2);

        $this->assertEquals([
            'user' => [
                'name' => 'John',
                'age' => 31, // Actually overwritten by array2
                'city' => 'NYC',
            ],
        ], $result);
    }

    /** @test */
    public function test_array_export_with_simple_array()
    {
        $array = ['name' => 'John', 'age' => 30];

        $result = array_export($array, true);

        $this->assertIsString($result);
        $this->assertStringContainsString('[', $result);
        $this->assertStringContainsString('name', $result);
        $this->assertStringContainsString('John', $result);
    }

    /** @test */
    public function test_array_export_with_non_array()
    {
        $result = array_export('string', true);

        $this->assertEquals("'string'", $result);
    }

    /** @test */
    public function test_array_export_returns_string_when_return_true()
    {
        $result = array_export(['a' => 1], true);

        $this->assertIsString($result);
    }

    /** @test */
    public function test_php_array_file_content_generates_php_file()
    {
        $array = ['key' => 'value'];

        $result = php_array_file_content($array);

        $this->assertStringContainsString('<?php', $result);
        $this->assertStringContainsString('return', $result);
        $this->assertStringContainsString('key', $result);
        $this->assertStringContainsString('value', $result);
    }

    /** @test */
    public function test_array_to_object_converts_array()
    {
        $array = ['name' => 'John', 'age' => 30];

        $result = array_to_object($array);

        $this->assertIsObject($result);
        $this->assertEquals('John', $result->name);
        $this->assertEquals(30, $result->age);
    }

    /** @test */
    public function test_array_to_object_handles_nested_arrays()
    {
        $array = [
            'user' => [
                'name' => 'John',
                'age' => 30,
            ],
        ];

        $result = array_to_object($array);

        $this->assertIsObject($result);
        $this->assertIsObject($result->user);
        $this->assertEquals('John', $result->user->name);
    }

    /** @test */
    public function test_object_to_array_converts_object()
    {
        $object = (object) ['name' => 'John', 'age' => 30];

        $result = object_to_array($object);

        $this->assertIsArray($result);
        $this->assertEquals(['name' => 'John', 'age' => 30], $result);
    }

    /** @test */
    public function test_object_to_array_handles_nested_objects()
    {
        $object = (object) [
            'user' => (object) [
                'name' => 'John',
                'age' => 30,
            ],
        ];

        $result = object_to_array($object);

        $this->assertIsArray($result);
        $this->assertIsArray($result['user']);
        $this->assertEquals('John', $result['user']['name']);
    }

    /** @test */
    public function test_nested_array_merge_merges_nested_values()
    {
        $array1 = ['a' => 1, 'b' => ['c' => 2]];
        $array2 = ['b' => ['d' => 3], 'e' => 4];

        $result = nested_array_merge($array1, $array2);

        $this->assertEquals([
            'a' => 1,
            'b' => ['c' => 2, 'd' => 3],
            'e' => 4,
        ], $result);
    }

    /** @test */
    public function test_nested_array_merge_handles_null_values()
    {
        $array1 = ['a' => 1, 'b' => 2];
        $array2 = ['b' => null, 'c' => 3];

        $result = nested_array_merge($array1, $array2);

        // null values should keep original value
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $result);
    }

    /** @test */
    public function test_nested_array_merge_handles_empty_strings()
    {
        $array1 = ['a' => 'value', 'b' => 'original'];
        $array2 = ['b' => '', 'c' => 'new'];

        $result = nested_array_merge($array1, $array2);

        // Empty strings should keep original value
        $this->assertEquals(['a' => 'value', 'b' => 'original', 'c' => 'new'], $result);
    }

    /** @test */
    public function test_array_merge_conditional_merges_with_all_true_conditions()
    {
        $array1 = ['a' => 1];
        $arrays = [['b' => 2], ['c' => 3]];

        $result = array_merge_conditional($array1, $arrays, true, true);

        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $result);
    }

    /** @test */
    public function test_array_merge_conditional_skips_false_conditions()
    {
        $array1 = ['a' => 1];
        $arrays = [['b' => 2], ['c' => 3]];

        $result = array_merge_conditional($array1, $arrays, true, false);

        $this->assertEquals(['a' => 1, 'b' => 2], $result);
    }

    /** @test */
    public function test_array_merge_conditional_handles_null_base_array()
    {
        $arrays = [['a' => 1], ['b' => 2]];

        $result = array_merge_conditional(null, $arrays, true, true);

        $this->assertEquals(['a' => 1, 'b' => 2], $result);
    }

    /** @test */
    public function test_array_merge_conditional_defaults_to_true_when_no_conditions()
    {
        $array1 = ['a' => 1];
        $arrays = [['b' => 2], ['c' => 3]];

        $result = array_merge_conditional($array1, $arrays);

        // Should merge all since conditions default to true
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $result);
    }

    /** @test */
    public function test_array_except_removes_specified_keys()
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];

        $result = array_except($array, ['b', 'd']);

        $this->assertEquals(['a' => 1, 'c' => 3], $result);
    }

    /** @test */
    public function test_array_except_handles_non_existent_keys()
    {
        $array = ['a' => 1, 'b' => 2];

        $result = array_except($array, ['c', 'd']);

        $this->assertEquals(['a' => 1, 'b' => 2], $result);
    }

    /** @test */
    public function test_array_except_handles_empty_excepts()
    {
        $array = ['a' => 1, 'b' => 2];

        $result = array_except($array, []);

        $this->assertEquals(['a' => 1, 'b' => 2], $result);
    }
}
