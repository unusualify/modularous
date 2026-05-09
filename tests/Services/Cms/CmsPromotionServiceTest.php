<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Services\CmsPromotionService;
use Modules\Cms\Services\DefaultCmsPromotionScopeApplier;
use Unusualify\Modularous\Services\Security\SecurityService;
use Unusualify\Modularous\Tests\TestCase;

class CmsPromotionServiceTest extends TestCase
{
    public function test_dry_run_diff_marks_missing_tables(): void
    {
        $security = $this->createMock(SecurityService::class);
        $security->method('canPromote')->willReturn(true);

        $service = new CmsPromotionService($security, new DefaultCmsPromotionScopeApplier);

        $report = $service->promote([
            'dry_run' => true,
            'scope' => [
                'settings' => true,
                'content' => true,
                'seo' => true,
                'redirects' => true,
                'layouts' => true,
            ],
        ], null);

        $this->assertTrue($report['ok']);
        $this->assertArrayHasKey('meta', $report['diff']);
        $this->assertFalse($report['diff']['settings_changes']['available']);
        $this->assertFalse($report['diff']['content_changes']['pages']['available']);
    }

    public function test_dry_run_diff_counts_rows_when_minimal_schema_present(): void
    {
        $security = $this->createMock(SecurityService::class);
        $security->method('canPromote')->willReturn(true);

        $this->createMinimalCmsSchema();

        $t = modularousConfig('tables.cms_site_settings', 'um_cms_site_settings');
        DB::table($t)->insert([
            'group_key' => 'seo',
            'key' => 'x',
            'locale' => 'en',
            'value' => '1',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = new CmsPromotionService($security, new DefaultCmsPromotionScopeApplier);

        $report = $service->promote([
            'dry_run' => true,
            'scope' => ['settings' => true],
        ], null);

        $this->assertTrue($report['ok']);
        $this->assertTrue($report['diff']['settings_changes']['available']);
        $this->assertSame(1, $report['diff']['settings_changes']['total_rows']);
        $this->assertSame(1, $report['diff']['settings_changes']['active_rows']);
        $this->assertSame(['seo' => 1], $report['diff']['settings_changes']['rows_by_group']);
    }

    public function test_promote_denied_when_user_cannot_approve(): void
    {
        $security = $this->createMock(SecurityService::class);
        $security->method('canPromote')->willReturn(false);

        $service = new CmsPromotionService($security, new DefaultCmsPromotionScopeApplier);

        $report = $service->promote(['dry_run' => true, 'scope' => []], null);

        $this->assertFalse($report['ok']);
        $this->assertSame('approval_check', $report['stage']);
    }

    /**
     * Columns aligned with {@see SiteSetting} for aggregate queries only.
     */
    protected function createMinimalCmsSchema(): void
    {
        $t = modularousConfig('tables.cms_site_settings', 'um_cms_site_settings');
        Schema::dropIfExists($t);
        Schema::create($t, function (Blueprint $table): void {
            $table->id();
            $table->string('group_key');
            $table->string('key');
            $table->string('locale');
            $table->text('value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
}
