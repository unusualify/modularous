<?php

namespace Unusualify\Modularity\Tests\Support\Decomposers;

use Unusualify\Modularity\Support\Decomposers\ValidatorParser;
use Unusualify\Modularity\Tests\TestCase;

class ValidatorParserTest extends TestCase
{
    /** @test */
    public function it_can_parse_rules_to_array()
    {
        $rules = 'required=true&min=3&max=10';
        $parser = new ValidatorParser($rules);
        
        $expected = [
            'required' => 'true',
            'min' => '3',
            'max' => '10'
        ];
        
        $this->assertEquals($expected, $parser->toArray());
    }

    /** @test */
    public function it_handles_null_rules()
    {
        $parser = new ValidatorParser(null);
        $this->assertEquals([], $parser->toArray());
    }

    /** @test */
    public function it_handles_string_without_values()
    {
        $rules = 'required&unique';
        $parser = new ValidatorParser($rules);
        
        $expected = [
            'required' => '',
            'unique' => ''
        ];
        
        $this->assertEquals($expected, $parser->toArray());
    }

    /** @test */
    public function it_removes_spaces_during_parsing()
    {
        $rules = 'required = true & min = 3';
        $parser = new ValidatorParser($rules);
        
        $expected = [
            'required' => 'true',
            'min' => '3'
        ];
        
        $this->assertEquals($expected, $parser->toArray());
    }

    /** @test */
    public function it_can_convert_to_replacement_string()
    {
        $rules = 'required=true';
        $parser = new ValidatorParser($rules);
        
        $replacement = $parser->toReplacement();
        
        // array_export is likely a helper that formats the array as PHP code
        // We expect it to contain 'required' => 'true'
        $this->assertStringContainsString("'required' => 'true'", $replacement);
    }
}
