<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\UserNotification;

class AllNotifications extends Component
{
    use WithPagination;

    public array $selected = [];
    public bool $selectAll = false;
    public string $filter = 'all'; // all, read, unread

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->getNotifications()->pluck('id')->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function toggleSelection($notificationId)
    {
        if (in_array($notificationId, $this->selected)) {
            $this->selected = array_values(array_diff($this->selected, [$notificationId]));
        } else {
            $this->selected[] = $notificationId;
        }

        $this->selectAll = count($this->selected) === $this->getNotifications()->count();
    }

    public function markSelectedAsRead()
    {
        if (empty($this->selected)) {
            return;
        }

        UserNotification::whereIn('id', $this->selected)
            ->where('user_id', auth()->id())
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $this->selected = [];
        $this->selectAll = false;
        $this->dispatch('notifications-updated');
        session()->flash('success', 'Notificaciones marcadas como leídas');
    }

    public function markAllAsRead()
    {
        UserNotification::forUser(auth()->id())
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $this->selected = [];
        $this->selectAll = false;
        $this->dispatch('notifications-updated');
        session()->flash('success', 'Todas las notificaciones marcadas como leídas');
    }

    public function deleteSelected()
    {
        if (empty($this->selected)) {
            return;
        }

        UserNotification::whereIn('id', $this->selected)
            ->where('user_id', auth()->id())
            ->delete();

        $this->selected = [];
        $this->selectAll = false;
        $this->dispatch('notifications-updated');
        session()->flash('success', 'Notificaciones eliminadas');
    }

    public function deleteAll()
    {
        $query = UserNotification::forUser(auth()->id());

        if ($this->filter === 'read') {
            $query->where('is_read', true);
        } elseif ($this->filter === 'unread') {
            $query->unread();
        }

        $query->delete();

        $this->selected = [];
        $this->selectAll = false;
        $this->dispatch('notifications-updated');
        session()->flash('success', 'Todas las notificaciones eliminadas');
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->selected = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function markAsRead($notificationId)
    {
        $notification = UserNotification::find($notificationId);
        if ($notification && $notification->user_id === auth()->id()) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
            $this->dispatch('notifications-updated');
        }
    }

    private function getNotifications()
    {
        $query = UserNotification::forUser(auth()->id());

        if ($this->filter === 'read') {
            $query->where('is_read', true);
        } elseif ($this->filter === 'unread') {
            $query->unread();
        }

        return $query->latest()->get();
    }

    public function render()
    {
        $query = UserNotification::forUser(auth()->id());

        if ($this->filter === 'read') {
            $query->where('is_read', true);
        } elseif ($this->filter === 'unread') {
            $query->unread();
        }

        $notifications = $query->latest()->paginate(20);
        $unreadCount = UserNotification::forUser(auth()->id())->unread()->count();

        return view('livewire.all-notifications', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}
