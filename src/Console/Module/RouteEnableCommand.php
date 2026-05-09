<?php

namespace Unusualify\Modularous\Console\Module;

use Illuminate\Console\Command;
use Nwidart\Modules\Module;
use Symfony\Component\Console\Input\InputArgument;
use Unusualify\Modularous\Facades\Modularous;

class RouteEnableCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'modularous:route:enable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable the specified module route.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {

        /** @var Module $module */
        $module = Modularous::findOrFail($this->argument('module'));

        $module->setModuleActivator($this->argument('module'));

        $route = $this->argument('route');

        if ($module->isDisabledRoute($route)) {
            $module->enableRoute($route);

            $this->info("Module's Route [{$route}] enabled successful.");
        } else {
            $this->comment("Module's Route [{$route}] has already enabled.");
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
            ['route', InputArgument::REQUIRED, 'Route name.'],
        ];
    }
}
