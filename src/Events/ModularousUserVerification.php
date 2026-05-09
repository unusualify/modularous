<?php

namespace Unusualify\Modularous\Events;

use Illuminate\Queue\SerializesModels;

class ModularousUserVerification
{
    use SerializesModels;

    public function __construct(public $request) {}
}
