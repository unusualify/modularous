<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $filepondsTable = modularousConfig('tables.fileponds', 'modularous_fileponds');

        if (! Schema::hasTable($filepondsTable)) {
            Schema::create($filepondsTable, function (Blueprint $table) {
                $table->{modularousIncrementsMethod()}('id');
                $table->uuidMorphs('filepondable');
                $table->text('uuid')->unique();
                $table->text('file_name');
                $table->string('role');
                $table->string('locale');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        $temporariesTable = modularousConfig('tables.filepond_temporaries', 'modularous_filepond_temporaries');

        if (! Schema::hasTable($temporariesTable)) {
            Schema::create($temporariesTable, function (Blueprint $table) {
                $table->{modularousIncrementsMethod()}('id');
                $table->string('file_name');
                $table->string('folder_name');
                $table->string('input_role');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        $filepondsTable = modularousConfig('tables.fileponds', 'modularous_fileponds');
        $temporariesTable = modularousConfig('tables.filepond_temporaries', 'modularous_filepond_temporaries');

        Schema::dropIfExists($filepondsTable);
        Schema::dropIfExists($temporariesTable);
    }
};
