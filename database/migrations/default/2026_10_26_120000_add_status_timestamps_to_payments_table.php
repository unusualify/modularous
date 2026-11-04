<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(config('payable.table', 'up_payments'), function (Blueprint $table) {
            $table->timestamp('provisioned_at')->nullable()->after('status');
            $table->timestamp('paid_at')->nullable()->after('provisioned_at');
            $table->timestamp('refunded_at')->nullable()->after('paid_at');
            $table->timestamp('cancelled_at')->nullable()->after('refunded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('payable.table', 'up_payments'), function (Blueprint $table) {
            $table->dropColumn(['provisioned_at', 'paid_at', 'refunded_at', 'cancelled_at']);
        });
    }
};
