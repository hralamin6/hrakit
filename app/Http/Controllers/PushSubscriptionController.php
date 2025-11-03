<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use NotificationChannels\WebPush\PushSubscription;

class PushSubscriptionController extends Controller
{
    /**
     * Subscribe user to push notifications
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|url',
            'keys.auth' => 'required|string',
            'keys.p256dh' => 'required|string',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Delete existing subscriptions with same endpoint
        PushSubscription::where('endpoint', $validated['endpoint'])->delete();

        $user->updatePushSubscription(
            $validated['endpoint'],
            $validated['keys']['p256dh'],
            $validated['keys']['auth']
        );

        return response()->json([
            'success' => true,
            'message' => 'Successfully subscribed to push notifications'
        ], 200);
    }

    /**
     * Unsubscribe user from push notifications
     */
    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|url',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user->pushSubscriptions()
            ->where('endpoint', $validated['endpoint'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully unsubscribed from push notifications'
        ], 200);
    }

    /**
     * Get subscription status
     */
    public function status(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['subscribed' => false]);
        }

        $subscriptionCount = $user->pushSubscriptions()->count();

        return response()->json([
            'subscribed' => $subscriptionCount > 0,
            'count' => $subscriptionCount
        ]);
    }

    /**
     * Get VAPID public key
     */
    public function vapidPublicKey()
    {
        return response()->json([
            'publicKey' => config('webpush.vapid.public_key')
        ]);
    }
}

