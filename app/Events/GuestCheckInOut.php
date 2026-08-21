<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GuestCheckInOut implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $propertyId;
    public $type; // 'checkin' or 'checkout'
    public $docId;
    public $guestName;
    public $roomNo;
    public $roomCat;
    public $arrivalDate;
    public $departureDate;
    public $billAmount;
    public $paymentMode;
    public $timestamp;

    public function __construct($data)
    {
        $this->propertyId = $data['propertyid'];
        $this->type = $data['type'] ?? 'checkin';
        $this->docId = $data['docid'] ?? '';
        $this->guestName = $data['guest_name'] ?? '';
        $this->roomNo = $data['room_no'] ?? '';
        $this->roomCat = $data['room_cat'] ?? '';
        $this->arrivalDate = $data['arrival_date'] ?? '';
        $this->departureDate = $data['departure_date'] ?? '';
        $this->billAmount = $data['bill_amount'] ?? 0;
        $this->paymentMode = $data['payment_mode'] ?? '';
        $this->timestamp = now()->toISOString();
    }

    public function broadcastOn()
    {
        return new Channel('property.' . $this->propertyId . '.guest-activity');
    }

    public function broadcastAs()
    {
        return 'GuestCheckInOut';
    }

    public function broadcastWith()
    {
        return [
            'type' => $this->type,
            'doc_id' => $this->docId,
            'guest_name' => $this->guestName,
            'room_no' => $this->roomNo,
            'room_cat' => $this->roomCat,
            'arrival_date' => $this->arrivalDate,
            'departure_date' => $this->departureDate,
            'bill_amount' => $this->billAmount,
            'payment_mode' => $this->paymentMode,
            'timestamp' => $this->timestamp,
        ];
    }
}
