<?php

namespace Unusualify\Modularous\Console;

use Illuminate\Support\Facades\File;

class RefreshCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'modularous:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move new modularous front sources';

    /**
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        File::deleteDirectory(public_path('vendor/modularous'));

        $this->publishAssets();
        $this->call('cache:clear');
        $this->call('view:clear');

        return 0;
    }

    /**
     * Publishes the package frontend assets.
     *
     * @return void
     */
    private function publishAssets()
    {
        $this->call('vendor:publish', [
            '--provider' => 'Unusualify\Modularous\LaravelServiceProvider',
            '--tag' => 'modularous-assets',
            '--force' => true,
        ]);
    }
}
