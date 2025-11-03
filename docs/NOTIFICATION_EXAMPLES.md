# Notification System Examples

## Example 1: E-commerce Order Notifications

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class OrderPlacedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        $preference = $notifiable->getNotificationPreference('orders');

        if ($preference->email_enabled) {
            $channels[] = 'mail';
        }

        if ($preference->push_enabled && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order #' . $this->order->id . ' Confirmed')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Thank you for your order!')
            ->line('Order Number: ' . $this->order->id)
            ->line('Total: $' . number_format($this->order->total, 2))
            ->line('Items: ' . $this->order->items->count())
            ->action('View Order', route('orders.show', $this->order->id))
            ->line('We will notify you when your order ships.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Order Confirmed',
            'message' => 'Your order #' . $this->order->id . ' has been confirmed. Total: $' . number_format($this->order->total, 2),
            'action_url' => route('orders.show', $this->order->id),
            'action_text' => 'View Order',
            'icon' => 'o-shopping-bag',
            'type' => 'success',
            'category' => 'orders',
            'data' => [
                'order_id' => $this->order->id,
                'total' => $this->order->total,
            ],
        ];
    }

    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title('Order Confirmed!')
            ->body('Order #' . $this->order->id . ' - $' . number_format($this->order->total, 2))
            ->icon(asset('logo.png'))
            ->badge(asset('logo.png'))
            ->data(['url' => route('orders.show', $this->order->id)])
            ->action('View Order', 'view-order', asset('logo.png'))
            ->tag('order-' . $this->order->id);
    }
}
```

## Example 2: Social Media Post Engagement

```php
// When someone likes your post
class PostLikedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $liker;
    protected $post;

    public function __construct($liker, $post)
    {
        $this->liker = $liker;
        $this->post = $post;
    }

    public function via(object $notifiable): array
    {
        $preference = $notifiable->getNotificationPreference('social');
        
        $channels = [];
        if ($preference->database_enabled) {
            $channels[] = 'database';
        }
        if ($preference->push_enabled && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }
        
        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->liker->name . ' liked your post',
            'message' => \Str::limit($this->post->content, 100),
            'action_url' => route('posts.show', $this->post->id),
            'action_text' => 'View Post',
            'icon' => 'o-heart',
            'type' => 'info',
            'category' => 'social',
        ];
    }

    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title($this->liker->name . ' liked your post')
            ->body(\Str::limit($this->post->content, 100))
            ->icon(asset('logo.png'))
            ->data(['url' => route('posts.show', $this->post->id)])
            ->tag('post-like-' . $this->post->id);
    }
}
```

## Example 3: Task Management System

```php
// Task assigned to user
class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;
    protected $assignedBy;

    public function __construct($task, $assignedBy)
    {
        $this->task = $task;
        $this->assignedBy = $assignedBy;
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        $preference = $notifiable->getNotificationPreference('tasks');

        if ($preference->email_enabled) {
            $channels[] = 'mail';
        }

        if ($preference->push_enabled && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Task Assigned: ' . $this->task->title)
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line($this->assignedBy->name . ' assigned you a new task.')
            ->line('**Task:** ' . $this->task->title)
            ->line('**Due Date:** ' . $this->task->due_date->format('M d, Y'))
            ->line('**Priority:** ' . ucfirst($this->task->priority))
            ->action('View Task', route('tasks.show', $this->task->id))
            ->line('Good luck!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Task Assigned',
            'message' => $this->assignedBy->name . ' assigned you: ' . $this->task->title,
            'action_url' => route('tasks.show', $this->task->id),
            'action_text' => 'View Task',
            'icon' => 'o-clipboard-document-list',
            'type' => $this->task->priority === 'high' ? 'warning' : 'info',
            'category' => 'tasks',
            'data' => [
                'task_id' => $this->task->id,
                'due_date' => $this->task->due_date,
                'priority' => $this->task->priority,
            ],
        ];
    }

    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title('New Task: ' . $this->task->title)
            ->body('Assigned by ' . $this->assignedBy->name . ' - Due: ' . $this->task->due_date->format('M d'))
            ->icon(asset('logo.png'))
            ->data(['url' => route('tasks.show', $this->task->id)])
            ->options(['requireInteraction' => $this->task->priority === 'high'])
            ->tag('task-' . $this->task->id);
    }
}
```

## Example 4: Real-time Event Broadcasting

```php
// In your event listener or controller
use App\Events\UserNotificationEvent;
use App\Notifications\SystemAlertNotification;

class NotificationController extends Controller
{
    public function broadcastAnnouncement(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error',
            'url' => 'nullable|url',
        ]);

        $users = User::all();

        $notification = new SystemAlertNotification(
            $validated['title'],
            $validated['message'],
            $validated['type'],
            $validated['url'] ?? null
        );

        // Send via notification system
        \Notification::send($users, $notification);

        // Also broadcast in real-time if using Pusher/Laravel Echo
        foreach ($users as $user) {
            event(new UserNotificationEvent(
                $user->id,
                $validated['title'],
                $validated['message'],
                ['url' => $validated['url'] ?? null]
            ));
        }

        return response()->json(['success' => true]);
    }
}
```

## Example 5: Scheduled Daily Digest

```php
// Create a scheduled command
class SendDailyDigest extends Command
{
    protected $signature = 'digest:send';
    protected $description = 'Send daily notification digest to users';

    public function handle()
    {
        $users = User::whereHas('notificationPreferences', function ($q) {
            $q->where('category', 'digest')->where('email_enabled', true);
        })->get();

        foreach ($users as $user) {
            $unreadNotifications = $user->unreadNotifications()
                ->whereDate('created_at', '>=', now()->subDay())
                ->get();

            if ($unreadNotifications->count() > 0) {
                $user->notify(new DailyDigestNotification($unreadNotifications));
            }
        }

        $this->info('Daily digest sent to ' . $users->count() . ' users');
    }
}

// Register in app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('digest:send')->daily();
}
```

## Example 6: Using Service for Complex Logic

```php
use App\Services\PushNotificationService;

class PostController extends Controller
{
    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    public function publish(Post $post)
    {
        $post->published = true;
        $post->save();

        // Notify all followers
        $followers = $post->author->followers;
        
        $this->pushService->sendToUsers(
            $followers,
            'New Post Published',
            $post->author->name . ' published: ' . $post->title,
            [
                'icon' => $post->author->avatar_url,
                'data' => ['url' => route('posts.show', $post->id)],
                'tag' => 'new-post-' . $post->id,
            ]
        );

        return redirect()->route('posts.show', $post);
    }
}
```

## Example 7: API Integration

```javascript
// Frontend JavaScript to send notification via API
async function sendCustomNotification() {
    const response = await fetch('/api/notifications/send-system-alert', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            user_id: 1,
            title: 'Custom Alert',
            message: 'This is sent from JavaScript!',
            type: 'info',
            action_url: window.location.href,
            action_text: 'View Page'
        })
    });

    const result = await response.json();
    console.log(result);
}
```

## Example 8: Webhook Integration

```php
// Receive webhook and send notification
class WebhookController extends Controller
{
    public function handlePaymentWebhook(Request $request)
    {
        $payment = $request->input('payment');
        
        $user = User::find($payment['user_id']);
        
        if ($payment['status'] === 'success') {
            $user->notify(new SystemAlertNotification(
                'Payment Successful',
                'Your payment of $' . $payment['amount'] . ' was processed successfully.',
                'success',
                route('payments.show', $payment['id'])
            ));
        } else {
            $user->notify(new SystemAlertNotification(
                'Payment Failed',
                'Your payment failed. Please try again or contact support.',
                'error',
                route('payments.retry', $payment['id']),
                'Retry Payment'
            ));
        }

        return response()->json(['received' => true]);
    }
}
```

