<?php

namespace Unusualify\Modularity\Tests\Services\MediaLibrary;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Unusualify\Modularity\Services\MediaLibrary\Glide;
use Unusualify\Modularity\Tests\TestCase;

class GlideTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock config
        Config::set(modularityBaseKey() . '.glide.base_url', 'http://localhost');
        Config::set(modularityBaseKey() . '.glide.base_path', '/img');
        Config::set(modularityBaseKey() . '.glide.source', storage_path('app/public'));
        Config::set(modularityBaseKey() . '.glide.cache', storage_path('app/glide'));
        Config::set(modularityBaseKey() . '.glide.cache_path_prefix', '.cache');
        Config::set(modularityBaseKey() . '.glide.driver', 'gd');
        Config::set(modularityBaseKey() . '.glide.use_signed_urls', false);
        Config::set(modularityBaseKey() . '.glide.default_params', ['fm' => 'jpg', 'q' => 80]);
        Config::set(modularityBaseKey() . '.glide.lqip_default_params', ['w' => 50, 'blur' => 10]);
        Config::set(modularityBaseKey() . '.glide.social_default_params', ['w' => 1200, 'h' => 630]);
        Config::set(modularityBaseKey() . '.glide.cms_default_params', ['w' => 800]);
        Config::set(modularityBaseKey() . '.glide.presets', ['thumbnail' => ['w' => 100, 'h' => 100]]);
        Config::set(modularityBaseKey() . '.glide.original_media_for_extensions', ['.svg', '.gif']);
        Config::set(modularityBaseKey() . '.glide.add_params_to_svgs', false);
        Config::set(modularityBaseKey() . '.media_library.disk', 'public');

        Storage::fake('public');

        $this->service = new Glide(
            app('config'),
            app(Application::class),
            Request::create('http://localhost')
        );
    }

    /** @test */
    public function it_can_get_url()
    {
        $url = $this->service->getUrl('test-image.jpg');

        $this->assertIsString($url);
        $this->assertStringContainsString('test-image.jpg', $url);
    }

    /** @test */
    public function it_merges_default_params_with_custom_params()
    {
        $url = $this->service->getUrl('test-image.jpg', ['w' => 300]);

        $this->assertIsString($url);
        $this->assertStringContainsString('w=300', $url);
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
        $this->assertStringContainsString('crop=', $url);
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

        $this->assertIsString($url);
        $this->assertStringContainsString('fit=crop', $url);
    }

    /** @test */
    public function it_can_get_lqip_url()
    {
        $url = $this->service->getLQIPUrl('test-image.jpg');

        $this->assertIsString($url);
        $this->assertStringContainsString('w=50', $url);
        $this->assertStringContainsString('blur=10', $url);
    }

    /** @test */
    public function it_can_get_social_url()
    {
        $url = $this->service->getSocialUrl('test-image.jpg');

        $this->assertIsString($url);
        $this->assertStringContainsString('w=1200', $url);
        $this->assertStringContainsString('h=630', $url);
    }

    /** @test */
    public function it_can_get_cms_url()
    {
        $url = $this->service->getCmsUrl('test-image.jpg');

        $this->assertIsString($url);
        $this->assertStringContainsString('w=800', $url);
    }

    /** @test */
    public function it_can_get_preset_url()
    {
        $url = $this->service->getPresetUrl('test-image.jpg', 'thumbnail');

        $this->assertIsString($url);
        $this->assertStringContainsString('p=thumbnail', $url);
    }

    /** @test */
    public function it_can_get_raw_url()
    {
        $url = $this->service->getRawUrl('test-image.jpg');

        $this->assertIsString($url);
    }

    /** @test */
    public function it_returns_original_url_for_svg_files()
    {
        Storage::disk('public')->put('test.svg', '<svg></svg>');

        $url = $this->service->getUrl('test.svg');

        $this->assertIsString($url);
        $this->assertStringContainsString('test.svg', $url);
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

        $this->assertIsString($url);
        $this->assertStringContainsString('crop', $url);
    }

    /** @test */
    public function it_returns_zero_dimensions_on_error()
    {
        $dimensions = $this->service->getDimensions('non-existent.jpg');

        $this->assertIsArray($dimensions);
        $this->assertEquals(0, $dimensions['width']);
        $this->assertEquals(0, $dimensions['height']);
    }
}
