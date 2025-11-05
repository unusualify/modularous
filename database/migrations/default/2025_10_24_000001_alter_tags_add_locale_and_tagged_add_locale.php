<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tagsTable = modularityConfig('tables.tags', 'modularity_tags');

        if (Schema::hasTable($tagsTable)) {
            Schema::table($tagsTable, function (Blueprint $table) use ($tagsTable) {
                if (! Schema::hasColumn($tagsTable, 'locale')) {
                    $table->string('locale', 12)->default(app()->getFallbackLocale())->after('namespace');
                }
                // Optional helpful index for quick lookups
                $table->index(['namespace', 'slug', 'locale'], $tagsTable . '_ns_slug_locale_idx');
            });
        }
    }

    public function down()
    {
        $tagsTable = modularityConfig('tables.tags', 'modularity_tags');

        if (Schema::hasTable($tagsTable)) {
            Schema::table($tagsTable, function (Blueprint $table) use ($tagsTable) {
                if (Schema::hasColumn($tagsTable, 'locale')) {
                    $table->dropColumn('locale');
                }
                // drop index if exists
                try { $table->dropIndex($tagsTable . '_ns_slug_locale_idx'); } catch (Throwable $e) {}
            });
        }
    }
};
