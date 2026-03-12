<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Console\Module;

use Illuminate\Console\Command;
use Unusualify\Modularity\Facades\Modularity;

class RouteStatusCommand extends Command
{
    protected $name = 'modularity:route:status';

    protected $description = 'List route enable/disable status per module.';

    public function handle(): int
    {
        $enabled = Modularity::allEnabled();

        if (empty($enabled)) {
            $this->warn('No enabled modules found.');

            return 0;
        }

        $rows = [];

        foreach ($enabled as $module) {
            $activator = $module->getActivator();
            $statuses = $activator->getRoutesStatuses();

            if (empty($statuses)) {
                $rows[] = [$module->getName(), '(no routes tracked)', ''];
                continue;
            }

            foreach ($statuses as $route => $enabled) {
                $rows[] = [
                    $module->getName(),
                    $route,
                    $enabled ? 'enabled' : 'disabled',
                ];
            }
        }

        $this->table(['Module', 'Route', 'Status'], $rows);

        return 0;
    }
}
