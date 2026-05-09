<?php

namespace Unusualify\Modularous\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class ModularousUserRegistered
{
    use SerializesModels;

    /**
     * The authenticated user.
     *
     * @var Authenticatable
     */
    public $user;

    /**
     * The request that triggered the event.
     *
     * @var Request
     */
    public $request;

    /**
     * Whether the user was registered via OAuth.
     *
     * @var bool
     */
    protected $isOauth = false;

    /**
     * Create a new event instance.
     *
     * @param Authenticatable $user
     * @param Request $request
     * @return void
     */
    public function __construct($user, $request, bool $isOauth = false)
    {
        $this->user = $user;
        $this->request = $request;
        $this->isOauth = $isOauth;
    }

    public function isOauth(): bool
    {
        return $this->isOauth;
    }
}
