<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;

class StepUpCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $code,
        public Carbon $expiresAt,
        public ?string $capability = null,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(Lang::get('Security Verification Code'))
            ->line(Lang::get('Use this code to confirm your sensitive action: :code', ['code' => $this->code]))
            ->line(Lang::get('This code will expire at :time', ['time' => $this->expiresAt->format('H:i')]))
            ->line(Lang::get('If you did not trigger this action, you can ignore this email.'));
    }
}
