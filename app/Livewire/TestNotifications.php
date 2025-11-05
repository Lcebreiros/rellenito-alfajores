<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\UserNotification;
use App\Events\NewNotification;

class TestNotifications extends Component
{
    public $testMessage = '';

    public function sendTestNotification()
    {
        $notification = UserNotification::create([
            'user_id' => auth()->id(),
            'type' => 'test',
            'title' => 'Prueba de Pusher',
            'message' => $this->testMessage ?: 'Esta es una notificación de prueba en tiempo real!',
        ]);

        // Broadcast en tiempo real
        broadcast(new NewNotification($notification))->toOthers();

        $this->testMessage = '';

        session()->flash('success', 'Notificación enviada! Deberías verla aparecer en tiempo real.');
    }

    public function render()
    {
        return view('livewire.test-notifications');
    }
}
