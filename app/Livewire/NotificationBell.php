<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\UserNotification;

class NotificationBell extends Component
{
    public $notifications = [];
    public $unreadCount = 0;
    public $showDropdown = false;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $this->notifications = UserNotification::forUser(auth()->id())
            ->unread()
            ->latest()
            ->take(5)
            ->get();

        $this->unreadCount = $this->notifications->count();
    }

    public function markAsRead($notificationId)
    {
        $notification = UserNotification::find($notificationId);
        if ($notification && $notification->user_id === auth()->id()) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

    public function markAllAsRead()
    {
        UserNotification::forUser(auth()->id())
            ->unread()
            ->each(fn($n) => $n->markAsRead());

        $this->loadNotifications();
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
    }

    // Escuchar eventos de notificaciones en tiempo real
    protected function getListeners()
    {
        return [
            "echo-private:user." . auth()->id() . ",notification.new" => 'handleNewNotification',
        ];
    }

    public function handleNewNotification($data)
    {
        $this->loadNotifications();

        // Mostrar notificación del navegador si tiene permisos
        $this->dispatch('show-browser-notification',
            title: $data['title'],
            message: $data['message']
        );
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
