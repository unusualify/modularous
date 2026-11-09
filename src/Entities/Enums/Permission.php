<?php

namespace Unusualify\Modularous\Entities\Enums;

use Illuminate\Support\Str;

enum Permission: string
{
    // case DASHBOARD = 'dashboard';
    case CREATE = 'create';
    case VIEW = 'view';
    case EDIT = 'edit';
    case DELETE = 'delete';
    case FORCEDELETE = 'forceDelete';
    case RESTORE = 'restore';
    case DUPLICATE = 'duplicate';
    case REORDER = 'reorder';
    case BULK = 'bulk';
    case BULKDELETE = 'bulkDelete';
    case BULKFORCEDELETE = 'bulkForceDelete';
    case BULKRESTORE = 'bulkRestore';
    case REVISION_APPROVE = 'revisionApprove';
    case REVISION_REJECT = 'revisionReject';
    case REVISION_RESTORE = 'revisionRestore';
    case CACHING = 'caching';

    case ACTIVITY = 'activity';
    case SHOW = 'show';

    public static function get($caseName)
    {
        foreach (self::cases() as $case) {
            if ($case->name == $caseName || $case->value == $caseName) {
                return $case->value;
            }
        }

        return null;
    }

    public const MODULE_SEPARATOR = '::';

    /**
     * Prefix shared by every permission of a module route, e.g. `system_user::user`.
     *
     * Same-named routes live in several modules (SystemUser and User both have `user`),
     * so the module is part of the name to keep their permissions apart.
     */
    public static function permissionPrefix(string $moduleName, string $routeName): string
    {
        return Str::snake(Str::studly($moduleName)) . self::MODULE_SEPARATOR . Str::kebab(Str::studly($routeName));
    }

    public static function generatePermissionName($permission, $routeName, string $moduleName)
    {
        return self::permissionPrefix($moduleName, $routeName) . '_' . self::get($permission);
    }

    public static function generatePermissionMiddlewareDefinition($permission, $routeName, string $moduleName)
    {
        return 'can:' . self::generatePermissionName($permission, $routeName, $moduleName);
    }
}
