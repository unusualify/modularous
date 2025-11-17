<?php

namespace Unusualify\Modularity\Events;

use Illuminate\Queue\SerializesModels;

class ModularityUserRegistering
{
    use SerializesModels;

    protected $isOauth = false;

    public function __construct(public $request, bool $isOauth = false) {
        $this->isOauth = $isOauth;
    }

    public function isOauth(): bool
    {
        return $this->isOauth;
    }
}
