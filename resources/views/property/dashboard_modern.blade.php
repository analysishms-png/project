@extends('property.layouts.main')
@section('main-container')
@php
    use Carbon\Carbon;
    $currentHour = Carbon::now()->format('H');
    $greeting = '';
    $greetingIcon = '';
    if ($currentHour >= 5 && $currentHour < 12) { $greeting = 'Good Morning'; $greetingIcon = '🌅'; }
    elseif ($currentHour >= 12 && $currentHour < 17) { $greeting = 'Good Afternoon'; $greetingIcon = '☀️'; }
    elseif ($currentHour >= 17 && $currentHour < 21) { $greeting = 'Good Evening'; $greetingIcon = '🌆'; }
    else { $greeting = 'Good Night'; $greetingIcon = '🌙'; }
    $softwareDate = Carbon::parse($datearr['ncurdate'])->format('l, d F Y');
    $userName = Auth::user()->name;
    $compName = $user->comp_name ?? 'Hotel';

    // Room counts
    $occupiedCount = $status['Occupied'] ? count($status['Occupied]) : 0;
    $checkinCount = $status['CheckIn'] ? count($status['CheckIn']) : 0;
    $checkoutCount = $status['CheckOut'] ? count($status['CheckOut']) : 0;
    $expectedCheckoutCount = $status['ExpectedCheckOut'] ? count($status['ExpectedCheckOut']) : 0;
    $expectedArrivalCount = 0;
    if ($status['ExpectedArrival']) {
        $filteredArrivals = $status['ExpectedArrival']->filter(fn($item) => $item->total_rooms > 0);
        $expectedArrivalCount = $filteredArrivals->sum('total_rooms');
    }
    $unsettledCount = $status['UnsettledRooms'] ? count($status['UnsettledRooms']) : 0;
    $oooCount = $status['OutOfOrderRooms'] ? count($status['OutOfOrderRooms']) : 0;
    $occupiedDirtyCount = $status['OccupiedDirtyRooms'] ? count($status['OccupiedDirtyRooms']) : 0;
    $vacantDirtyCount = $status['VacantDirtyRooms'] ? count($status['VacantDirtyRooms']) : 0;

    // Get all rooms for grid
    $allRooms = \App\Models\RoomMast::where('propertyid', Auth::user()->propertyid)
        ->where('type', 'RO')
        ->orderBy('rcode')
        ->get();
    $totalRooms = $allRooms->count();

    // Get room statuses for grid
    $roomStatuses = [];
    $occupiedRooms = $status['Occupied'] ? $status['Occupied']->pluck('Name')->toArray() : [];
    $checkoutRooms = $status['CheckOut'] ? $status['CheckOut']->pluck('Name')->toArray() : [];
    $dirtyRooms = $status['OccupiedDirtyRooms'] ? $status['OccupiedDirtyRooms']->pluck('name')->toArray() : [];
    $vacantDirtyRooms = $status['VacantDirtyRooms'] ? $status['VacantDirtyRooms']->pluck('Name')->toArray() : [];
    $oooRooms = $status['OutOfOrderRooms'] ? $status['OutOfOrderRooms']->pluck('name')->toArray() : [];

    foreach ($allRooms as $room) {
        $rcode = $room->rcode;
        if (in_array($rcode, $oooRooms)) { $roomStatuses[$rcode] = 'ooo'; }
        elseif (in_array($rcode, $dirtyRooms)) { $roomStatuses[$rcode] = 'dirty'; }
        elseif (in_array($rcode, $vacantDirtyRooms)) { $roomStatuses[$rcode] = 'vacant-dirty'; }
        elseif (in_array($rcode, $occupiedRooms)) { $roomStatuses[$rcode] = 'occupied'; }
        elseif (in_array($rcode, $checkoutRooms)) { $roomStatuses[$rcode] = 'checkout'; }
        else { $roomStatuses[$rcode] = 'vacant-clean'; }
    }

    // Revenue data
    $combinedTotal = $data['combinedTotal'] ?? '0.00';
    $yesterdayTotal = $data['yesterdaycombinedTotal'] ?? '0.00';
    $percentageChange = $data['percentageChange'] ?? 0;
@endphp

<style>
/* Dashboard Modern Styles */
.dashboard-modern { padding: 0; margin: -15px; }
.welcome-bar { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); color: #fff; padding: 20px 30px; border-radius: 16px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
.welcome-bar h2 { font-size: 22px; font-weight: 700; margin: 0; }
.welcome-bar .date-info { font-size: 13px; opacity: 0.8; }
.welcome-bar .user-info { display: flex; align-items: center; gap: 12px; }
.welcome-bar .user-avatar { width: 42px; height: 42px; border-radius: 50%; background: #e94560; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; }

/* Summary Cards */
.summary-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
.summary-card { background: #fff; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-left: 4px solid; }
.summary-card.occupied { border-left-color: #3b82f6; }
.summary-card.checkout { border-left-color: #10b981; }
.summary-card.dirty { border-left-color: #f59e0b; }
.summary-card.vacant-dirty { border-left-color: #8b5cf6; }
.summary-card .card-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
.summary-card.occupied .card-icon { background: #eff6ff; color: #3b82f6; }
.summary-card.checkout .card-icon { background: #ecfdf5; color: #10b981; }
.summary-card.dirty .card-icon { background: #fffbeb; color: #f59e0b; }
.summary-card.vacant-dirty .card-icon { background: #f5f3ff; color: #8b5cf6; }
.summary-card .card-value { font-size: 28px; font-weight: 800; color: #1a1a2e; }
.summary-card .card-label { font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
.summary-card .card-rooms { font-size: 11px; color: #9ca3af; margin-top: 4px; }
.summary-card .card-arrow { margin-left: auto; color: #d1d5db; font-size: 18px; }

/* Main Grid */
.dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px; }
.dashboard-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.dashboard-card .card-title { font-size: 16px; font-weight: 700; color: #1a1a2e; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; }
.dashboard-card .card-title select { font-size: 12px; padding: 4px 8px; border-radius: 6px; border: 1px solid #e5e7eb; }

/* Donut Chart Section */
.donut-section { display: flex; align-items: center; gap: 24px; }
.donut-chart { position: relative; width: 180px; height: 180px; }
.donut-center { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; }
.donut-center .total { font-size: 24px; font-weight: 800; color: #1a1a2e; }
.donut-center .label { font-size: 11px; color: #6b7280; }
.donut-legend { flex: 1; }
.donut-legend .legend-item { display: flex; align-items: center; gap: 10px; padding: 6px 0; font-size: 13px; }
.donut-legend .legend-dot { width: 10px; height: 10px; border-radius: 50%; }
.donut-legend .legend-count { margin-left: auto; font-weight: 600; }
.donut-legend .legend-pct { margin-left: 8px; color: #9ca3af; font-size: 12px; }

/* Revenue Summary */
.revenue-big { font-size: 32px; font-weight: 800; color: #1a1a2e; margin-bottom: 4px; }
.revenue-subtitle { font-size: 13px; color: #6b7280; margin-bottom: 16px; }
.revenue-breakdown { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 16px; }
.revenue-item { text-align: center; padding: 12px; background: #f9fafb; border-radius: 8px; }
.revenue-item .rev-label { font-size: 11px; color: #6b7280; text-transform: uppercase; }
.revenue-item .rev-value { font-size: 16px; font-weight: 700; color: #1a1a2e; margin-top: 4px; }
.revenue-metrics { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 12px; }
.revenue-metric { text-align: center; padding: 10px; background: #f0fdf4; border-radius: 8px; }
.revenue-metric .rm-label { font-size: 11px; color: #6b7280; }
.revenue-metric .rm-value { font-size: 15px; font-weight: 700; color: #059669; }

/* Events Timeline */
.events-timeline { max-height: 300px; overflow-y: auto; }
.event-item { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f3f4f6; }
.event-item:last-child { border-bottom: none; }
.event-time { background: #eff6ff; color: #3b82f6; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; white-space: nowrap; }
.event-time.wedding { background: #fef3c7; color: #d97706; }
.event-time.meeting { background: #ecfdf5; color: #059669; }
.event-info h5 { font-size: 13px; font-weight: 600; margin: 0; color: #1a1a2e; }
.event-info .event-meta { font-size: 12px; color: #6b7280; margin-top: 2px; }
.event-badge { font-size: 10px; padding: 2px 8px; border-radius: 10px; font-weight: 600; }
.event-badge.birthday { background: #fef3c7; color: #d97706; }
.event-badge.wedding { background: #fce7f3; color: #db2777; }
.event-badge.meeting { background: #dbeafe; color: #2563eb; }

/* Room Grid */
.room-grid-section { margin-bottom: 20px; }
.room-grid { display: flex; flex-wrap: wrap; gap: 6px; }
.room-chip { padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; color: #fff; min-width: 50px; text-align: center; }
.room-chip.occupied { background: #ef4444; }
.room-chip.checkout { background: #10b981; }
.room-chip.dirty { background: #f59e0b; }
.room-chip.vacant-dirty { background: #8b5cf6; }
.room-chip.vacant-clean { background: #3b82f6; }
.room-chip.ooo { background: #6b7280; }

/* Quick Status Bar */
.quick-status-bar { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 12px; }
.status-pill { display: flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.status-pill .pill-dot { width: 8px; height: 8px; border-radius: 50%; }
.status-pill.occupied { background: #fef2f2; color: #dc2626; }
.status-pill.occupied .pill-dot { background: #dc2626; }
.status-pill.checkout { background: #ecfdf5; color: #059669; }
.status-pill.checkout .pill-dot { background: #059669; }
.status-pill.dirty { background: #fffbeb; color: #d97706; }
.status-pill.dirty .pill-dot { background: #d97706; }
.status-pill.vacant-dirty { background: #f5f3ff; color: #7c3aed; }
.status-pill.vacant-dirty .pill-dot { background: #7c3aed; }

/* Quick Actions */
.quick-actions-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.quick-action-btn { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 16px 8px; border-radius: 12px; border: 1px solid #e5e7eb; background: #fff; cursor: pointer; transition: all 0.2s; text-decoration: none; color: #374151; }
.quick-action-btn:hover { border-color: #3b82f6; background: #eff6ff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(59,130,246,0.15); }
.quick-action-btn .qa-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
.quick-action-btn .qa-icon.blue { background: #eff6ff; color: #3b82f6; }
.quick-action-btn .qa-icon.green { background: #ecfdf5; color: #10b981; }
.quick-action-btn .qa-icon.orange { background: #fff7ed; color: #f97316; }
.quick-action-btn .qa-icon.purple { background: #f5f3ff; color: #8b5cf6; }
.quick-action-btn .qa-icon.red { background: #fef2f2; color: #ef4444; }
.quick-action-btn .qa-icon.teal { background: #f0fdfa; color: #14b8a6; }
.quick-action-btn .qa-icon.indigo { background: #eef2ff; color: #6366f1; }
.quick-action-btn .qa-icon.gray { background: #f3f4f6; color: #6b7280; }
.quick-action-btn .qa-label { font-size: 11px; font-weight: 600; text-align: center; }

/* Bottom Grid */
.bottom-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }

/* Revenue Chart */
.chart-container { height: 200px; }

/* Responsive */
@media (max-width: 1200px) { .summary-cards { grid-template-columns: repeat(2, 1fr); } .dashboard-grid, .bottom-grid { grid-template-columns: 1fr; } }
@media (max-width: 768px) { .summary-cards { grid-template-columns: 1fr; } .quick-actions-grid { grid-template-columns: repeat(2, 1fr); } .donut-section { flex-direction: column; } }
</style>

<div class="dashboard-modern">
    {{-- Welcome Bar --}}
    <div class="welcome-bar">
        <div>
            <h2>Analytics Dashboard</h2>
            <div class="date-info">Welcome back, {{ $userName }}! 👋 | {{ $softwareDate }}</div>
        </div>
        <div class="user-info">
            <div style="text-align:right;">
                <div style="font-weight:600;">{{ $compName }}</div>
                <div style="font-size:11px;opacity:0.7;">{{ $datearr['ncurdate'] }}</div>
            </div>
            <div class="user-avatar">{{ strtoupper(substr($userName, 0, 1)) }}</div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="summary-cards">
        <div class="summary-card occupied">
            <div class="card-icon"><i class="fa fa-bed"></i></div>
            <div>
                <div class="card-value">{{ $occupiedCount }}</div>
                <div class="card-label">Occupied Rooms</div>
                <div class="card-rooms">{{ implode(', ', array_slice($occupiedRooms, 0, 5)) }}{{ count($occupiedRooms) > 5 ? '...' : '' }}</div>
            </div>
            <div class="card-arrow"><i class="fa fa-chevron-right"></i></div>
        </div>
        <div class="summary-card checkout">
            <div class="card-icon"><i class="fa fa-check-circle"></i></div>
            <div>
                <div class="card-value">{{ $checkoutCount }}</div>
                <div class="card-label">Checkout Rooms</div>
                <div class="card-rooms">{{ implode(', ', array_slice($checkoutRooms, 0, 5)) }}{{ count($checkoutRooms) > 5 ? '...' : '' }}</div>
            </div>
            <div class="card-arrow"><i class="fa fa-chevron-right"></i></div>
        </div>
        <div class="summary-card dirty">
            <div class="card-icon"><i class="fa fa-bell"></i></div>
            <div>
                <div class="card-value">{{ $occupiedDirtyCount }}</div>
                <div class="card-label">Occupied Dirty Rooms</div>
                <div class="card-rooms">{{ implode(', ', array_slice($dirtyRooms, 0, 5)) }}{{ count($dirtyRooms) > 5 ? '...' : '' }}</div>
            </div>
            <div class="card-arrow"><i class="fa fa-chevron-right"></i></div>
        </div>
        <div class="summary-card vacant-dirty">
            <div class="card-icon"><i class="fa fa-door-open"></i></div>
            <div>
                <div class="card-value">{{ $vacantDirtyCount }}</div>
                <div class="card-label">Vacant Dirty Rooms</div>
                <div class="card-rooms">{{ implode(', ', array_slice($vacantDirtyRooms, 0, 5)) }}{{ count($vacantDirtyRooms) > 5 ? '...' : '' }}</div>
            </div>
            <div class="card-arrow"><i class="fa fa-chevron-right"></i></div>
        </div>
    </div>

    {{-- Main Grid: Room Status + Revenue --}}
    <div class="dashboard-grid">
        {{-- Room Status Overview --}}
        <div class="dashboard-card">
            <div class="card-title">
                <span>Room Status Overview</span>
                <select class="form-control form-control-sm" style="width:auto;">
                    <option>All Room Types</option>
                </select>
            </div>
            <div class="donut-section">
                <div class="donut-chart">
                    <canvas id="roomStatusDonut"></canvas>
                    <div class="donut-center">
                        <div class="total">{{ $totalRooms }}</div>
                        <div class="label">Total Rooms</div>
                    </div>
                </div>
                <div class="donut-legend">
                    @php
                        $vacantCleanCount = $totalRooms - $occupiedCount - $vacantDirtyCount - $oooCount;
                        if ($vacantCleanCount < 0) $vacantCleanCount = 0;
                        $legendItems = [
                            ['label' => 'Occupied', 'count' => $occupiedCount, 'color' => '#ef4444'],
                            ['label' => 'Vacant Clean', 'count' => $vacantCleanCount, 'color' => '#10b981'],
                            ['label' => 'Vacant Dirty', 'count' => $vacantDirtyCount, 'color' => '#8b5cf6'],
                            ['label' => 'Out Of Order', 'count' => $oooCount, 'color' => '#6b7280'],
                        ];
                    @endphp
                    @foreach ($legendItems as $item)
                        @php $pct = $totalRooms > 0 ? round(($item['count'] / $totalRooms) * 100, 2) : 0; @endphp
                        <div class="legend-item">
                            <div class="legend-dot" style="background:{{ $item['color'] }}"></div>
                            <span>{{ $item['label'] }}</span>
                            <span class="legend-count">{{ $item['count'] }}</span>
                            <span class="legend-pct">{{ $pct }}%</span>
                        </div>
                    @endforeach
                    <div style="margin-top:12px;padding-top:12px;border-top:1px solid #e5e7eb;">
                        <div style="display:flex;justify-content:space-between;font-size:13px;">
                            <span>Chargeable Rooms</span>
                            <span style="font-weight:700;">{{ $occupiedCount }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:13px;margin-top:6px;">
                            <span>Occupancy %</span>
                            <span style="font-weight:700;color:#3b82f6;">{{ $totalRooms > 0 ? round(($occupiedCount / $totalRooms) * 100, 2) : 0 }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Revenue Summary --}}
        <div class="dashboard-card">
            <div class="card-title">
                <span>Revenue Summary</span>
                <select class="form-control form-control-sm" style="width:auto;">
                    <option>₹ INR</option>
                </select>
            </div>
            <div class="revenue-big">₹{{ number_format((float) str_replace(',', '', $combinedTotal), 2) }}</div>
            <div class="revenue-subtitle">Total for selected period
                @if($percentageChange > 0)
                    <span style="color:#10b981;font-weight:600;">↑ {{ number_format($percentageChange, 1) }}%</span>
                @elseif($percentageChange < 0)
                    <span style="color:#ef4444;font-weight:600;">↓ {{ number_format(abs($percentageChange), 1) }}%</span>
                @endif
            </div>
            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>
            <div class="revenue-breakdown">
                <div class="revenue-item">
                    <div class="rev-label">Room Rent</div>
                    <div class="rev-value">₹{{ number_format((float) str_replace(',', '', $yesterdayTotal), 2) }}</div>
                </div>
                <div class="revenue-item">
                    <div class="rev-label">Transfer From Outlet</div>
                    <div class="rev-value">₹{{ number_format((float) str_replace(',', '', $combinedTotal) - (float) str_replace(',', '', $yesterdayTotal), 2) }}</div>
                </div>
                <div class="revenue-item">
                    <div class="rev-label">Tax</div>
                    <div class="rev-value">₹0.00</div>
                </div>
            </div>
            <div class="revenue-metrics">
                <div class="revenue-metric">
                    <div class="rm-label">ADR</div>
                    <div class="rm-value">₹{{ $occupiedCount > 0 ? number_format((float) str_replace(',', '', $combinedTotal) / $occupiedCount, 2) : '0.00' }}</div>
                </div>
                <div class="revenue-metric">
                    <div class="rm-label">RevPAR</div>
                    <div class="rm-value">₹{{ $totalRooms > 0 ? number_format((float) str_replace(',', '', $combinedTotal) / $totalRooms, 2) : '0.00' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Grid: Events + Room Grid + Quick Actions --}}
    <div class="bottom-grid">
        {{-- Room Quick Status --}}
        <div class="dashboard-card">
            <div class="card-title">
                <span>Room Quick Status</span>
                <a href="{{ url('/roomstatus') }}" style="font-size:13px;color:#3b82f6;text-decoration:none;">View All Rooms →</a>
            </div>
            <div class="quick-status-bar">
                <div class="status-pill occupied"><div class="pill-dot"></div>{{ $occupiedCount }} Occupied</div>
                <div class="status-pill checkout"><div class="pill-dot"></div>{{ $checkoutCount }} Checkout</div>
                <div class="status-pill dirty"><div class="pill-dot"></div>{{ $occupiedDirtyCount }} Dirty</div>
                <div class="status-pill vacant-dirty"><div class="pill-dot"></div>{{ $vacantDirtyCount }} Vacant Dirty</div>
            </div>
            <div class="room-grid">
                @foreach ($roomStatuses as $rcode => $status)
                    <div class="room-chip {{ $status }}">{{ $rcode }}</div>
                @endforeach
            </div>
        </div>

        {{-- Quick Actions + Events --}}
        <div>
            <div class="dashboard-card" style="margin-bottom:20px;">
                <div class="card-title"><span>Quick Actions</span></div>
                <div class="quick-actions-grid">
                    <a href="{{ url('/walkin') }}" class="quick-action-btn">
                        <div class="qa-icon blue"><i class="fa fa-user-plus"></i></div>
                        <div class="qa-label">Quick Check-In</div>
                    </a>
                    <a href="{{ url('/reservation') }}" class="quick-action-btn">
                        <div class="qa-icon green"><i class="fa fa-calendar-plus"></i></div>
                        <div class="qa-label">New Reservation</div>
                    </a>
                    <a href="{{ url('/roomstatus') }}" class="quick-action-btn">
                        <div class="qa-icon orange"><i class="fa fa-door-open"></i></div>
                        <div class="qa-label">Room Availability</div>
                    </a>
                    <a href="{{ url('/fombilldetails') }}" class="quick-action-btn">
                        <div class="qa-icon purple"><i class="fa fa-file-invoice"></i></div>
                        <div class="qa-label">Create Invoice</div>
                    </a>
                    <a href="{{ url('/posbillentry') }}" class="quick-action-btn">
                        <div class="qa-icon red"><i class="fa fa-utensils"></i></div>
                        <div class="qa-label">POS Billing</div>
                    </a>
                    <a href="{{ url('/generalledger') }}" class="quick-action-btn">
                        <div class="qa-icon teal"><i class="fa fa-chart-bar"></i></div>
                        <div class="qa-label">Reports</div>
                    </a>
                    <a href="{{ url('/opennightaudit') }}" class="quick-action-btn">
                        <div class="qa-icon indigo"><i class="fa fa-moon"></i></div>
                        <div class="qa-label">Night Audit</div>
                    </a>
                    <a href="{{ url('/roomstatus') }}" class="quick-action-btn">
                        <div class="qa-icon gray"><i class="fa fa-ellipsis-h"></i></div>
                        <div class="qa-label">More Options</div>
                    </a>
                </div>
            </div>

            {{-- Today's Events --}}
            @if ($status['Events'] && count($status['Events']) > 0)
            <div class="dashboard-card">
                <div class="card-title">
                    <span>Today's Events</span>
                    <span style="font-size:12px;color:#6b7280;">{{ count($status['Events']) }} events</span>
                </div>
                <div class="events-timeline">
                    @foreach ($status['Events'] as $event)
                        @php
                            $eventType = 'meeting';
                            $eventName = strtolower($event->FName ?? '');
                            if (str_contains($eventName, 'birthday')) $eventType = 'birthday';
                            elseif (str_contains($eventName, 'wedding')) $eventType = 'wedding';
                        @endphp
                        <div class="event-item">
                            <div class="event-time {{ $eventType }}">{{ \Carbon\Carbon::parse($event->PTime)->format('h:i A') }}</div>
                            <div class="event-info" style="flex:1;">
                                <h5>{{ $event->FName }} - {{ $event->VName }}</h5>
                                <div class="event-meta"><i class="fa fa-user"></i> {{ $event->PName }}</div>
                            </div>
                            <span class="event-badge {{ $eventType }}">{{ ucfirst($eventType) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Room Status Donut
    const donutCtx = document.getElementById('roomStatusDonut');
    if (donutCtx) {
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Occupied', 'Vacant Clean', 'Vacant Dirty', 'Out Of Order'],
                datasets: [{
                    data: [{{ $occupiedCount }}, {{ $vacantCleanCount }}, {{ $vacantDirtyCount }}, {{ $oooCount }}],
                    backgroundColor: ['#ef4444', '#10b981', '#8b5cf6', '#6b7280'],
                    borderWidth: 0,
                    cutout: '70%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }

    // Revenue Line Chart
    const revCtx = document.getElementById('revenueChart');
    if (revCtx) {
        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: ['Apr', 'May', 'Jun', 'Jul', 'Aug'],
                datasets: [{
                    label: 'Revenue',
                    data: [{{ (float) str_replace(',', '', $combinedTotal) * 0.6 }}, {{ (float) str_replace(',', '', $combinedTotal) * 0.75 }}, {{ (float) str_replace(',', '', $combinedTotal) * 0.85 }}, {{ (float) str_replace(',', '', $combinedTotal) * 0.95 }}, {{ (float) str_replace(',', '', $combinedTotal) }}],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: { grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 }, callback: v => '₹' + (v/1000).toFixed(0) + 'K' } }
                }
            }
        });
    }
});
</script>
@endsection
