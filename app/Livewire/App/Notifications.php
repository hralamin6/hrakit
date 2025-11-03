<?php

namespace App\Livewire\App;

use App\Notifications\SimplePushNotification;
use App\Notifications\WebPushNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Push Notifications')]
#[Layout('layouts.app')]
class Notifications extends Component
{
    use Toast;

    public string $testTitle = 'Test Notification';
    public string $testBody = 'This is a test push notification!';

    public function sendTest()
    {
        $user = Auth::user();

        if (!$user->pushSubscriptions()->exists()) {
            $this->error('Please subscribe to push notifications first!');
            return;
        }

        try {
            $user->notify(new WebPushNotification(
                $this->testTitle,
                $this->testBody,
                asset('logo.png'), // $icon
                asset('logo.png'), // $badge
                [ // $data
                    'url' => route('app.dashboard'),
                    'meta' => ['source' => 'test'],
                ],
                [ // $actions
                    ['action' => 'open-dashboard', 'title' => 'Open Dashboard', 'icon' => asset('favicon.ico')],
                    ['action' => 'dismiss', 'title' => 'Dismiss', 'icon' => asset('logo.png')],
                ],
                'test-notification', // $tag
                true // $requireInteraction
            ));

            $this->success('✅ Test notification sent! Check your browser.');
            $this->dispatch('test-sent');
        } catch (\Exception $e) {
            $this->error('Failed to send: ' . $e->getMessage());
        }
    }

    public function render()
    {
      \auth()->user()->notify(new WelcomeNotification(\auth()->user()->name));

      return view('livewire.app.notifications');
    }
}

