<?php

namespace Unusualify\Modularous\Console\Module;

use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Facades\Modularous;

class RemoveModuleCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularous:remove:module {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove completely a module.';

    protected $aliases = [
        'm:r:m',
        'mod:r:module',
        'unusual:remove:module',
    ];

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        Modularous::disableCache();

        $moduleName = $this->argument('module');

        $module = Modularous::find($moduleName);

        // $this->call('optimize:clear');

        $this->call('modularous:migrate:rollback', [
            'module' => $moduleName,
        ]);

        Modularous::deleteModule($moduleName);

        $this->info("Module [{$moduleName}] removed completely!");

        return 0;
    }
}
