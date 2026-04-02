<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $capabilitiesTable = modularityConfig('tables.capabilities', 'um_capabilities');
        $capabilityRoutesTable = modularityConfig('tables.capability_routes', 'um_capability_routes');
        $pivotTable = modularityConfig('tables.role_capability', 'um_role_capability');
        $capabilityRoutePivotTable = modularityConfig('tables.capability_capability_route', 'um_capability_capability_route');
        $rolesTable = config('permission.table_names.roles', 'roles');

        if (! Schema::hasTable($capabilitiesTable)) {
            Schema::create($capabilitiesTable, function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('title')->nullable();
                $table->boolean('strict_route_binding')->default(false);
                $table->boolean('requires_step_up')->default(false);
                $table->boolean('published')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        } elseif (! Schema::hasColumn($capabilitiesTable, 'strict_route_binding')) {
            Schema::table($capabilitiesTable, function (Blueprint $table) {
                $table->boolean('strict_route_binding')->default(false)->after('title');
            });
        }

        if (! Schema::hasTable($pivotTable)) {
            Schema::create($pivotTable, function (Blueprint $table) use ($capabilitiesTable, $rolesTable) {
                $table->id();
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('capability_id');
                $table->timestamps();

                $table->unique(['role_id', 'capability_id'], 'um_role_capability_unique');
                $table->foreign('role_id')->references('id')->on($rolesTable)->cascadeOnDelete();
                $table->foreign('capability_id')->references('id')->on($capabilitiesTable)->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable($capabilityRoutesTable)) {
            Schema::create($capabilityRoutesTable, function (Blueprint $table) {
                $table->id();
                $table->string('route_name')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable($capabilityRoutePivotTable)) {
            Schema::create($capabilityRoutePivotTable, function (Blueprint $table) use ($capabilitiesTable, $capabilityRoutesTable) {
                $table->id();
                $table->unsignedBigInteger('capability_id');
                $table->unsignedBigInteger('capability_route_id');
                $table->timestamps();

                $table->unique(['capability_id', 'capability_route_id'], 'um_capability_capability_route_unique');
                $table->foreign('capability_id')->references('id')->on($capabilitiesTable)->cascadeOnDelete();
                $table->foreign('capability_route_id')->references('id')->on($capabilityRoutesTable)->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(modularityConfig('tables.capability_capability_route', 'um_capability_capability_route'));
        Schema::dropIfExists(modularityConfig('tables.capability_routes', 'um_capability_routes'));
        Schema::dropIfExists(modularityConfig('tables.role_capability', 'um_role_capability'));
        Schema::dropIfExists(modularityConfig('tables.capabilities', 'um_capabilities'));
    }
};
