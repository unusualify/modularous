<?php

namespace Unusualify\Modularity\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDatabaseCollation extends Command
{
    protected $signature = 'db:check-collation {table}';
    protected $description = 'Check database and connection collations';

    public function handle()
    {
        $table = $this->argument('table');

        $this->info('Database Collation: ' . DB::select('SELECT @@collation_database')[0]->{'@@collation_database'});
        $this->info('Connection Collation: ' . DB::select('SELECT @@collation_connection')[0]->{'@@collation_connection'});

        if(!$table) {
            $this->error('Table is required');
            return;
        }

        if(!DB::getSchemaBuilder()->hasTable($table)) {
            $this->error('Table does not exist');
            return;
        }

        // Check specific table columns
        $columns = DB::select('SHOW FULL COLUMNS FROM ' . $table);
        $this->info("\n" . $table . " table columns:");
        foreach ($columns as $column) {
            $this->line("{$column->Field}: {$column->Collation}");
        }
    }
}
