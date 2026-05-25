<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Illuminate\Support\Facades\Notification;

class NotifyAdminOfNewOrder
{
    public function handle(OrderPlaced $event): void
    {
        $adminEmail = config('app.admin_email');

        if (! $adminEmail) {
            return;
        }

        $admin = User::query()->where('email', $adminEmail)->first();

        if ($admin) {
            $admin->notify(new NewOrderNotification($event->order));

            return;
        }

        Notification::route('mail', $adminEmail)
            ->notify(new NewOrderNotification($event->order));
    }
}
