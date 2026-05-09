<?php

namespace Modules\SystemNotification\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Unusualify\Modularous\Entities\ChatMessage;

class UnreadChatMessage
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ChatMessage $model)
    {
        //
    }

    public function broadcastOn(): array
    {
        return [new Channel('unread-chat-message')];
    }
}
