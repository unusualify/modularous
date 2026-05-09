<?php

namespace Modules\SystemUser\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\SystemUser\Entities\Traits\FlushesSecurityCache;
use Spatie\Permission\Models\Role as SpatieRole;
use Unusualify\Modularous\Entities\Traits\Core\ModelHelpers;

class Role extends SpatieRole
{
    use ModelHelpers, FlushesSecurityCache;

    public function scopeClient($query)
    {
        return $query->where('name', 'like', '%client%');
    }

    public function capabilities(): BelongsToMany
    {
        return $this->belongsToMany(
            Capability::class,
            modularousConfig('tables.role_capability', 'um_role_capability'),
            'role_id',
            'capability_id'
        );
    }
}
