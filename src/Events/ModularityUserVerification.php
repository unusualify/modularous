<?php

namespace Unusualify\Modularity\Events;

use Illuminate\Queue\SerializesModels;

class ModularityUserVerification
{
    use SerializesModels;

    public function __construct(public $request) {}
}
