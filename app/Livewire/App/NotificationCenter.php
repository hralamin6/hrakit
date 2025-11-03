<?php

namespace App\Livewire\App;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Title('Notification Center')]
#[Layout('layouts.app')]
class NotificationCenter extends Component
{
    use Toast, WithPagination;

    public $selectedTab = 'all';
    public $unreadCount = 0;

    public function mount(): void
    {
        $this->updateUnreadCount();
    }

    public function updateUnreadCount(): void
    {
        $this->unreadCount = Auth::user()->unreadNotifications()->count();
    }

    public function markAsRead($notificationId): void
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            $this->updateUnreadCount();
            $this->success('Notification marked as read', position: 'toast-bottom');
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->updateUnreadCount();
        $this->success('All notifications marked as read', position: 'toast-bottom');
    }

    public function deleteNotification($notificationId): void
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->delete();
            $this->updateUnreadCount();
            $this->success('Notification deleted', position: 'toast-bottom');
        }
    }

    public function deleteAll(): void
    {
        Auth::user()->notifications()->delete();
        $this->updateUnreadCount();
        $this->success('All notifications deleted', position: 'toast-bottom');
    }

    public function getNotifications()
    {
        $query = Auth::user()->notifications();

        if ($this->selectedTab === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->selectedTab === 'read') {
            $query->whereNotNull('read_at');
        }

        return $query->latest()->paginate(10);
    }

    public function render()
    {
        return view('livewire.app.notification-center', [
            'notifications' => $this->getNotifications(),
        ]);
    }
}

