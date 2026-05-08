<?php

namespace Unusualify\Modularity\Services\Security;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SecurityService
{
    protected const CACHE_KEY_CAPABILITIES_MAP = 'modularity.security.capabilities.map';

    protected const CACHE_KEY_REQUIRED_STEP_UP_CAPABILITIES = 'modularity.security.capabilities.step_up.required';

    protected const CACHE_KEY_ROUTE_STEP_UP_CAPABILITIES = 'modularity.security.capabilities.step_up.routes';

    protected const CACHE_TTL_SECONDS = 3600;

    private function config(string $key, $default = null)
    {
        return modularityConfig("security.{$key}", $default);
    }

    public function getCapabilities(): array
    {
        return Cache::remember(
            self::CACHE_KEY_CAPABILITIES_MAP,
            self::CACHE_TTL_SECONDS,
            fn () => $this->buildCapabilitiesMap()
        );
    }

    public function requiredStepUpCapabilities(): array
    {
        return Cache::remember(
            self::CACHE_KEY_REQUIRED_STEP_UP_CAPABILITIES,
            self::CACHE_TTL_SECONDS,
            fn () => $this->buildRequiredStepUpCapabilities()
        );
    }

    public function routeMatchesStepUpCapability(string $capability, ?string $routeName): bool
    {
        return in_array($capability, $this->stepUpCapabilitiesForRoute($routeName), true);
    }

    public function stepUpCapabilitiesForRoute(?string $routeName): array
    {
        $routeName = is_string($routeName) ? trim($routeName) : '';

        if ($routeName === '') {
            return [];
        }

        $map = Cache::remember(
            self::CACHE_KEY_ROUTE_STEP_UP_CAPABILITIES,
            self::CACHE_TTL_SECONDS,
            fn () => $this->buildRouteStepUpCapabilitiesMap()
        );

        return $map[$routeName] ?? [];
    }

    public function matchedUserStepUpCapability(?Authenticatable $user, ?string $routeName, ?string $hintCapability = null): ?string
    {
        if (! $user) {
            return null;
        }

        $routeCapabilities = $this->stepUpCapabilitiesForRoute($routeName);

        if ($routeCapabilities === []) {
            return null;
        }

        if (is_string($hintCapability) && $hintCapability !== '' && ! in_array($hintCapability, $routeCapabilities, true)) {
            return null;
        }

        $userCapabilities = $this->userCapabilities($user);

        if ($userCapabilities === []) {
            return null;
        }

        if (is_string($hintCapability) && $hintCapability !== '' && in_array($hintCapability, $userCapabilities, true)) {
            return $hintCapability;
        }

        foreach ($routeCapabilities as $capability) {
            if (in_array($capability, $userCapabilities, true)) {
                return $capability;
            }
        }

        return null;
    }

    public function userCapabilities(?Authenticatable $user): array
    {
        if (! $user) {
            return [];
        }

        if (method_exists($user, 'getAttribute')) {
            $capabilities = $user->getAttribute('capabilities');

            if (is_array($capabilities)) {
                return array_values(array_unique(array_filter($capabilities, fn ($capability) => is_string($capability) && $capability !== '')));
            }
        }

        $caps = [];

        foreach ($this->getCapabilities() as $role => $roleCaps) {
            if ($this->userHasRole($user, $role)) {
                $caps = array_merge($caps, (array) $roleCaps);
            }
        }

        return array_values(array_unique($caps));
    }

    public function userHasCapability(?Authenticatable $user, string $capability): bool
    {
        if ($user && is_callable([$user, 'hasCapability'])) {
            return (bool) $user->hasCapability($capability);
        }

        return in_array($capability, $this->userCapabilities($user), true);
    }

    public function userRequiresMfa(?Authenticatable $user): bool
    {
        if (! $this->config('mfa.enabled', false) || ! $user) {
            return false;
        }

        $requiredRoles = (array) $this->config('mfa.required_roles', []);

        if (method_exists($user, 'existAnyRole') && $user->existAnyRole($requiredRoles)) {
            return true;
        }

        foreach ($requiredRoles as $requiredRole) {
            if ($this->userHasRole($user, (string) $requiredRole)) {
                return true;
            }
        }

        return false;
    }

    public function userHasEnabledMfa(?Authenticatable $user): bool
    {
        if (! $user) {
            return false;
        }

        $provider = (string) $this->config('mfa.provider', 'email_otp');

        // Email OTP does not require per-user setup columns.
        if ($provider === 'email_otp') {
            return true;
        }

        // Unknown providers should not lock users out by strict setup checks.
        if ($provider !== 'google_totp') {
            return true;
        }

        return (bool) ($user->google_2fa_enabled ?? false)
            && ! empty($user->google_2fa_secret ?? null);
    }

    public function canWriteField(?Authenticatable $user, string $field): bool
    {
        $permission = $this->config("critical_field_permissions.{$field}", null);

        if (! $permission) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if (is_callable([$user, 'can'])) {
            return (bool) $user->can($permission);
        }

        return false;
    }

    public function canPromote(?Authenticatable $user): bool
    {
        if (! $user) {
            return false;
        }

        $allowedRoles = (array) modularityConfig('cms_promotion.approval.roles', []);
        $allowedEmails = (array) modularityConfig('cms_promotion.approval.emails', []);

        foreach ($allowedRoles as $role) {
            if ($this->userHasRole($user, (string) $role)) {
                return true;
            }
        }

        return in_array((string) ($user->email ?? ''), $allowedEmails, true);
    }

    public function flushPersistentCache(bool $warmup = true): void
    {
        Cache::forget(self::CACHE_KEY_CAPABILITIES_MAP);
        Cache::forget(self::CACHE_KEY_REQUIRED_STEP_UP_CAPABILITIES);
        Cache::forget(self::CACHE_KEY_ROUTE_STEP_UP_CAPABILITIES);

        if ($warmup) {
            $this->warmupPersistentCache();
        }
    }

    public function warmupPersistentCache(): void
    {
        $this->getCapabilities();
        $this->requiredStepUpCapabilities();
        Cache::remember(
            self::CACHE_KEY_ROUTE_STEP_UP_CAPABILITIES,
            self::CACHE_TTL_SECONDS,
            fn () => $this->buildRouteStepUpCapabilitiesMap()
        );
    }

    private function buildCapabilitiesMap(): array
    {
        $query = $this->capabilityQuery();

        if (! $query) {
            return [];
        }

        $map = [];

        foreach ($query->where('published', true)->with('roles:id,name')->get(['id', 'name']) as $capability) {
            $name = (string) ($capability->name ?? '');
            if ($name === '') {
                continue;
            }

            foreach ($capability->roles ?? [] as $role) {
                $role = (string) ($role->name ?? '');
                if ($role === '') {
                    continue;
                }

                $map[$role] ??= [];
                $map[$role][] = $name;
            }
        }

        return collect($map)
            ->map(fn ($caps) => array_values(array_unique((array) $caps)))
            ->toArray();
    }

    private function buildRequiredStepUpCapabilities(): array
    {
        $query = $this->capabilityQuery();

        if (! $query) {
            return [];
        }

        return $query
            ->where('published', true)
            ->where('requires_step_up', true)
            ->pluck('name')
            ->filter(fn ($name) => is_string($name) && $name !== '')
            ->values()
            ->all();
    }

    private function buildRouteStepUpCapabilitiesMap(): array
    {
        $query = $this->capabilityQuery();

        if (! $query) {
            return [];
        }

        $map = [];

        $query
            ->where('published', true)
            ->where('requires_step_up', true)
            ->with(['routes' => fn ($q) => $q->where('is_active', true)])
            ->get(['id', 'name'])
            ->each(function ($capability) use (&$map) {
                $capabilityName = (string) ($capability->name ?? '');

                if ($capabilityName === '') {
                    return;
                }

                foreach ($capability->routes ?? [] as $route) {
                    $routeName = (string) ($route->route_name ?? '');

                    if ($routeName === '') {
                        continue;
                    }

                    $map[$routeName] ??= [];
                    $map[$routeName][] = $capabilityName;
                }
            });

        return collect($map)
            ->map(fn ($capabilities) => array_values(array_unique(array_filter($capabilities, fn ($capability) => is_string($capability) && $capability !== ''))))
            ->toArray();
    }

    private function userHasRole(Authenticatable $user, string $role): bool
    {
        if (is_callable([$user, 'hasRole'])) {
            return (bool) $user->hasRole($role);
        }

        return false;
    }

    private function capabilityQuery(): ?Builder
    {
        $class = '\Modules\SystemUser\Entities\Capability';

        if (! class_exists($class)) {
            return null;
        }

        $model = app()->make($class);

        if (! method_exists($model, 'getTable') || ! method_exists($model, 'newQuery')) {
            return null;
        }

        if (! Schema::hasTable($model->getTable())) {
            return null;
        }

        return $model->newQuery();
    }
}
