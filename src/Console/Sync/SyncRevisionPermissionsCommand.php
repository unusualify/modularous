<?php

namespace Unusualify\Modularity\Console\Sync;

use ReflectionMethod;
use Spatie\Permission\Models\Permission;
use Unusualify\Modularity\Console\BaseCommand;
use Unusualify\Modularity\Entities\Enums\Permission as PermissionEnum;
use Unusualify\Modularity\Entities\Traits\HasRevisions;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Facades\ModularityFinder;

class SyncRevisionPermissionsCommand extends BaseCommand
{
    /**
     * @var string
     */
    protected $signature = 'modularity:sync:revision-permissions
        {--dry-run : List permissions without writing to the database}';

    /**
     * @var string
     */
    protected $description = 'Create Spatie permissions for revision routes ({route}_revision_approve, {route}_revision_reject, {route}_revision_restore) for models using HasRevisions';

    public function handle(): int
    {
        $models = ModularityFinder::getModelsWithTrait(HasRevisions::class);

        if (count($models) === 0) {
            $this->warn('No models using HasRevisions were found.');

            return 0;
        }

        // $guard = config('auth.defaults.guard', 'web');
        $guard = Modularity::getAuthGuardName();

        $suffixes = [
            PermissionEnum::REVISION_APPROVE->value,
            PermissionEnum::REVISION_REJECT->value,
            PermissionEnum::REVISION_RESTORE->value,
        ];

        $created = [];

        foreach ($models as $modelClass) {
            $prefixes = $this->resolveRoutePrefixesForModel($modelClass);

            foreach ($prefixes as $prefix) {
                foreach ($suffixes as $suffix) {
                    $name = "{$prefix}_{$suffix}";

                    if ($this->option('dry-run')) {
                        $this->line("[dry-run] {$name}");

                        continue;
                    }

                    $permission = Permission::firstOrCreate(
                        ['name' => $name, 'guard_name' => $guard],
                        []
                    );

                    if ($permission->wasRecentlyCreated) {
                        $created[] = $name;
                    }
                }
            }
        }

        if ($this->option('dry-run')) {
            return 0;
        }

        foreach ($created as $name) {
            $this->info("Created permission: {$name}");
        }

        $this->info('Revision permissions synced.');

        return 0;
    }

    /**
     * Uses {@see HasRevisions::revisionPermissionPrefix()} when overridden on the model.
     *
     * @return list<string>
     */
    protected function resolveRoutePrefixesForModel(string $modelClass): array
    {
        $method = new ReflectionMethod($modelClass, 'revisionPermissionPrefix');

        if ($method->getDeclaringClass()->getName() === HasRevisions::class) {
            $this->warn("Skipping {$modelClass}: override protected function revisionPermissionPrefix(): ?string (kebab-case route name).");

            return [];
        }

        $method->setAccessible(true);
        $instance = new $modelClass;
        $prefix = $method->invoke($instance);

        if (! is_string($prefix) || $prefix === '') {
            $this->warn("Skipping {$modelClass}: revisionPermissionPrefix() returned empty.");

            return [];
        }

        return [$prefix];
    }
}
