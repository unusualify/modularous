<?php

namespace Unusualify\Modularity\Tests\Services\MediaLibrary;

use Illuminate\Support\Facades\Config;
use Unusualify\Modularity\Services\MediaLibrary\TwicPics;
use Unusualify\Modularity\Services\MediaLibrary\TwicPicsParamsProcessor;
use Unusualify\Modularity\Tests\TestCase;

class TwicPicsTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set(modularityBaseKey() . '.twicpics.domain', 'test.twic.pics');
        Config::set(modularityBaseKey() . '.twicpics.path', 'images');
        Config::set(modularityBaseKey() . '.twicpics.api_version', 'v1');
        Config::set(modularityBaseKey() . '.twicpics.default_params', ['quality' => 80]);
        Config::set(modularityBaseKey() . '.twicpics.lqip_default_params', ['resize' => '50x', 'output' => 'preview']);
        Config::set(modularityBaseKey() . '.twicpics.social_default_params', ['cover' => '1200x630']);
        Config::set(modularityBaseKey() . '.twicpics.cms_default_params', ['resize' => '800x']);

        $this->service = new TwicPics(new TwicPicsParamsProcessor());
    }

    /** @test */
    public function it_can_get_url()
    {
        $url = $this->service->getUrl('test-image.jpg');

        $this->assertIsString($url);
        $this->assertStringContainsString('test.twic.pics', $url);
        $this->assertStringContainsString('test-image.jpg', $url);
        $this->assertStringContainsString('twic=', $url);
    }

    /** @test */
    public function it_includes_path_in_url()
    {
        $url = $this->service->getUrl('test-image.jpg');

        $this->assertStringContainsString('images/', $url);
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

        $this->assertStringContainsString('crop=100x150', $url);
    }

    /** @test */
    public function it_includes_crop_position_when_provided()
    {
        $url = $this->service->getUrlWithCrop('test-image.jpg', [
            'crop_x' => 10,
            'crop_y' => 20,
            'crop_w' => 100,
            'crop_h' => 150,
        ]);

        $this->assertStringContainsString('@10x20', $url);
    }

    /** @test */
    public function it_can_get_url_with_focal_crop()
    {
        $url = $this->service->getUrlWithFocalCrop('test-image.jpg', [
            'crop_x' => 100,
            'crop_y' => 100,
            'crop_w' => 200,
            'crop_h' => 200,
        ], 800, 600);

        $this->assertStringContainsString('focus=', $url);
    }

    /** @test */
    public function it_calculates_focal_point_center()
    {
        $url = $this->service->getUrlWithFocalCrop('test-image.jpg', [
            'crop_x' => 100,
            'crop_y' => 100,
            'crop_w' => 200,
            'crop_h' => 200,
        ], 800, 600);

        // Center is at 200,200
        $this->assertStringContainsString('focus=200x200', $url);
    }

    /** @test */
    public function it_can_get_lqip_url()
    {
        $url = $this->service->getLQIPUrl('test-image.jpg');

        $this->assertStringContainsString('resize=50x', $url);
        $this->assertStringContainsString('output=preview', $url);
    }

    /** @test */
    public function it_can_get_social_url()
    {
        $url = $this->service->getSocialUrl('test-image.jpg');

        $this->assertStringContainsString('cover=1200x630', $url);
    }

    /** @test */
    public function it_can_get_cms_url()
    {
        $url = $this->service->getCmsUrl('test-image.jpg');

        $this->assertStringContainsString('resize=800x', $url);
    }

    /** @test */
    public function it_can_get_raw_url()
    {
        $url = $this->service->getRawUrl('test-image.jpg');

        $this->assertStringContainsString('https://test.twic.pics/images/test-image.jpg', $url);
        $this->assertStringNotContainsString('twic=', $url);
    }

    /** @test */
    public function it_returns_null_for_dimensions()
    {
        $dimensions = $this->service->getDimensions('test-image.jpg');

        $this->assertNull($dimensions);
    }

    /** @test */
    public function it_handles_empty_crop_params()
    {
        $url = $this->service->getUrlWithCrop('test-image.jpg', []);

        $this->assertStringNotContainsString('crop=', $url);
    }

    /** @test */
    public function it_handles_partial_crop_params()
    {
        $url = $this->service->getUrlWithCrop('test-image.jpg', [
            'crop_w' => 100,
        ]);

        $this->assertStringNotContainsString('crop=', $url);
    }

    /** @test */
    public function it_returns_empty_focal_crop_for_empty_params()
    {
        $url = $this->service->getUrlWithFocalCrop('test-image.jpg', [], 800, 600);

        $this->assertStringNotContainsString('focus=', $url);
    }

    /** @test */
    public function it_handles_crop_params_in_social_url()
    {
        $url = $this->service->getSocialUrl('test-image.jpg', [
            'crop_x' => 10,
            'crop_y' => 20,
            'crop_w' => 100,
            'crop_h' => 150,
        ]);

        $this->assertStringContainsString('crop=', $url);
    }
}
