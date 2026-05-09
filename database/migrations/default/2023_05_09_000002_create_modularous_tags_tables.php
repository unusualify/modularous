<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $modularousTaggedTable = modularousConfig('tables.tagged', 'modularous_tagged');

        if (! Schema::hasTable($modularousTaggedTable)) {
            Schema::create($modularousTaggedTable, function (Blueprint $table) {
                $table->{modularousIncrementsMethod()}('id');
                $table->uuidMorphs('taggable');
                // $table->string('taggable_type');
                // $table->integer('taggable_id')->unsigned();
                $table->integer('tag_id')->unsigned();

                // $table->engine = 'InnoDB';

                // $table->index(['taggable_type', 'taggable_id']);
            });
        }

        $modularousTagsTable = modularousConfig('tables.tags', 'modularous_tags');

        if (! Schema::hasTable($modularousTagsTable)) {
            Schema::create($modularousTagsTable, function (Blueprint $table) {
                $table->{modularousIncrementsMethod()}('id');
                $table->string('namespace');
                $table->string('slug');
                $table->string('name');
                $table->integer('count')->default(0)->unsigned();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(modularousConfig('tables.tags', 'modularous_tags'));
        Schema::dropIfExists(modularousConfig('tables.tagged', 'modularous_tagged'));
    }
};
