<?php

namespace Unusualify\Modularous\Tests\Services\MediaLibrary;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Unusualify\Modularous\Services\MediaLibrary\Local;
use Unusualify\Modularous\Tests\TestCase;

class LocalTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set(modularousBaseKey() . '.media_library.disk', 'public');
        Storage::fake('public');

        $this->service = new Local;
    }

    /** @test */
    public function it_can_get_url()
    {
        $url = $this->service->getUrl('test-image.jpg');

        $this->assertIsString($url);
        $this->assertStringContainsString('test-image.jpg', $url);
    }

    /** @test */
    public function it_can_get_url_with_crop()
    {
        $url = $this->service->getUrlWithCrop('test-image.jpg', [
            'crop_x' => 10,
            'crop_y' => 20,
            'crop_w' => 100,
            'crop_h' => 150,
        ]);

        $this->assertIsString($url);
        $this->assertStringContainsString('test-image.jpg', $url);
    }

    /** @test */
    public function it_can_get_url_with_focal_crop()
    {
        $url = $this->service->getUrlWithFocalCrop('test-image.jpg', [
            'crop_x' => 10,
            'crop_y' => 20,
            'crop_w' => 100,
            'crop_h' => 150,
        ], 400, 300);

        $this->assertIsString($url);
        $this->assertStringContainsString('test-image.jpg', $url);
    }

    /** @test */
    public function it_can_get_lqip_url()
    {
        $url = $this->service->getLQIPUrl('test-image.jpg');

        $this->assertIsString($url);
        $this->assertStringContainsString('test-image.jpg', $url);
    }

    /** @test */
    public function it_can_get_social_url()
    {
        $url = $this->service->getSocialUrl('test-image.jpg');

        $this->assertIsString($url);
        $this->assertStringContainsString('test-image.jpg', $url);
    }

    /** @test */
    public function it_can_get_cms_url()
    {
        $url = $this->service->getCmsUrl('test-image.jpg');

        $this->assertIsString($url);
        $this->assertStringContainsString('test-image.jpg', $url);
    }

    /** @test */
    public function it_can_get_raw_url()
    {
        $url = $this->service->getRawUrl('test-image.jpg');

        $this->assertIsString($url);
        $this->assertStringContainsString('test-image.jpg', $url);
    }

    /** @test */
    public function it_returns_null_for_dimensions()
    {
        $dimensions = $this->service->getDimensions('test-image.jpg');

        $this->assertNull($dimensions);
    }
}
