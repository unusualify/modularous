<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds ui_preferences JSON column for persisting user navigation/layout choices.
     */
    public function up(): void
    {
        $usersTable = modularousConfig('tables.users', 'um_users');

        if (Schema::hasTable($usersTable) && ! Schema::hasColumn($usersTable, 'ui_preferences')) {
            Schema::table($usersTable, function (Blueprint $table) {
                $table->json('ui_preferences')->nullable()->after('timezone');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $usersTable = modularousConfig('tables.users', 'um_users');

        if (Schema::hasTable($usersTable) && Schema::hasColumn($usersTable, 'ui_preferences')) {
            Schema::table($usersTable, function (Blueprint $table) {
                $table->dropColumn('ui_preferences');
            });
        }
    }
};
