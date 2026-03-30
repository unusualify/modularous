<?php

namespace Unusualify\Modularity\Entities\Traits\Core;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;

trait Rolable
{
    use HasRoles;

    public static function bootRolable()
    {
        static::addGlobalScope('roles_meta', function ($query) {
            $query->with('rolesMetaRelation');
        });
    }

    public function initializeRolable()
    {
        $this->append(['roles_meta', 'is_superadmin', 'is_client']);
    }

    /**
     * Minimal roles relation (id, name, title) for roles_meta.
     * Does not affect the original roles relationship.
     */
    public function rolesMetaRelation(): BelongsToMany
    {
        $rolesTable = config('permission.table_names.roles');
        $relation = $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            PermissionRegistrar::$pivotRole
        )->select("{$rolesTable}.id", "{$rolesTable}.name", "{$rolesTable}.title");

        if (! PermissionRegistrar::$teams) {
            return $relation;
        }

        return $relation->wherePivot(PermissionRegistrar::$teamsKey, getPermissionsTeamId())
            ->where(function ($q) use ($rolesTable) {
                $teamField = "{$rolesTable}." . PermissionRegistrar::$teamsKey;
                $q->whereNull($teamField)->orWhere($teamField, getPermissionsTeamId());
            });
    }

    protected function rolesMeta(): Attribute
    {
        return new Attribute(
            get: fn () => $this->rolesMetaRelation
        );
    }

    public function isSuperadmin(): Attribute
    {
        return new Attribute(
            get: fn () => collect($this->roles_meta)
                ->contains(fn ($role) => $role['name'] === 'superadmin'),
        );
    }

    public function isAdmin(): Attribute
    {
        return new Attribute(
            get: fn () => collect($this->roles_meta)
                ->contains(fn ($role) => $role['name'] === 'admin'),
        );
    }

    /**
     * @deprecated Use $this->is_client instead
     */
    public function isClient(): bool
    {
        return $this->is_client;
    }

    public function getIsClientAttribute()
    {
        return collect($this->roles_meta)
            ->contains(fn ($role) => Str::startsWith($role['name'], 'client'));
    }

    public function existRole(string|Model $role): bool
    {
        $roleName = $role instanceof Model ? $role->name : $role;

        return collect($this->roles_meta)
            ->contains(fn ($role) => $role['name'] === $roleName);
    }

    public function existRoles(array $roles): bool
    {
        return collect($roles)
            ->every(fn ($role) => $this->existRole($role));
    }

    public function existAnyRole(array $roles): bool
    {
        return collect($roles)
            ->some(fn ($role) => $this->existRole($role));
    }
}
