<?php

namespace TestModules\SystemModule\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Minimal fixture model for ModuleTest. Extends Laravel's base Model
 * to avoid Unusualify Model traits (activitylog, ManageEloquent, etc.)
 * that require extra setup in test environment.
 */
class Item extends Model
{
    protected $table = 'system_module_items';

    protected $fillable = ['name'];
}
