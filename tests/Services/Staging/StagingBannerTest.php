<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Staging;

use Illuminate\Support\Facades\Blade;
use Unusualify\Modularous\Tests\TestCase;

class StagingBannerTest extends TestCase
{
    private function renderBanner(): string
    {
        return Blade::render(file_get_contents(__DIR__ . '/../../../resources/views/partials/staging-banner.blade.php'));
    }

    public function test_banner_is_hidden_when_staging_is_disabled(): void
    {
        config()->set('modularous.staging.enabled', false);

        $this->assertSame('', trim($this->renderBanner()));
    }

    public function test_banner_shows_the_label_when_staging_is_enabled(): void
    {
        config()->set('modularous.staging.enabled', true);
        config()->set('modularous.staging.label', 'PR #73');

        $html = $this->renderBanner();

        $this->assertStringContainsString('data-staging-banner', $html);
        $this->assertStringContainsString('Staging · PR #73: changes are locked', $html);
    }
}
