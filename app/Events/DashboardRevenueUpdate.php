<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DashboardRevenueUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $propertyId;
    public $todayRevenue;
    public $todayRoomRent;
    public $todayPosRevenue;
    public $todayPayments;
    public $totalOccupied;
    public $totalRevenue;
    public $adr;
    public $revpar;
    public $occupancyPct;
    public $timestamp;

    public function __construct($data)
    {
        $this->propertyId = $data['propertyid'];
        $this->todayRevenue = $data['today_revenue'] ?? 0;
        $this->todayRoomRent = $data['today_room_rent'] ?? 0;
        $this->todayPosRevenue = $data['today_pos_revenue'] ?? 0;
        $this->todayPayments = $data['today_payments'] ?? 0;
        $this->totalOccupied = $data['total_occupied'] ?? 0;
        $this->totalRevenue = $data['total_revenue'] ?? 0;
        $this->adr = $data['adr'] ?? 0;
        $this->revpar = $data['revpar'] ?? 0;
        $this->occupancyPct = $data['occupancy_pct'] ?? 0;
        $this->timestamp = now()->toISOString();
    }

    public function broadcastOn()
    {
        return new Channel('property.' . $this->propertyId . '.dashboard');
    }

    public function broadcastAs()
    {
        return 'DashboardRevenueUpdate';
    }

    public function broadcastWith()
    {
        return [
            'today_revenue' => $this->todayRevenue,
            'today_room_rent' => $this->todayRoomRent,
            'today_pos_revenue' => $this->todayPosRevenue,
            'today_payments' => $this->todayPayments,
            'total_occupied' => $this->totalOccupied,
            'total_revenue' => $this->totalRevenue,
            'adr' => $this->adr,
            'revpar' => $this->revpar,
            'occupancy_pct' => $this->occupancyPct,
            'timestamp' => $this->timestamp,
        ];
    }
}
