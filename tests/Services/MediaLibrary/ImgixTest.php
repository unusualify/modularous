<?php

namespace Unusualify\Modularity\Tests\Services\MediaLibrary;

use Illuminate\Support\Facades\Config;
use Unusualify\Modularity\Services\MediaLibrary\Imgix;
use Unusualify\Modularity\Tests\TestCase;
use Imgix\UrlBuilder;

class ImgixTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set(modularityBaseKey() . '.imgix.source_host', 'test.imgix.net');
        Config::set(modularityBaseKey() . '.imgix.use_https', true);
        Config::set(modularityBaseKey() . '.imgix.use_signed_urls', false);
        Config::set(modularityBaseKey() . '.imgix.sign_key', null);
        Config::set(modularityBaseKey() . '.imgix.default_params', ['auto' => 'compress', 'q' => 80]);
        Config::set(modularityBaseKey() . '.imgix.lqip_default_params', ['w' => 50, 'blur' => 10]);
        Config::set(modularityBaseKey() . '.imgix.social_default_params', ['w' => 1200, 'h' => 630]);
        Config::set(modularityBaseKey() . '.imgix.cms_default_params', ['w' => 800]);
        Config::set(modularityBaseKey() . '.imgix.add_params_to_svgs', false);

        $this->service = new Imgix(app('config'));
    }

    /** @test */
    public function it_can_get_url()
    {
        $url = $this->service->getUrl('test-image.jpg');

        $this->assertIsString($url);
        $this->assertStringContainsString('test.imgix.net', $url);
        $this->assertStringContainsString('test-image.jpg', $url);
    }

    /** @test */
    public function it_merges_default_params()
    {
        $url = $this->service->getUrl('test-image.jpg', ['w' => 300]);

        $this->assertStringContainsString('w=300', $url);
        $this->assertStringContainsString('auto=compress', $url);
    }

    /** @test */
    public function it_skips_params_for_svg_files()
    {
        $url = $this->service->getUrl('test.svg');

        $this->assertStringContainsString('test.svg', $url);
        $this->assertStringNotContainsString('auto=', $url);
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

        $this->assertStringContainsString('rect=', $url);
        $this->assertStringContainsString('10', $url);
        $this->assertStringContainsString('20', $url);
        $this->assertStringContainsString('100', $url);
        $this->assertStringContainsString('150', $url);
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

        $this->assertStringContainsString('fp-x=', $url);
        $this->assertStringContainsString('fp-y=', $url);
        $this->assertStringContainsString('fp-z=', $url);
        $this->assertStringContainsString('crop=focalpoint', $url);
        $this->assertStringContainsString('fit=crop', $url);
    }

    /** @test */
    public function it_can_get_lqip_url()
    {
        $url = $this->service->getLQIPUrl('test-image.jpg');

        $this->assertStringContainsString('w=50', $url);
        $this->assertStringContainsString('blur=10', $url);
    }

    /** @test */
    public function it_can_get_social_url()
    {
        $url = $this->service->getSocialUrl('test-image.jpg');

        $this->assertStringContainsString('w=1200', $url);
        $this->assertStringContainsString('h=630', $url);
    }

    /** @test */
    public function it_can_get_cms_url()
    {
        $url = $this->service->getCmsUrl('test-image.jpg');

        $this->assertStringContainsString('w=800', $url);
    }

    /** @test */
    public function it_can_get_raw_url()
    {
        $url = $this->service->getRawUrl('test-image.jpg');

        $this->assertStringContainsString('test.imgix.net', $url);
        $this->assertStringContainsString('test-image.jpg', $url);
    }

    /** @test */
    public function it_handles_crop_params_in_lqip_url()
    {
        $url = $this->service->getLQIPUrl('test-image.jpg', [
            'crop_x' => 10,
            'crop_y' => 20,
            'crop_w' => 100,
            'crop_h' => 150,
        ]);

        $this->assertStringContainsString('rect=', $url);
    }

    /** @test */
    public function it_calculates_focal_point_correctly()
    {
        $url = $this->service->getUrlWithFocalCrop('test-image.jpg', [
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 400,
            'crop_h' => 300,
        ], 800, 600);

        // Center is at 200,150 -> 0.25, 0.25
        $this->assertStringContainsString('fp-x=0.25', $url);
        $this->assertStringContainsString('fp-y=0.25', $url);
    }

    /** @test */
    public function it_returns_empty_crop_for_empty_params()
    {
        $url = $this->service->getUrlWithCrop('test-image.jpg', []);

        $this->assertStringNotContainsString('rect=', $url);
    }

    /** @test */
    public function it_returns_empty_focal_crop_for_empty_params()
    {
        $url = $this->service->getUrlWithFocalCrop('test-image.jpg', [], 800, 600);

        $this->assertStringNotContainsString('fp-x=', $url);
    }
}
