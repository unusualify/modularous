<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Facades\MigrationBackup;
use Unusualify\Modularity\Facades\Modularity;

return new class extends Migration
{
    public function up()
    {
        $stateTable = Modularity::config('tables.states', 'modularity_states');
        $stateTranslationsTable = Modularity::config('tables.state_translations', 'modularity_state_translations');
        $stateablesTable = Modularity::config('tables.stateables', 'modularity_stateables');

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
        $stateTable = Modularity::config('tables.states', 'modularity_states');
        $stateTranslationsTable = Modularity::config('tables.state_translations', 'modularity_state_translations');
        $stateablesTable = Modularity::config('tables.stateables', 'modularity_stateables');

        MigrationBackup::backup($stateablesTable);
        MigrationBackup::backup($stateTranslationsTable);
        MigrationBackup::backup($stateTable);

        Schema::dropIfExists($stateablesTable);
        Schema::dropIfExists($stateTranslationsTable);
        Schema::dropIfExists($stateTable);
    }
};
