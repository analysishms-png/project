<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DashboardNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $propertyId;
    public $title;
    public $message;
    public $type; // 'info', 'success', 'warning', 'danger'
    public $icon;
    public $actionUrl;
    public $timestamp;

    public function __construct($data)
    {
        $this->propertyId = $data['propertyid'];
        $this->title = $data['title'] ?? 'Notification';
        $this->message = $data['message'] ?? '';
        $this->type = $data['type'] ?? 'info';
        $this->icon = $data['icon'] ?? 'ri-information-line';
        $this->actionUrl = $data['action_url'] ?? null;
        $this->timestamp = now()->toISOString();
    }

    public function broadcastOn()
    {
        return new Channel('property.' . $this->propertyId . '.notifications');
    }

    public function broadcastAs()
    {
        return 'DashboardNotification';
    }

    public function broadcastWith()
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'icon' => $this->icon,
            'action_url' => $this->actionUrl,
            'timestamp' => $this->timestamp,
        ];
    }
}
