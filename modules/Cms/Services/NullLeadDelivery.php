<?php

namespace Modules\Cms\Services;

use Modules\Cms\Contracts\LeadDeliveryInterface;

class NullLeadDelivery implements LeadDeliveryInterface
{
    public function deliver(array $leadPayload): array
    {
        return [
            'delivered' => false,
            'driver' => 'null',
            'message' => 'CRM/Webhook integration is not enabled in v1.',
            'payload' => $leadPayload,
        ];
    }
}
