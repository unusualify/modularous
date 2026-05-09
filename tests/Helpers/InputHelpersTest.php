<?php

namespace Unusualify\Modularous\Tests\Helpers;

use Unusualify\Modularous\Tests\TestCase;

class InputHelpersTest extends TestCase
{
    /** @test */
    public function test_configure_input_processes_input_array()
    {
        $input = [
            'name' => 'email',
            'type' => 'text',
        ];

        $result = configure_input($input);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
    }

    /** @test */
    public function test_modularous_default_input_returns_default_structure()
    {
        $result = modularous_default_input();

        $this->assertIsArray($result);
        // Default input should have standard keys
    }

    /** @test */
    public function test_hydrate_input_type_processes_type()
    {
        $input = ['type' => 'text'];

        $result = hydrate_input_type($input);

        $this->assertIsArray($result);
    }

    /** @test */
    public function test_hydrate_input_processes_full_input()
    {
        $input = [
            'name' => 'title',
            'type' => 'text',
        ];

        $result = hydrate_input($input);

        $this->assertIsArray($result);
    }

    /** @test */
    public function test_format_input_formats_input_data()
    {
        $input = [
            'name' => 'description',
            'type' => 'textarea',
        ];

        $result = format_input($input);

        $this->assertIsArray($result);
    }

    /** @test */
    public function test_modularous_format_input_wraps_format_input()
    {
        $input = [
            'name' => 'status',
            'type' => 'select',
        ];

        $result = modularous_format_input($input);

        $this->assertIsArray($result);
    }

    /** @test */
    public function test_modularous_format_inputs_processes_multiple_inputs()
    {
        $inputs = [
            ['name' => 'field1', 'type' => 'text'],
            ['name' => 'field2', 'type' => 'number'],
        ];

        $result = modularous_format_inputs($inputs);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }
}
