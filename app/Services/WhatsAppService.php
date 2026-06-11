<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a notification message via WhatsApp Gateway.
     */
    public function sendNotification(Notification $notification): void
    {
        $user = $notification->user;

        if (!$user || !$user->phone) {
            return;
        }

        $token = config('services.whatsapp.token');
        $endpoint = config('services.whatsapp.endpoint', 'https://api.fonnte.com/send');

        if (empty($token)) {
            Log::info('WhatsApp Service: Sending skipped. WA_GATEWAY_TOKEN is not configured.');
            return;
        }

        $formattedPhone = $this->formatPhoneNumber($user->phone);
        if (empty($formattedPhone)) {
            Log::warning("WhatsApp Service: Invalid phone number format for User ID {$user->id}: {$user->phone}");
            return;
        }

        $link = $notification->link ? url($notification->link) : url('/');
        $messageText = "🔔 *[SIKD Desa Awa]*\n"
                     . "*Judul*: {$notification->title}\n"
                     . "*Pesan*: {$notification->message}\n\n"
                     . "*Tautan*: {$link}";

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post($endpoint, [
                'target' => $formattedPhone,
                'message' => $messageText,
            ]);

            if (!$response->successful()) {
                Log::error('WhatsApp Service: Request failed. Status: ' . $response->status() . ' - Body: ' . $response->body());
            } else {
                Log::debug("WhatsApp Service: Notification successfully sent to {$formattedPhone}.");
            }
        } catch (\Throwable $e) {
            Log::error('WhatsApp Service: Exception thrown while sending notification: ' . $e->getMessage());
        }
    }

    /**
     * Format/Normalize phone numbers to international standard (62 for Indonesia).
     */
    public function formatPhoneNumber(string $phone): ?string
    {
        // Remove all non-digits (spaces, dashes, parentheses, plus sign)
        $clean = preg_replace('/[^\d]/', '', $phone);

        if (empty($clean)) {
            return null;
        }

        // Check if starts with 62
        if (str_starts_with($clean, '62')) {
            return $clean;
        }

        // Convert leading 0 to 62
        if (str_starts_with($clean, '0')) {
            return '62' . substr($clean, 1);
        }

        // Prepend 62 if it starts with 8 (e.g. 812xxxxxxxx)
        if (str_starts_with($clean, '8')) {
            return '62' . $clean;
        }

        return $clean;
    }
}
