<?php

namespace Modules\SystemUser\Entities\Traits;

use Unusualify\Modularous\Services\Security\SecurityService;

trait FlushesSecurityCache
{
    protected static function bootFlushesSecurityCache(): void
    {
        $flush = static function () {
            app(SecurityService::class)->flushPersistentCache();
        };

        static::saved($flush);
        static::deleted($flush);

        if (method_exists(static::class, 'restored')) {
            static::restored($flush);
        }
    }
}
