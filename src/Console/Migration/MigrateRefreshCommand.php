<?php

namespace Unusualify\Modularous\Console\Migration;

use Illuminate\Console\Command;
use Nwidart\Modules\Module;
use Symfony\Component\Console\Input\InputArgument;
use Unusualify\Modularous\Facades\Modularous;

class MigrateRefreshCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'modularous:migrate:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh migrations of the specified module';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var Module $module */
        $module = Modularous::findOrFail($this->argument('module'));

        try {
            $this->call('modularous:migrate:rollback', [
                'module' => $module->getName(),
            ]);
            $this->call('modularous:migrate', [
                'module' => $module->getName(),
            ]);

        } catch (\Throwable $th) {
            $this->comment(" {$module->getStudlyName()} Module cannot be refreshed.");

        }

        return 0;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['module', InputArgument::REQUIRED, 'Module name.'],
        ];
    }
}
