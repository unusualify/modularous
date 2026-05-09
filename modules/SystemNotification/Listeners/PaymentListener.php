<?php

namespace Modules\SystemNotification\Listeners;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\SystemNotification\Events\PaymentCompleted;
use Modules\SystemNotification\Events\PaymentFailed;
use Modules\SystemNotification\Notifications\PaymentCompletedNotification;
use Modules\SystemNotification\Notifications\PaymentFailedNotification;
use Unusualify\Modularous\Entities\User;

class PaymentListener implements ShouldHandleEventsAfterCommit, ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PaymentCompleted|PaymentFailed $event): void
    {
        $activeUser = auth()->user();
        $isSuccess = get_class($event) === PaymentCompleted::class;

        $payment = $event->model;

        if ($isSuccess) {
            try {
                $user = $payment->price->priceable->creator;
                $user->notify(new PaymentCompletedNotification($payment));
            } catch (\Throwable $th) {
                // throw $th;
            }
        } else {
            $superadmins = User::role('superadmin')->get();
            foreach ($superadmins as $superadmin) {
                $superadmin->notify(new PaymentFailedNotification($payment));
            }
        }
    }
}
