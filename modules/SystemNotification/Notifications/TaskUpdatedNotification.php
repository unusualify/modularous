<?php

namespace Modules\SystemNotification\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\SystemTask\Entities\Assignment;

class TaskUpdatedNotification extends FeatureNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Assignment $model
     * @return void
     */
    public function __construct(\Unusualify\Modularous\Entities\Assignment $model)
    {
        parent::__construct($model);
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        return true;
    }

    public function toArray($notifiable): array
    {
        return [

        ];
    }

    public function getNotificationMailSubject(object $notifiable, Model $model): string
    {
        $assignable = $model->assignable;

        $default = __('The Task Updated');

        if (isset(static::$mailSubjectCallbacks[static::class]) && is_callable(static::$mailSubjectCallbacks[static::class])) {
            return call_user_func(static::$mailSubjectCallbacks[static::class], $notifiable, $assignable, $default);
        }

        return $default;
    }

    public function getModelTitleField(Model $model): string
    {
        $assignable = $model->assignable;

        $default = parent::getModelTitleField($assignable);

        if (isset(static::$modelTitleFieldCallbacks[static::class]) && is_callable(static::$modelTitleFieldCallbacks[static::class])) {
            return call_user_func(static::$modelTitleFieldCallbacks[static::class], $assignable, $default);
        }

        return $default;
    }

    public function getNotificationRedirector(object $notifiable, Model $model)
    {
        return parent::getNotificationRedirector($notifiable, $model->assignable);
    }

    public function getMailMessage(MailMessage $mailMessage, object $notifiable, Model $model): MailMessage
    {
        $assignable = $model->assignable;

        $mailMessage = $mailMessage->line('Status: ' . $model->status_label);

        if (isset(static::$mailMessageClassCallbacks[static::class]) && is_callable(static::$mailMessageClassCallbacks[static::class])) {
            return call_user_func(static::$mailMessageClassCallbacks[static::class], $mailMessage, $notifiable, $assignable);
        }

        return $mailMessage;
    }
}
