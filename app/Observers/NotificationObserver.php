<?php

namespace App\Observers;

use App\Models\Notification;
use App\Services\WebPushService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class NotificationObserver
{
    /**
     * Handle the Notification "created" event.
     */
    public function created(Notification $notification): void
    {
        try {
            app(WebPushService::class)->sendPush($notification);
        } catch (\Throwable $e) {
            Log::error('WebPush dispatch error: ' . $e->getMessage());
        }

        try {
            app(WhatsAppService::class)->sendNotification($notification);
        } catch (\Throwable $e) {
            Log::error('WhatsApp dispatch error: ' . $e->getMessage());
        }
    }
}
