<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $propertyId;
    public $roomNo;
    public $status;
    public $previousStatus;
    public $guestName;
    public $roomCat;
    public $roomRate;
    public $timestamp;
    public $occupiedCount;
    public $vacantCleanCount;
    public $vacantDirtyCount;
    public $outOfOrderCount;

    public function __construct($data)
    {
        $this->propertyId = $data['propertyid'];
        $this->roomNo = $data['roomno'] ?? '';
        $this->status = $data['status'] ?? 'unknown';
        $this->previousStatus = $data['previous_status'] ?? '';
        $this->guestName = $data['guest_name'] ?? '';
        $this->roomCat = $data['room_cat'] ?? '';
        $this->roomRate = $data['room_rate'] ?? 0;
        $this->timestamp = now()->toISOString();
        $this->occupiedCount = $data['occupied_count'] ?? 0;
        $this->vacantCleanCount = $data['vacant_clean_count'] ?? 0;
        $this->vacantDirtyCount = $data['vacant_dirty_count'] ?? 0;
        $this->outOfOrderCount = $data['out_of_order_count'] ?? 0;
    }

    public function broadcastOn()
    {
        return new Channel('property.' . $this->propertyId . '.room-status');
    }

    public function broadcastAs()
    {
        return 'RoomStatusChanged';
    }

    public function broadcastWith()
    {
        return [
            'room_no' => $this->roomNo,
            'status' => $this->status,
            'previous_status' => $this->previousStatus,
            'guest_name' => $this->guestName,
            'room_cat' => $this->roomCat,
            'room_rate' => $this->roomRate,
            'timestamp' => $this->timestamp,
            'counts' => [
                'occupied' => $this->occupiedCount,
                'vacant_clean' => $this->vacantCleanCount,
                'vacant_dirty' => $this->vacantDirtyCount,
                'out_of_order' => $this->outOfOrderCount,
            ],
        ];
    }
}
