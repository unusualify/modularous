<?php

namespace Unusualify\Modularous\Tests\Support;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Foundation\Vite;
use Illuminate\Foundation\ViteManifestNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;
use Unusualify\Modularous\Support\ModularousVite;
use Unusualify\Modularous\Tests\TestCase;

class ModularousViteTest extends TestCase
{
    protected ModularousVite $vite;

    protected string $buildPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vite = new ModularousVite;
        $this->buildPath = public_path('vendor/modularous');

        $this->ensureModularousBuildExists();
    }

    protected function ensureModularousBuildExists(): void
    {
        $distPath = realpath(__DIR__ . '/../../vue/dist/modularous');

        if ($distPath && is_dir($distPath)) {
            if (! is_dir($this->buildPath)) {
                File::makeDirectory($this->buildPath, 0755, true);
            }
            File::copyDirectory($distPath, $this->buildPath);
        } else {
            $this->createFixtureBuild();
        }
    }

    protected function createFixtureBuild(): void
    {
        File::makeDirectory($this->buildPath, 0755, true);
        File::makeDirectory($this->buildPath . '/entries', 0755, true);
        File::makeDirectory($this->buildPath . '/css', 0755, true);

        $manifest = json_decode(
            File::get(__DIR__ . '/../fixtures/modularous-manifest.json'),
            true
        );
        File::put(
            $this->buildPath . '/modularous-manifest.json',
            json_encode($manifest, JSON_PRETTY_PRINT)
        );

        File::put($this->buildPath . '/entries/core-inertia.js', '// fixture');
        File::put($this->buildPath . '/entries/core-auth.js', '// fixture');
        File::put($this->buildPath . '/css/core-auth.css', '/* fixture */');
    }

    protected function tearDown(): void
    {
        // Clear cached manifests between tests
        $reflection = new \ReflectionClass(Vite::class);
        $prop = $reflection->getProperty('manifests');
        $prop->setAccessible(true);
        $prop->setValue(null, []);

        parent::tearDown();
    }

    public function test_is_running_hot_returns_boolean(): void
    {
        $result = $this->vite->isRunningHot();

        $this->assertIsBool($result);
    }

    public function test_uses_modularous_manifest_filename(): void
    {
        $reflection = new \ReflectionClass($this->vite);
        $prop = $reflection->getProperty('manifestFilename');
        $prop->setAccessible(true);

        $this->assertEquals('modularous-manifest.json', $prop->getValue($this->vite));
    }

    public function test_uses_modularous_build_directory(): void
    {
        $reflection = new \ReflectionClass($this->vite);
        $prop = $reflection->getProperty('buildDirectory');
        $prop->setAccessible(true);

        $this->assertEquals('vendor/modularous', $prop->getValue($this->vite));
    }

    public function test_extends_laravel_vite(): void
    {
        $this->assertInstanceOf(Vite::class, $this->vite);
    }

    public function test_invoke_returns_html_string_in_production(): void
    {
        $entrypoint = $this->getFirstManifestEntrypoint();

        $result = ($this->vite)($entrypoint);

        $this->assertInstanceOf(HtmlString::class, $result);
        $this->assertNotEmpty($result->toHtml());
    }

    public function test_invoke_accepts_string_entrypoint(): void
    {
        $entrypoint = $this->getFirstManifestEntrypoint();

        $result = ($this->vite)($entrypoint);

        $this->assertInstanceOf(HtmlString::class, $result);
    }

    public function test_invoke_accepts_array_entrypoints(): void
    {
        $entrypoints = $this->getManifestEntrypoints(2);

        $result = ($this->vite)($entrypoints);

        $this->assertInstanceOf(HtmlString::class, $result);
    }

    public function test_invoke_uses_custom_build_directory_when_passed(): void
    {
        $customPath = 'vendor/modularous-custom';
        $customFullPath = public_path($customPath);
        File::makeDirectory($customFullPath . '/entries', 0755, true);
        $manifestPath = $customFullPath . '/modularous-manifest.json';
        File::put($manifestPath, json_encode([
            'src/js/test.js' => [
                'file' => 'entries/test.js',
                'name' => 'test',
                'src' => 'src/js/test.js',
                'isEntry' => true,
                'imports' => [],
            ],
        ], JSON_PRETTY_PRINT));
        File::put($customFullPath . '/entries/test.js', '// test');

        $result = ($this->vite)('src/js/test.js', $customPath);

        $this->assertInstanceOf(HtmlString::class, $result);
        $this->assertStringContainsString('test.js', $result->toHtml());

        File::deleteDirectory($customFullPath);
    }

    public function test_invoke_throws_when_manifest_not_found(): void
    {
        $this->expectException(ViteManifestNotFoundException::class);
        $this->expectExceptionMessageMatches('/Vite manifest not found at:/');

        ($this->vite)('nonexistent-entry', 'vendor/nonexistent-build');
    }

    public function test_invoke_throws_when_entrypoint_not_in_manifest(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unable to locate file in Vite manifest');

        ($this->vite)('src/js/nonexistent-entry.js');
    }

    public function test_asset_returns_url_for_manifest_entry(): void
    {
        $entrypoint = $this->getFirstManifestEntrypoint();

        $url = $this->vite->asset($entrypoint);

        $this->assertIsString($url);
        $this->assertNotEmpty($url);
    }

    public function test_asset_uses_custom_build_directory(): void
    {
        $entrypoint = $this->getFirstManifestEntrypoint();

        $url = $this->vite->asset($entrypoint, 'vendor/modularous');

        $this->assertIsString($url);
        $this->assertStringContainsString('vendor/modularous', $url);
    }

    public function test_content_returns_file_content(): void
    {
        $entrypoint = $this->getFirstManifestEntrypoint();

        $content = $this->vite->content($entrypoint);

        $this->assertIsString($content);
        $this->assertNotEmpty($content);
    }

    public function test_manifest_hash_returns_string_when_manifest_exists(): void
    {
        $hash = $this->vite->manifestHash();

        $this->assertIsString($hash);
        $this->assertEquals(32, mb_strlen($hash));
    }

    public function test_manifest_hash_returns_null_for_nonexistent_manifest(): void
    {
        $hash = $this->vite->manifestHash('vendor/nonexistent-build');

        $this->assertNull($hash);
    }

    public function test_use_build_directory_changes_build_path(): void
    {
        $this->vite->useBuildDirectory('custom/build');

        $reflection = new \ReflectionClass($this->vite);
        $prop = $reflection->getProperty('buildDirectory');
        $prop->setAccessible(true);

        $this->assertEquals('custom/build', $prop->getValue($this->vite));
    }

    public function test_use_manifest_filename_changes_manifest_name(): void
    {
        $this->vite->useManifestFilename('custom-manifest.json');

        $reflection = new \ReflectionClass($this->vite);
        $prop = $reflection->getProperty('manifestFilename');
        $prop->setAccessible(true);

        $this->assertEquals('custom-manifest.json', $prop->getValue($this->vite));
    }

    public function test_implements_htmlable(): void
    {
        $this->assertInstanceOf(Htmlable::class, $this->vite);
    }

    public function test_invoke_in_hot_mode_prepends_svg_spritemap_client(): void
    {
        $hotFile = $this->buildPath . '/hot';
        $viteUrl = 'http://localhost:5173';
        File::put($hotFile, $viteUrl);

        $vite = new ModularousVite;
        $vite->useHotFile($hotFile);

        $entrypoint = $this->getFirstManifestEntrypoint();
        $result = $vite($entrypoint);

        $html = $result->toHtml();

        // ModularousVite prepends @vite-plugin-svg-spritemap/client and @vite/client (Laravel only prepends @vite/client)
        $this->assertStringContainsString('@vite-plugin-svg-spritemap/client', $html);
        $this->assertStringContainsString('@vite/client', $html);

        File::delete($hotFile);
    }

    protected function getFirstManifestEntrypoint(): string
    {
        $manifestPath = $this->buildPath . '/modularous-manifest.json';
        if (! is_file($manifestPath)) {
            $this->markTestSkipped('Modularous manifest not found');
        }

        $manifest = json_decode(File::get($manifestPath), true);
        foreach ($manifest as $key => $chunk) {
            if (isset($chunk['isEntry']) && $chunk['isEntry']) {
                return $key;
            }
        }

        return array_key_first($manifest);
    }

    protected function getManifestEntrypoints(int $limit = 2): array
    {
        $manifestPath = $this->buildPath . '/modularous-manifest.json';
        if (! is_file($manifestPath)) {
            $this->markTestSkipped('Modularous manifest not found');
        }

        $manifest = json_decode(File::get($manifestPath), true);
        $entrypoints = [];
        foreach ($manifest as $key => $chunk) {
            if ((isset($chunk['isEntry']) && $chunk['isEntry']) || empty($entrypoints)) {
                $entrypoints[] = $key;
                if (count($entrypoints) >= $limit) {
                    break;
                }
            }
        }

        return $entrypoints;
    }
}
