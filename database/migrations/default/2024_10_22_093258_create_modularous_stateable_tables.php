<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Facades\MigrationBackup;
use Unusualify\Modularous\Facades\Modularous;

return new class extends Migration
{
    public function up()
    {
        $stateTable = Modularous::config('tables.states', 'modularous_states');
        $stateTranslationsTable = Modularous::config('tables.state_translations', 'modularous_state_translations');
        $stateablesTable = Modularous::config('tables.stateables', 'modularous_stateables');

        if (! Schema::hasTable($stateTable)) {
            Schema::create($stateTable, function (Blueprint $table) {
                // this will create an id, name field
                createDefaultTableFields($table);
                $table->string('code');
                $table->string('color');
                $table->string('icon');
                // a "published" column, and soft delete and timestamps columns
                createDefaultExtraTableFields($table);
            });
        }

        if (! Schema::hasTable($stateTranslationsTable)) {
            Schema::create($stateTranslationsTable, function (Blueprint $table) use ($stateTable) {
                createDefaultTranslationsTableFields($table, 'state', $stateTable);
                $table->string('name');
            });
        }

        if (! Schema::hasTable($stateablesTable)) {
            Schema::create($stateablesTable, function (Blueprint $table) use ($stateablesTable, $stateTable) {
                createDefaultMorphPivotTableFields($table, modelName: 'State', tableName: $stateablesTable, morphedTableName: $stateTable);
                $table->timestamps();
            });
        }

        MigrationBackup::restore($stateTable);
        MigrationBackup::restore($stateTranslationsTable);
        MigrationBackup::restore($stateablesTable);
    }

    public function down()
    {
        $stateTable = Modularous::config('tables.states', 'modularous_states');
        $stateTranslationsTable = Modularous::config('tables.state_translations', 'modularous_state_translations');
        $stateablesTable = Modularous::config('tables.stateables', 'modularous_stateables');

        MigrationBackup::backup($stateablesTable);
        MigrationBackup::backup($stateTranslationsTable);
        MigrationBackup::backup($stateTable);

        Schema::dropIfExists($stateablesTable);
        Schema::dropIfExists($stateTranslationsTable);
        Schema::dropIfExists($stateTable);
    }
};
