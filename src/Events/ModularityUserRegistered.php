<?php

namespace Unusualify\Modularity\Events;

use Illuminate\Queue\SerializesModels;

class ModularityUserRegistered
{
    use SerializesModels;

    /**
     * The authenticated user.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * The request that triggered the event.
     *
     * @var \Illuminate\Http\Request
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
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param \Illuminate\Http\Request $request
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
