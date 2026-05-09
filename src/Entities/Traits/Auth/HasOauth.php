<?php

namespace Unusualify\Modularous\Entities\Traits\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Socialite\Contracts\User;
use Unusualify\Modularous\Entities\UserOauth;

trait HasOauth
{
    /**
     * @return HasMany
     */
    public function providers()
    {

        return $this->hasMany(UserOauth::class, 'user_id');

    }

    /**
     * @param string $provider Socialite provider
     * @return Model|false
     */
    public function linkProvider(User $oauthUser, $provider)
    {

        $provider = new UserOauth([
            'token' => $oauthUser->token,
            'avatar' => $oauthUser->avatar,
            'provider' => $provider,
            'oauth_id' => $oauthUser->id,
        ]);

        return $this->providers()->save($provider);
    }
}
