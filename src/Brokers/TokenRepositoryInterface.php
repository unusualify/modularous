<?php

namespace Unusualify\Modularous\Brokers;

use Illuminate\Auth\Passwords\TokenRepositoryInterface as BaseTokenRepositoryInterface;

interface TokenRepositoryInterface extends BaseTokenRepositoryInterface
{
    public function recentlyCreatedToken($email);

    public function create($email);
}
