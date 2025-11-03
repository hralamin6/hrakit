# Quick Start Guide - PWA & Web Push Notifications

## 🚀 Quick Setup (5 minutes)

### Step 1: Generate VAPID Keys
```bash
php artisan webpush:vapid
```
Copy the output to your `.env` file:
```env
VAPID_PUBLIC_KEY=your_public_key_here
VAPID_PRIVATE_KEY=your_private_key_here
VAPID_SUBJECT=mailto:your-email@example.com
```

### Step 2: Run Migrations
```bash
php artisan migrate
```

### Step 3: Build Assets
```bash
npm run build
# or for development
npm run dev
```

### Step 4: Clear Cache
```bash
php artisan optimize:clear
```

## 📱 Usage

### Access Notification Management
Visit: `http://your-domain/app/notifications`

### Send Test Notification via UI
1. Go to `/app/notifications`
2. Click "Subscribe" and allow permissions
3. Enter title and message
4. Click "Send Test Notification"

### Send Notification via Command Line
```bash
php artisan push:send "Hello" "This is a test notification"

# Send to specific users
php artisan push:send "Hello" "Message" --user=1 --user=2

# With custom options
php artisan push:send "Alert" "Important message" \
  --url=https://example.com \
  --require-interaction \
  --tag=important
```

### Send Notification via Code
```php
use App\Notifications\WebPushNotification;

$user = auth()->user();
$notification = new WebPushNotification(
    title: 'Hello!',
    body: 'This is a notification'
);
$user->notify($notification);
```

### Using the Service
```php
use App\Services\PushNotificationService;

$service = app(PushNotificationService::class);

// Send to one user
$service->sendToUser($user, 'Title', 'Message');

// Send to all users
$service->sendToAll('Title', 'Message');

// Send to specific role
$service->sendToRole('admin', 'Title', 'Message');

// Get statistics
$stats = $service->getStats();
```

## 🎯 Features Implemented

✅ **PWA Features**
- Offline support with service worker
- App installation capability
- Background sync
- Advanced caching strategies
- Auto-updating service worker

✅ **Push Notifications**
- User subscription management
- Test notifications
- Custom icons and badges
- Interactive action buttons
- Notification grouping
- Permission management
- VAPID authentication

✅ **Advanced Features**
- Notification preferences per user
- CLI command for sending notifications
- Push notification service for easy integration
- Statistics and analytics
- Subscription cleanup utilities
- Event-based notifications
- Multi-user targeting

## 📊 API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/push/vapid-key` | GET | Get VAPID public key |
| `/api/push/subscribe` | POST | Subscribe to notifications |
| `/api/push/unsubscribe` | POST | Unsubscribe from notifications |
| `/api/push/status` | GET | Get subscription status |

## 🔧 JavaScript API

```javascript
// Subscribe to notifications
await window.pushManager.subscribe();

// Unsubscribe
await window.pushManager.unsubscribe();

// Check status
const subscription = await window.pushManager.checkSubscription();

// Request permission
await window.pushManager.requestPermission();
```

## 📝 Files Created/Modified

### New Files
- `app/Notifications/WebPushNotification.php` - Notification class
- `app/Livewire/App/Notifications.php` - UI component
- `app/Http/Controllers/PushSubscriptionController.php` - API controller
- `app/Services/PushNotificationService.php` - Service class
- `app/Console/Commands/SendPushNotification.php` - CLI command
- `app/Models/NotificationPreference.php` - Preference model
- `app/Events/UserNotificationEvent.php` - Broadcast event
- `resources/js/push-notifications.js` - Frontend manager
- `resources/views/livewire/app/notifications.blade.php` - UI view
- `database/migrations/*_create_notification_preferences_table.php`
- `docs/PWA_WEBPUSH_GUIDE.md` - Complete documentation

### Modified Files
- `public/sw.js` - Enhanced service worker
- `resources/js/app.js` - Added push module
- `routes/web.php` - Added routes
- `resources/views/layouts/app.blade.php` - Added menu item
- `app/Models/User.php` - Added notification preferences
- `app/Livewire/App/Setting.php` - Fixed branding save

## 🎨 Customization

### Change Notification Icon/Badge
Edit in `config/pwa.php` or use Settings page at `/app/settings`

### Modify Service Worker Cache
Edit `public/sw.js` and update `CACHE_NAME` and `filesToCache`

### Add Notification Categories
Create preferences in `NotificationPreference` model with different categories

## 🔒 Security Notes

- HTTPS required (except localhost)
- Keep VAPID private key secure
- Never expose private key to client
- Implement rate limiting for subscriptions

## 🐛 Troubleshooting

**Issue: Notifications not working**
- Check VAPID keys in `.env`
- Ensure HTTPS enabled
- Check browser console for errors
- Verify user is subscribed

**Issue: Service worker not updating**
- Clear browser cache
- Unregister old service workers
- Hard refresh (Ctrl+Shift+R)

**Issue: Permission denied**
- Check browser notification settings
- Clear site data and try again
- User may have previously blocked

## 📚 Documentation

Full documentation available at: `docs/PWA_WEBPUSH_GUIDE.md`

## 🎉 Next Steps

1. Set up VAPID keys in production
2. Customize notification icons/branding
3. Create notification categories
4. Implement notification scheduling
5. Add notification history tracking
6. Set up analytics for notification engagement

