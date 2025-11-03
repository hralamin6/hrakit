<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\SystemAlertNotification;
use App\Notifications\UserMentionedNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationExampleController extends Controller
{
    /**
     * Send a welcome notification to a user
     */
    public function sendWelcome(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->user_id);

        $user->notify(new WelcomeNotification($user->name));

        return response()->json([
            'success' => true,
            'message' => 'Welcome notification sent!',
        ]);
    }

    /**
     * Send a mention notification
     */
    public function sendMention(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'mentioned_by' => 'required|string',
            'content' => 'required|string',
            'url' => 'required|url',
        ]);

        $user = User::findOrFail($validated['user_id']);

        $user->notify(new UserMentionedNotification(
            $validated['mentioned_by'],
            $validated['content'],
            $validated['url']
        ));

        return response()->json([
            'success' => true,
            'message' => 'Mention notification sent!',
        ]);
    }

    /**
     * Send a system alert
     */
    public function sendSystemAlert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'title' => 'required|string',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,error,success',
            'action_url' => 'nullable|url',
            'action_text' => 'nullable|string',
            'broadcast' => 'boolean',
        ]);

        $notification = new SystemAlertNotification(
            $validated['title'],
            $validated['message'],
            $validated['type'],
            $validated['action_url'] ?? null,
            $validated['action_text'] ?? null
        );

        if ($validated['broadcast'] ?? false) {
            // Send to all users with push subscriptions
            $users = User::whereHas('pushSubscriptions')->get();
            \Notification::send($users, $notification);
            $count = $users->count();
        } else {
            $user = User::findOrFail($validated['user_id']);
            $user->notify($notification);
            $count = 1;
        }

        return response()->json([
            'success' => true,
            'message' => "System alert sent to {$count} user(s)!",
        ]);
    }

    /**
     * Get user's unread notification count
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        $count = $request->user()->unreadNotifications()->count();

        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }
}

