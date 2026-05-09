<?php

namespace Unusualify\Modularous\Console\Module;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Facades\Modularous;

class FixModuleCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'modularous:fix:module';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes the un-desired changes on module\'s config file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $moduleName = studlyName($this->argument('module'));
        $routes = Modularous::find($moduleName)->getRoutes();

        foreach ($routes as $key => $routeName) {

            $this->call('modularous:make:route', [
                'module' => $moduleName,
                'route' => $routeName,
                '--fix' => true,
            ]);

        }

        return 0;
    }

    protected function getArguments()
    {
        return [
            ['module', InputArgument::REQUIRED, 'The name of module will be used.'],
            ['route', InputArgument::OPTIONAL, 'The name of the route.', null],
        ];
    }

    protected function getOptions()
    {
        return array_merge([
            ['migration', null, InputOption::VALUE_NONE, 'Fix will create migrations'],

        ], modularousTraitOptions());
    }
}
