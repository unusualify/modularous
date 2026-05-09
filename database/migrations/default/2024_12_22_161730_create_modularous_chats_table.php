<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Facades\MigrationBackup;

return new class extends Migration
{
    public function up()
    {
        $modularousChatsTable = modularousConfig('tables.chats', 'modularous_chats');
        $modularousChatMessagesTable = modularousConfig('tables.chat_messages', 'modularous_chat_messages');

        if (! Schema::hasTable($modularousChatsTable)) {
            Schema::create($modularousChatsTable, function (Blueprint $table) {
                // this will create an id, name field
                createDefaultTableFields($table);
                $table->uuidMorphs('chatable');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable($modularousChatMessagesTable)) {
            Schema::create($modularousChatMessagesTable, function (Blueprint $table) use ($modularousChatsTable) {
                // this will create an id, name field
                createDefaultTableFields($table);
                $table->foreignId('chat_id')
                    ->constrained(table: $modularousChatsTable, indexName: 'fk_chat_messages_chat_id')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->text('content')->nullable();
                $table->boolean('is_read')->default(false);
                $table->boolean('is_starred')->default(false);
                $table->boolean('is_pinned')->default(false);
                $table->boolean('is_sent')->default(false);
                $table->boolean('is_received')->default(false);

                $table->timestamp('edited_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        MigrationBackup::restore();
    }

    public function down()
    {
        $modularousChatsTable = modularousConfig('tables.chats', 'modularous_chats');
        $modularousChatMessagesTable = modularousConfig('tables.chat_messages', 'modularous_chat_messages');

        MigrationBackup::backup($modularousChatMessagesTable);
        MigrationBackup::backup($modularousChatsTable);

        Schema::dropIfExists($modularousChatMessagesTable);
        Schema::dropIfExists($modularousChatsTable);
    }
};
