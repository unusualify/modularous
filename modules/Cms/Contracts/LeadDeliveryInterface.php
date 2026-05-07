<?php

namespace Modules\Cms\Contracts;

interface LeadDeliveryInterface
{
    public function deliver(array $leadPayload): array;
}
