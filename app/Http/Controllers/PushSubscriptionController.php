<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PushSubscriptionController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => ['required', 'url', 'max:500'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'content_encoding' => ['nullable', 'string', 'max:20'],
        ]);

        $user = auth()->user();

        $user->pushSubscriptions()->updateOrCreate(
            ['endpoint' => $request->endpoint],
            [
                'public_key' => $request->input('keys.p256dh'),
                'auth_token' => $request->input('keys.auth'),
                'content_encoding' => $request->input('content_encoding', 'aes128gcm'),
            ]
        );

        return response()->json(['success' => true]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => ['required', 'url', 'max:500'],
        ]);

        $user = auth()->user();

        $user->pushSubscriptions()->where('endpoint', $request->endpoint)->delete();

        return response()->json(['success' => true]);
    }
}
