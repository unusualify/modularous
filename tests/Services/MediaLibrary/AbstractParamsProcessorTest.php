<?php

namespace Unusualify\Modularity\Tests\Services\MediaLibrary;

use Unusualify\Modularity\Tests\TestCase;

class AbstractParamsProcessorTest extends TestCase
{
    protected $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->processor = new ConcreteParamsProcessor();
    }

    /** @test */
    public function it_extracts_compatible_params()
    {
        $result = $this->processor->process(['w' => 300, 'h' => 200]);

        $this->assertEquals(300, $this->processor->getWidth());
        $this->assertEquals(200, $this->processor->getHeight());
    }

    /** @test */
    public function it_extracts_format_param()
    {
        $result = $this->processor->process(['fm' => 'webp']);

        $this->assertEquals('webp', $this->processor->getFormat());
    }

    /** @test */
    public function it_extracts_quality_param()
    {
        $result = $this->processor->process(['q' => 85]);

        $this->assertEquals(85, $this->processor->getQuality());
    }

    /** @test */
    public function it_preserves_unknown_params()
    {
        $result = $this->processor->process(['custom' => 'value', 'w' => 300]);

        $this->assertArrayHasKey('custom', $result);
        $this->assertEquals('value', $result['custom']);
        $this->assertArrayNotHasKey('w', $result);
    }

    /** @test */
    public function it_calls_custom_param_handlers()
    {
        $result = $this->processor->process(['special' => 'test']);

        $this->assertTrue($this->processor->wasSpecialHandled());
    }
}

/**
 * Concrete implementation for testing
 */
class ConcreteParamsProcessor extends \Unusualify\Modularity\Services\MediaLibrary\AbstractParamsProcessor
{
    protected $specialHandled = false;

    public function finalizeParams()
    {
        return $this->params;
    }

    protected function handleParamspecial($key, $value)
    {
        $this->specialHandled = true;
        unset($this->params[$key]);
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function getQuality()
    {
        return $this->quality;
    }

    public function wasSpecialHandled()
    {
        return $this->specialHandled;
    }
}
