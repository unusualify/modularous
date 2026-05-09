<?php

namespace Unusualify\Modularous\Events\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait EventUser
{
    /**
     * The user model.
     *
     * @var Model
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
     * @return Model
     */
    public function getUser()
    {
        return $this->user;
    }
}
