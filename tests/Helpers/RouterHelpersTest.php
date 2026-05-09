<?php

namespace Unusualify\Modularous\Tests\Helpers;

use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Tests\TestCase;

class RouterHelpersTest extends TestCase
{
    /** @test */
    public function test_array_to_query_string_converts_simple_array()
    {
        $data = ['page' => 2, 'limit' => 10];

        $result = array_to_query_string($data);

        $this->assertEquals('page=2&limit=10', $result);
    }

    /** @test */
    public function test_array_to_query_string_encodes_special_characters()
    {
        $data = ['search' => 'test query', 'tag' => 'PHP & Laravel'];

        $result = array_to_query_string($data);

        $this->assertStringContainsString('search=test%20query', $result);
        $this->assertStringContainsString('tag=PHP%20%26%20Laravel', $result);
    }

    /** @test */
    public function test_array_to_query_string_handles_json_objects()
    {
        $data = [
            'filter' => ['status' => 'active', 'type' => 'user'],
            'page' => 1,
        ];

        $result = array_to_query_string($data);

        $this->assertStringContainsString('filter=', $result);
        $this->assertStringContainsString('page=1', $result);
        // JSON should be included
        $this->assertStringContainsString('status', $result);
    }

    /** @test */
    public function test_array_to_query_string_handles_array_values()
    {
        $data = [
            'ids' => [1, 2, 3],
            'page' => 1,
        ];

        $result = array_to_query_string($data);

        $this->assertStringContainsString('ids', $result);
        $this->assertStringContainsString('page=1', $result);
    }

    /** @test */
    public function test_merge_url_query_merges_with_new_params()
    {
        $url = 'https://example.com/path?existing=value';
        $data = ['new' => 'param', 'page' => 2];

        $result = merge_url_query($url, $data);

        $this->assertStringContainsString('https://example.com/path?', $result);
        $this->assertStringContainsString('existing=value', $result);
        $this->assertStringContainsString('new=param', $result);
        $this->assertStringContainsString('page=2', $result);
    }

    /** @test */
    public function test_merge_url_query_overwrites_existing_params()
    {
        $url = 'https://example.com/path?page=1&limit=10';
        $data = ['page' => 2];

        $result = merge_url_query($url, $data);

        $this->assertStringContainsString('page=2', $result);
        $this->assertStringNotContainsString('page=1', $result);
        $this->assertStringContainsString('limit=10', $result);
    }

    /** @test */
    public function test_merge_url_query_handles_url_without_existing_query()
    {
        $url = 'https://example.com/path';
        $data = ['page' => 1, 'limit' => 20];

        $result = merge_url_query($url, $data);

        $this->assertEquals('https://example.com/path?page=1&limit=20', $result);
    }

    /** @test */
    public function test_merge_url_query_handles_object_data()
    {
        $url = 'https://example.com/path';
        $data = (object) ['key' => 'value'];

        $result = merge_url_query($url, $data);

        $this->assertStringContainsString('key=value', $result);
    }

    /** @test */
    public function test_merge_url_query_handles_nested_arrays()
    {
        $url = 'https://example.com/path';
        $data = ['filter' => ['status' => 'active']];

        $result = merge_url_query($url, $data);

        $this->assertStringContainsString('filter=', $result);
        $this->assertStringContainsString('status', $result);
    }

    /** @test */
    public function test_resolve_route_returns_url_for_non_existent_route()
    {
        $definition = 'non.existent.route';

        $result = resolve_route($definition);

        // Should return the string as-is when route doesn't exist
        $this->assertEquals('non.existent.route', $result);
    }

    /** @test */
    public function test_resolve_route_handles_array_definition()
    {
        $definition = ['test.route', ['param' => 'value']];

        $result = resolve_route($definition);

        // When route doesn't exist, it returns the route name which is the first element
        // Looking at the code: $url is initialized to $definition, so it returns the whole array
        $this->assertEquals(['test.route', ['param' => 'value']], $result);
    }
}
