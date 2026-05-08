<?php

namespace Modules\SystemUser\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Unusualify\Modularity\Entities\Model;
use Modules\SystemUser\Entities\Traits\FlushesSecurityCache;

class CapabilityRoute extends Model
{
    use FlushesSecurityCache;

    protected $fillable = [
        'capability_id',
        'route_name',
        'is_active',
    ];

    // protected $casts = [
    //     'is_active' => 'boolean',
    // ];

    public function getTable()
    {
        return modularityConfig('tables.capability_routes', parent::getTable());
    }

    public function capabilities(): BelongsToMany
    {
        return $this->belongsToMany(
            Capability::class,
            modularityConfig('tables.capability_capability_route', 'um_capability_capability_route'),
            'capability_route_id',
            'capability_id'
        );
    }
}
