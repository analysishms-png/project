<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosActivity implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $propertyId;
    public $type; // 'new_bill', 'payment', 'kot', 'room_charge'
    public $billNo;
    public $outlet;
    public $amount;
    public $paymentMode;
    public $roomNo;
    public $itemCount;
    public $timestamp;

    public function __construct($data)
    {
        $this->propertyId = $data['propertyid'];
        $this->type = $data['type'] ?? 'new_bill';
        $this->billNo = $data['bill_no'] ?? '';
        $this->outlet = $data['outlet'] ?? '';
        $this->amount = $data['amount'] ?? 0;
        $this->paymentMode = $data['payment_mode'] ?? '';
        $this->roomNo = $data['room_no'] ?? '';
        $this->itemCount = $data['item_count'] ?? 0;
        $this->timestamp = now()->toISOString();
    }

    public function broadcastOn()
    {
        return new Channel('property.' . $this->propertyId . '.pos-activity');
    }

    public function broadcastAs()
    {
        return 'PosActivity';
    }

    public function broadcastWith()
    {
        return [
            'type' => $this->type,
            'bill_no' => $this->billNo,
            'outlet' => $this->outlet,
            'amount' => $this->amount,
            'payment_mode' => $this->paymentMode,
            'room_no' => $this->roomNo,
            'item_count' => $this->itemCount,
            'timestamp' => $this->timestamp,
        ];
    }
}
