<?php

namespace Unusualify\Modularity\Tests\Support\Stubs;

use Illuminate\Support\Collection;

/**
 * Stub class for hostable route tests - provides required static and instance methods.
 */
class HostableStub
{
    public $url = 'stub.example.com';

    public function getTable(): string
    {
        return 'hostable_stubs';
    }

    public static function hostableRouteBindingParameter(): string
    {
        return '{hostable_stub}';
    }

    public static function hostables(): Collection
    {
        return collect([]);
    }
}
