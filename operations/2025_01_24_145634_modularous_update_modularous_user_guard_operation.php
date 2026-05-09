<?php

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\ConsoleOutput;
use TimoKoerber\LaravelOneTimeOperations\OneTimeOperation;
use Unusualify\Modularous\Facades\Modularous;

return new class extends OneTimeOperation
{
    use InteractsWithIO;

    public function __construct()
    {
        $this->output = new ConsoleOutput;
    }

    /**
     * Determine if the operation is being processed asynchronously.
     */
    protected bool $async = true;

    /**
     * The queue that the job will be dispatched to.
     */
    protected string $queue = 'default';

    /**
     * A tag name, that this operation can be filtered by.
     */
    protected ?string $tag = 'modularous';

    /**
     * Process the operation.
     */
    public function process(): void
    {
        $this->info("\n\tUpdating Modularous User Guard");

        $modularousAuthGuardName = Modularous::getAuthGuardName();
        $permissionsTable = config('permission.table_names.permissions', 'permissions');
        DB::table($permissionsTable)
            ->where('guard_name', 'unusual_users')
            ->update(['guard_name' => $modularousAuthGuardName]);

        $rolesTable = config('permission.table_names.roles', 'roles');
        DB::table($rolesTable)
            ->where('guard_name', 'unusual_users')
            ->update(['guard_name' => $modularousAuthGuardName]);

        $this->info("\tPermissions guard names updated from 'unusual_users' to '{$modularousAuthGuardName}'");
        $this->output->writeln('');
    }
};
