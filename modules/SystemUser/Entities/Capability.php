<?php

namespace Modules\SystemUser\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Unusualify\Modularity\Entities\Model;
use Modules\SystemUser\Entities\Traits\FlushesSecurityCache;

class Capability extends Model
{
    use FlushesSecurityCache;

    protected $fillable = [
        'name',
        'title',
        'strict_route_binding',
        'requires_step_up',
        'published',
    ];

    // protected $casts = [
    //     'strict_route_binding' => 'boolean',
    //     'requires_step_up' => 'boolean',
    //     'published' => 'boolean',
    // ];

    public function getTable()
    {
        return modularityConfig('tables.capabilities', parent::getTable());
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            modularityConfig('tables.role_capability', 'um_role_capability'),
            'capability_id',
            'role_id'
        );
    }

    public function routes(): BelongsToMany
    {
        return $this->belongsToMany(
            CapabilityRoute::class,
            modularityConfig('tables.capability_capability_route', 'um_capability_capability_route'),
            'capability_id',
            'capability_route_id'
        );
    }
}
