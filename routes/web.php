<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Passwords\Confirm;
use App\Livewire\Auth\Passwords\Email;
use App\Livewire\Auth\Passwords\Reset;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\Verify;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::livewire('/', 'web.home')->name('web.home');
Route::get('/mary', \App\Livewire\Welcome::class);

//Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', Login::class)
        ->name('login');

    Route::get('register', Register::class)
        ->name('register');

    Route::get('auth/{provider}/redirect', [\App\Http\Controllers\SocialiteController::class, 'loginSocial'])->name('socialite.auth');
    Route::get('auth/{provider}/callback', [\App\Http\Controllers\SocialiteController::class, 'callbackSocial'])->name('socialite.callback');

});

Route::get('password/reset', Email::class)
    ->name('password.request');

Route::get('password/reset/{token}', Reset::class)
    ->name('password.reset');

Route::middleware('auth')->group(function () {
    Route::get('email/verify', Verify::class)
        ->middleware('throttle:6,1')
        ->name('verification.notice');

    Route::get('password/confirm', Confirm::class)
        ->name('password.confirm');
});

Route::middleware(['auth', 'verified'])->group(function () {
  Route::livewire('/app/', 'app.dashboard')->name('app.dashboard');
  Route::livewire('/app/profile/', 'app.profile')->name('app.profile');
  Route::livewire('/app/settings/', 'app.setting')->name('app.settings');
  Route::livewire('/app/notifications/', 'app.notifications')->name('app.notifications');
  Route::livewire('/app/notification-center/', 'app.notification-center')->name('app.notification-center');
  Route::livewire('/app/notification-preferences/', 'app.notification-preferences')->name('app.notification-preferences');
  Route::livewire('/app/roles/', 'app.role')->name('app.roles');
  Route::livewire('/app/users/', 'app.user')->name('app.users');

  // Activity Management Routes
  Route::get('/app/activities/', \App\Livewire\App\ActivityDashboard::class)->name('app.activity.dashboard');
  Route::get('/app/activities/feed/', \App\Livewire\App\ActivityFeed::class)->name('app.activity.feed');
  Route::get('/app/activities/my/', \App\Livewire\App\MyActivities::class)->name('app.activity.my');
  Route::get('/app/activities/clear/', \App\Livewire\App\ActivityClear::class)->name('app.activity.clear');

  // Activity API routes
  Route::prefix('api/activities')->group(function () {
    Route::get('export', [\App\Http\Controllers\ActivityController::class, 'export'])->name('activity.export');
    Route::get('stats', [\App\Http\Controllers\ActivityController::class, 'stats'])->name('activity.stats');
    Route::post('{activity}/notify-admins', [\App\Http\Controllers\ActivityController::class, 'notifyAdmins'])->name('activity.notify-admins');
    Route::post('clear', [\App\Http\Controllers\ActivityController::class, 'clear'])->name('activity.clear.api');
  });
});

Route::middleware('auth')->group(function () {
  // Push notification API routes
  Route::post('api/push/subscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
  Route::get('api/push/status', [\App\Http\Controllers\PushSubscriptionController::class, 'status'])->name('push.status');
  Route::get('api/push/status', [\App\Http\Controllers\PushSubscriptionController::class, 'status'])->name('push.status');

  // Notification API routes
  Route::prefix('api/notifications')->group(function () {
    Route::get('unread-count', [\App\Http\Controllers\NotificationExampleController::class, 'getUnreadCount']);
    Route::post('{id}/mark-read', [\App\Http\Controllers\NotificationExampleController::class, 'markAsRead']);
  Route::post('api/push/subscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
  });

  Route::get('email/verify/{id}/{hash}', \App\Http\Controllers\Auth\EmailVerificationController::class)
    ->middleware('signed')
    ->name('verification.verify');

  Route::post('logout', \App\Http\Controllers\Auth\LogoutController::class)
    ->name('logout');
});

// Public VAPID key endpoint
Route::get('api/push/vapid-key', [\App\Http\Controllers\PushSubscriptionController::class, 'vapidPublicKey'])->name('push.vapid-key');
