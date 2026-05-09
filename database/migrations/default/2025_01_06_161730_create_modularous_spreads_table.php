<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $modularousSpreadsTable = modularousConfig('tables.spreads', 'um_spreads');

        if (! Schema::hasTable($modularousSpreadsTable)) {
            Schema::create($modularousSpreadsTable, function (Blueprint $table) {
                createDefaultTableFields($table);
                $table->uuidMorphs('spreadable');
                $table->json('content')->default(new Expression('(JSON_ARRAY())'));
                $table->timestamps();
                $table->softDeletes();
            });

        }

    }

    public function down()
    {
        Schema::dropIfExists(modularousConfig('tables.spreads', 'um_spreads'));
    }
};
