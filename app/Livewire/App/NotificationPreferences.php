<?php

namespace App\Livewire\App;

use App\Models\NotificationPreference;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Title('Notification Preferences')]
#[Layout('layouts.app')]
class NotificationPreferences extends Component
{
    use Toast;

    public array $preferences = [];

    public array $categories = [
        'general' => [
            'name' => 'General',
            'description' => 'General notifications and updates',
        ],
        'welcome' => [
            'name' => 'Welcome',
            'description' => 'Welcome messages and onboarding',
        ],
        'mentions' => [
            'name' => 'Mentions',
            'description' => 'When someone mentions you',
        ],
        'system' => [
            'name' => 'System Alerts',
            'description' => 'System updates and important alerts',
        ],
        'messages' => [
            'name' => 'Messages',
            'description' => 'Direct messages and conversations',
        ],
        'updates' => [
            'name' => 'Updates',
            'description' => 'Product updates and announcements',
        ],
    ];

    public function mount(): void
    {
        $this->loadPreferences();
    }

    public function loadPreferences(): void
    {
        $user = Auth::user();

        foreach ($this->categories as $category => $details) {
            $preference = $user->notificationPreferences()
                ->where('category', $category)
                ->first();

            if (!$preference) {
                $preference = $user->notificationPreferences()->create([
                    'category' => $category,
                ]);
            }

            $this->preferences[$category] = [
                'push_enabled' => $preference->push_enabled,
                'email_enabled' => $preference->email_enabled,
                'database_enabled' => $preference->database_enabled,
            ];
        }
    }

    public function save(): void
    {
        $user = Auth::user();

        foreach ($this->preferences as $category => $settings) {
            $user->notificationPreferences()->updateOrCreate(
                ['category' => $category],
                [
                    'push_enabled' => $settings['push_enabled'] ?? true,
                    'email_enabled' => $settings['email_enabled'] ?? true,
                    'database_enabled' => $settings['database_enabled'] ?? true,
                ]
            );
        }

        $this->success('Notification preferences saved successfully!', position: 'toast-bottom');
    }

    public function enableAll(): void
    {
        foreach ($this->preferences as $category => $settings) {
            $this->preferences[$category] = [
                'push_enabled' => true,
                'email_enabled' => true,
                'database_enabled' => true,
            ];
        }

        $this->save();
    }

    public function disableAll(): void
    {
        foreach ($this->preferences as $category => $settings) {
            $this->preferences[$category] = [
                'push_enabled' => false,
                'email_enabled' => false,
                'database_enabled' => false,
            ];
        }

        $this->save();
    }

    public function render()
    {
        return view('livewire.app.notification-preferences');
    }
}

