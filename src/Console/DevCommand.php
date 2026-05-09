<?php

namespace Unusualify\Modularous\Console;

class DevCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularous:dev {--noInstall}';

    protected $aliases = [
        'unusual:dev',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hot reload unusual assets with custom Vue component, configuration';

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        $this->call('modularous:build', [
            '--hot' => true,
            '--noInstall' => $this->option('noInstall'),
        ]);

        return 0;
    }
}
