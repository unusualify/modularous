<?php

namespace Unusualify\Modularity\Tests\Services\MediaLibrary;

use Unusualify\Modularity\Services\MediaLibrary\TwicPicsParamsProcessor;
use Unusualify\Modularity\Tests\TestCase;

class TwicPicsParamsProcessorTest extends TestCase
{
    protected $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->processor = new TwicPicsParamsProcessor();
    }

    /** @test */
    public function it_converts_width_param()
    {
        $result = $this->processor->process(['w' => 300]);

        $this->assertArrayHasKey('resize', $result);
        $this->assertEquals('300x-', $result['resize']);
    }

    /** @test */
    public function it_converts_height_param()
    {
        $result = $this->processor->process(['h' => 200]);

        $this->assertArrayHasKey('resize', $result);
        $this->assertEquals('-x200', $result['resize']);
    }

    /** @test */
    public function it_converts_width_and_height()
    {
        $result = $this->processor->process(['w' => 300, 'h' => 200]);

        $this->assertArrayHasKey('resize', $result);
        $this->assertEquals('300x200', $result['resize']);
    }

    /** @test */
    public function it_converts_format_param()
    {
        $result = $this->processor->process(['fm' => 'webp']);

        $this->assertArrayHasKey('output', $result);
        $this->assertEquals('webp', $result['output']);
    }

    /** @test */
    public function it_converts_quality_param()
    {
        $result = $this->processor->process(['q' => 90]);

        $this->assertArrayHasKey('quality', $result);
        $this->assertEquals(90, $result['quality']);
    }

    /** @test */
    public function it_converts_fit_crop_to_crop_param()
    {
        $result = $this->processor->process(['fit' => 'crop', 'w' => 300, 'h' => 200]);

        $this->assertArrayHasKey('crop', $result);
        $this->assertEquals('300x200', $result['crop']);
        $this->assertArrayNotHasKey('resize', $result);
        $this->assertArrayNotHasKey('fit', $result);
    }

    /** @test */
    public function it_ignores_non_crop_fit_values()
    {
        $result = $this->processor->process(['fit' => 'max']);

        // Non-crop fit values are preserved in params
        $this->assertArrayHasKey('fit', $result);
    }

    /** @test */
    public function it_preserves_unknown_params()
    {
        $result = $this->processor->process(['custom' => 'value']);

        $this->assertArrayHasKey('custom', $result);
        $this->assertEquals('value', $result['custom']);
    }

    /** @test */
    public function it_processes_multiple_params()
    {
        $result = $this->processor->process([
            'w' => 300,
            'h' => 200,
            'fm' => 'webp',
            'q' => 85,
            'custom' => 'test'
        ]);

        $this->assertArrayHasKey('resize', $result);
        $this->assertArrayHasKey('output', $result);
        $this->assertArrayHasKey('quality', $result);
        $this->assertArrayHasKey('custom', $result);
        $this->assertEquals('300x200', $result['resize']);
        $this->assertEquals('webp', $result['output']);
        $this->assertEquals(85, $result['quality']);
        $this->assertEquals('test', $result['custom']);
    }

    /** @test */
    public function it_does_not_override_existing_crop_param()
    {
        $result = $this->processor->process([
            'fit' => 'crop',
            'crop' => '400x300',
            'w' => 300,
            'h' => 200
        ]);

        // Should keep the existing crop param
        $this->assertArrayHasKey('crop', $result);
        $this->assertEquals('400x300', $result['crop']);
    }
}
