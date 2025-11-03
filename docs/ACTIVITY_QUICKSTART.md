# Activity System Quick Start

## Installation

The activity management system is now fully installed! Here's what was created:

### ✅ Database & Models
- `activities` table migration
- `Activity` model with relationships and scopes
- `LogsActivity` trait for automatic model tracking

### ✅ Services & Observers
- `ActivityLogger` service for manual logging
- `UserObserver` for tracking profile changes
- `AuthActivityListener` for login/logout events

### ✅ UI Components
- **Activity Dashboard** - `/app/activities/` (statistics & charts)
- **Activity Feed** - `/app/activities/feed/` (filterable timeline)
- **My Activities** - `/app/activities/my/` (personal activity log)

### ✅ Features
- Automatic tracking of user authentication events
- Profile update logging
- Custom activity logging
- Web push notifications for activities
- Advanced filtering and search
- Export and statistics API
- Automatic cleanup command

## Quick Usage

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Start Logging Activities

**Automatic logging for any model:**
```php
use App\Traits\LogsActivity;

class Post extends Model
{
    use LogsActivity;
}
```

**Manual logging:**
```php
use App\Services\ActivityLogger;

// Simple log
ActivityLogger::log('User downloaded report', $user);

// With metadata
ActivityLogger::log(
    'Order placed',
    $order,
    ['total' => 99.99, 'items' => 3],
    'orders',
    'created'
);
```

### 3. Access the UI
Navigate to:
- `/app/activities/` - View dashboard with statistics
- `/app/activities/feed/` - Browse all activities
- `/app/activities/my/` - See your personal timeline

### 4. Schedule Cleanup (Optional)
In `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('activities:clean --days=90')->weekly();
}
```

## What's Being Logged Automatically

✅ User login/logout
✅ Failed login attempts  
✅ Email verification
✅ Profile updates (name, email changes)

## Next Steps

1. Add `LogsActivity` trait to models you want to track
2. Customize activity descriptions as needed
3. Set up web push notifications for important activities
4. Configure cleanup schedule based on your needs

See `docs/ACTIVITY_SYSTEM_GUIDE.md` for detailed documentation!

