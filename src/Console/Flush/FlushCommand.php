<?php

namespace Unusualify\Modularous\Console\Flush;

use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Facades\Modularous;

class FlushCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularous:flush';

    protected $aliases = [];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush Modularous caches';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        // handle command
        Modularous::clearCache();

        $this->call('modularous:cache:versions');

        $this->info('Modularous caches flushed');

        return 0;
    }
}
