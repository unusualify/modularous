<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;
use Unusualify\Modularity\Database\Factories\UserFactory;
use Unusualify\Modularity\Entities\Traits\Auth\CanRegister;
use Unusualify\Modularity\Entities\Traits\Auth\HasOauth;
use Unusualify\Modularity\Entities\Traits\Core\HasCompany;
use Unusualify\Modularity\Entities\Traits\Core\ModelHelpers;
use Unusualify\Modularity\Entities\Traits\HasFileponds;
use Unusualify\Modularity\Entities\Traits\IsTranslatable;
use Unusualify\Modularity\Notifications\GeneratePasswordNotification;

class User extends Authenticatable implements HasLocalePreference, MustVerifyEmailContract
{
    use HasApiTokens,
        HasFactory,
        HasRoles,
        IsTranslatable,
        ModelHelpers,
        Notifiable,
        HasFileponds,
        HasOauth,
        CanRegister,
        HasCompany;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'company_id',
        'surname',
        'job_title',
        'email',
        'language',
        'timezone',
        'ui_preferences',
        'phone',
        'country_id',
        'password',
        'published',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'ui_preferences' => 'array',
    ];

    protected $appends = [
        'roles_meta',
        'is_client',
        'is_superadmin',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->password == null) {
                $model->password = Hash::make(env('DEFAULT_USER_PASSWORD', 'Hj84TlN!'));
            }
        });

        static::updated(function ($model) {
            if ($model->isDirty('email')) {
                $model->email_verified_at = null;
                $model->saveQuietly();
            }
        });

        static::addGlobalScope('roles_meta', function ($query) {
            $query->with('rolesMetaRelation');
        });
    }

    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return UserFactory::new();
    }

    /**
     * Minimal roles relation (id, name, title) for roles_meta.
     * Does not affect the original roles relationship.
     */
    public function rolesMetaRelation(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
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

    public function setImpersonating($id)
    {
        Session::put('impersonate', $id);
    }

    public function stopImpersonating()
    {
        Session::forget('impersonate');
    }

    public function isImpersonating()
    {
        return Session::has('impersonate');
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
    public function isClient() : bool
    {
        return $this->is_client;
    }

    public function getIsClientAttribute()
    {
        return collect($this->roles_meta)
            ->contains(fn ($role) => Str::startsWith($role['name'], 'client'));
    }

    protected function avatar(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->fileponds()
                ->where('role', 'avatar')
                ->first()?->mediableFormat()['source'] ?? '/vendor/modularity/jpg/anonymous.jpg',
        );
    }

    public function getTable()
    {
        return modularityConfig('tables.users', parent::getTable());
    }

    /**
     * Send the password generate notification.
     *
     * @param string $token
     * @return void
     */
    public function sendGeneratePasswordNotification($token)
    {
        $this->notify(new GeneratePasswordNotification($token));
    }

    /**
     * Get the email address that should be used for the password generate notification.
     *
     * @return string
     */
    public function getEmailForPasswordGeneration()
    {
        return $this->email;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \Unusualify\Modularity\Notifications\ResetPasswordNotification($token));
    }

    public function preferredLocale()
    {
        return $this->language ?? app()->getLocale();
    }
}
