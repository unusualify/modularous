<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Entities\Redirect;
use Modules\Cms\Http\Controllers\RedirectController;
use Modules\Cms\Providers\CmsServiceProvider;
use Unusualify\Modularous\Contracts\CanBulkSheet;
use Unusualify\Modularous\Services\BulkCsv\BulkImportService;
use Unusualify\Modularous\Tests\ModelTestCase;

class RedirectBulkImportServiceTest extends ModelTestCase
{
    private const TOOL_KEY = 'cms.redirect';

    private function redirectBulkSheet(): CanBulkSheet
    {
        return $this->app->make(RedirectController::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('modularous.cms_features.enabled', true);
        $this->app['config']->set('modularous.cms_features.register_contracts', false);
        $this->app['config']->set('modularous.tables.cms_redirects', 'um_cms_redirects');
        $this->app['config']->set('modularous.tables.cms_url_routes', 'um_cms_url_routes');

        $this->createRedirectsTable();
        $this->createUrlRoutesTable();

        $this->app->register(CmsServiceProvider::class);
    }

    public function test_dry_run_does_not_persist(): void
    {
        $csv = "locale,from_path,to_path,status_code,is_active\nen,/a,/b,301,1\n";
        $service = $this->app->make(BulkImportService::class);
        $result = $service->import($csv, true, $this->redirectBulkSheet(), self::TOOL_KEY);

        $this->assertTrue($result['ok']);
        $this->assertSame(0, Redirect::query()->count());
    }

    public function test_dry_run_accepts_semicolon_delimiter(): void
    {
        $csv = "locale;from_path;to_path;status_code;is_active\r\nen;/back;/pages/test;301;1\r\n";
        $service = $this->app->make(BulkImportService::class);
        $result = $service->import($csv, true, $this->redirectBulkSheet(), self::TOOL_KEY);

        $this->assertTrue($result['ok'], (string) ($result['message'] ?? ''));
        $this->assertSame(0, Redirect::query()->count());
    }

    public function test_commit_creates_rows(): void
    {
        $csv = "locale,from_path,to_path,status_code,is_active\nen,/a,/b,301,1\n";
        $service = $this->app->make(BulkImportService::class);
        $result = $service->import($csv, false, $this->redirectBulkSheet(), self::TOOL_KEY);

        $this->assertTrue($result['ok']);
        $this->assertSame(1, Redirect::query()->count());
        $this->assertSame('/b', Redirect::query()->first()->to_path);
    }

    public function test_commit_updates_existing_by_locale_and_from_path(): void
    {
        Redirect::query()->create([
            'from_path' => '/a',
            'to_path' => '/old',
            'locale' => 'en',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $csv = "locale,from_path,to_path\nen,/a,/new\n";
        $service = $this->app->make(BulkImportService::class);
        $result = $service->import($csv, false, $this->redirectBulkSheet(), self::TOOL_KEY);

        $this->assertTrue($result['ok']);
        $this->assertSame(1, Redirect::query()->count());
        $this->assertSame('/new', Redirect::query()->first()->to_path);
    }

    public function test_rejects_loop_in_batch(): void
    {
        $csv = "locale,from_path,to_path\nen,/a,/b\nen,/b,/a\n";
        $service = $this->app->make(BulkImportService::class);
        $result = $service->import($csv, true, $this->redirectBulkSheet(), self::TOOL_KEY);

        $this->assertFalse($result['ok']);
    }

    public function test_invalid_first_row_does_not_block_second_with_same_from(): void
    {
        $csv = "locale,from_path,to_path\nen,/a,/a\nen,/a,/b\n";
        $service = $this->app->make(BulkImportService::class);
        $result = $service->import($csv, true, $this->redirectBulkSheet(), self::TOOL_KEY);

        $this->assertFalse($result['ok']);
        $rows = $result['rows'];
        $this->assertFalse($rows[0]['valid']);
        $this->assertTrue($rows[1]['valid']);
    }

    public function test_empty_csv_fails(): void
    {
        $service = $this->app->make(BulkImportService::class);
        $result = $service->import('', true, $this->redirectBulkSheet(), self::TOOL_KEY);

        $this->assertFalse($result['ok']);
    }

    protected function createRedirectsTable(): void
    {
        $t = 'um_cms_redirects';
        Schema::dropIfExists($t);
        Schema::create($t, function (Blueprint $table): void {
            $table->id();
            $table->string('from_path');
            $table->string('to_path');
            $table->string('locale', 12)->index();
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['from_path', 'locale']);
        });
    }

    protected function createUrlRoutesTable(): void
    {
        $t = 'um_cms_url_routes';
        Schema::dropIfExists($t);
        Schema::create($t, function (Blueprint $table): void {
            $table->id();
            $table->string('locale', 12)->index();
            $table->string('normalized_path', 2048);
            $table->morphs('urlable');
            $table->string('kind', 32)->nullable()->index();
            $table->timestamps();

            $table->unique(['locale', 'normalized_path']);
        });
    }
}
