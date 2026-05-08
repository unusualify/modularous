<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\HasApiTokens;
use Unusualify\Modularity\Database\Factories\UserFactory;
use Unusualify\Modularity\Entities\Traits\Auth\CanRegister;
use Unusualify\Modularity\Entities\Traits\Auth\HasOauth;
use Unusualify\Modularity\Entities\Traits\Core\HasCompany;
use Unusualify\Modularity\Entities\Traits\Core\HasCapabilities;
use Unusualify\Modularity\Entities\Traits\Core\ModelHelpers;
use Unusualify\Modularity\Entities\Traits\Core\Rolable;
use Unusualify\Modularity\Entities\Traits\HasFileponds;
use Unusualify\Modularity\Entities\Traits\IsTranslatable;
use Unusualify\Modularity\Notifications\GeneratePasswordNotification;
use Unusualify\Modularity\Notifications\ResetPasswordNotification;

class User extends Authenticatable implements HasLocalePreference, MustVerifyEmailContract
{
    use HasApiTokens,
        HasFactory,
        Rolable,
        HasCapabilities,
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
    }

    protected static function newFactory(): Factory
    {
        return UserFactory::new();
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

    protected function avatar(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->fileponds
                ->filter(fn ($filepond) => $filepond->role === 'avatar')
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
        $this->notify(new ResetPasswordNotification($token));
    }

    public function preferredLocale()
    {
        return $this->language ?? app()->getLocale();
    }
}
