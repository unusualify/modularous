<?php

namespace Unusualify\Modularity\Console\Flush;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class FlushSessionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:flush:sessions {--driver=}';

    protected $aliases = [
        'modularity:session:flush',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush all user sessions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $driver = $this->option('driver') ?: config('session.driver');

        switch ($driver) {
            case 'database': $this->flushDB();

                break;
            case 'file': $this->flushFile();

                break;
            case 'all': $this->flushDB();
                $this->flushFile();

                break;
        }
    }

    private function flushDB()
    {
        $table = config('session.table');
        if (Schema::hasTable($table)) {
            DB::table($table)->truncate();
            error_log($table . ' was truncated');
        } else {
            error_log($table . ' table does not exist');
        }

    }

    private function flushFile()
    {
        $path = config('session.files');

        if (File::exists($path)) {
            $files = File::allFiles($path);
            File::delete($files);
            error_log(count($files) . ' sessions flushed');
        } else {
            error_log('check your session path exists');
        }
    }
}
