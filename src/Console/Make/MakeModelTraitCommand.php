<?php

namespace Unusualify\Modularous\Console\Make;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nwidart\Modules\Support\Stub;
use Unusualify\Modularous\Console\BaseCommand;

class MakeModelTraitCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularous:make:model:trait {name}';

    protected $aliases = [
        'mod:c:model:trait',
        'modularous:create:model:trait',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Model trait';

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
        $name = $this->argument('name');
        $studlyName = Str::studly($name);

        $replacements = [
            'STUDLY_NAME' => $studlyName,
        ];

        $content = (new Stub('/classes/model-trait.stub', $replacements))->render();

        $path = get_modularous_vendor_path("src/Entities/Traits/Has{$studlyName}.php");

        File::put($path, $content);

        return 0;
    }
}
