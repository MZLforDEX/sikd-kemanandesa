<?php

namespace App\Services;

use App\Models\Notification as AppNotification;
use App\Models\PushSubscription;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class WebPushService
{
    protected ?WebPush $webPush = null;

    public function __construct()
    {
        $publicKey = env('VAPID_PUBLIC_KEY');
        $privateKey = env('VAPID_PRIVATE_KEY');

        if ($publicKey && $privateKey) {
            // Fix for Windows OpenSSL config path issue in library
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $possiblePaths = [
                    'D:\\xampp\\apache\\conf\\openssl.cnf',
                    'C:\\xampp\\apache\\conf\\openssl.cnf',
                    'D:\\xampp\\php\\extras\\openssl\\openssl.cnf',
                    'C:\\xampp\\php\\extras\\openssl\\openssl.cnf',
                    'D:\\xampp\\php\\extras\\ssl\\openssl.cnf',
                    'C:\\xampp\\php\\extras\\ssl\\openssl.cnf',
                ];
                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        putenv("OPENSSL_CONF={$path}");
                        break;
                    }
                }
            }

            $auth = [
                'VAPID' => [
                    'subject' => 'mailto:aduan@desa-awa.go.id',
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ];
            $this->webPush = new WebPush($auth);
        }
    }

    public function sendPush(AppNotification $notification): void
    {
        if (!$this->webPush) {
            return;
        }

        $subscriptions = PushSubscription::where('user_id', $notification->user_id)->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $payload = json_encode([
            'title' => $notification->title,
            'body' => $notification->message,
            'url' => $notification->link ?: '/',
        ]);

        foreach ($subscriptions as $sub) {
            $this->webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                    'contentEncoding' => $sub->content_encoding ?: 'aes128gcm',
                ]),
                $payload
            );
        }

        foreach ($this->webPush->flush() as $report) {
            if (!$report->isSuccess() && $report->isSubscriptionExpired()) {
                // Remove expired push endpoint
                PushSubscription::where('endpoint', $report->getEndpoint())->delete();
            }
        }
    }
}
