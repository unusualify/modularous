<?php

namespace Unusualify\Modularity\Events\Traits;

use Illuminate\Support\Facades\Auth;

trait EventUser
{
    /**
     * The user model.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $user;

    public function setupEventUser()
    {
        $this->user = Auth::user();
    }

    /**
     * Check if the user model exists.
     *
     * @return bool
     */
    public function hasUser()
    {
        return $this->user !== null;
    }

    /**
     * Get the user model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getUser()
    {
        return $this->user;
    }
}
