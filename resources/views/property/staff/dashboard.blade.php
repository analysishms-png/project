@extends('property.layouts.property')

@section('content')
<style>
    .staff-app {
        max-width: 480px;
        margin: 0 auto;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    .sa-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #fff;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 16px;
        text-align: center;
    }
    .sa-header h4 { font-weight: 800; margin: 0 0 4px; font-size: 18px; }
    .sa-header small { opacity: 0.6; font-size: 12px; }
    .sa-staff-select {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        background: #fff;
        margin-bottom: 16px;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath d='M6 8L1 3h10z' fill='%2364748b'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 16px center;
    }
    .sa-clock {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 16px;
        text-align: center;
    }
    .sa-clock .time { font-size: 42px; font-weight: 800; color: #1e293b; font-variant-numeric: tabular-nums; }
    .sa-clock .date { font-size: 13px; color: #94a3b8; margin-top: 4px; }
    .sa-checkin-btn {
        width: 100%;
        padding: 16px;
        border: none;
        border-radius: 14px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        margin-top: 12px;
        transition: all 0.2s;
        color: #fff;
    }
    .sa-checkin-btn.checkin { background: linear-gradient(135deg, #10b981 0%, #34d399 100%); }
    .sa-checkin-btn.checkout { background: linear-gradient(135deg, #ef4444 0%, #f87171 100%); }
    .sa-checkin-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    .sa-checkin-btn:active { transform: scale(0.98); }
    .sa-status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        margin-top: 8px;
    }
    .sa-status-badge.checked-in { background: #dcfce7; color: #16a34a; }
    .sa-status-badge.checked-out { background: #fee2e2; color: #dc2626; }
    .sa-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 16px;
    }
    .sa-summary-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px;
        text-align: center;
    }
    .sa-summary-card .num { font-size: 28px; font-weight: 800; line-height: 1; }
    .sa-summary-card .label { font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }
    .sa-task-list { margin-bottom: 16px; }
    .sa-task-item {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.2s;
    }
    .sa-task-item:hover { border-color: #667eea; }
    .sa-task-icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .sa-task-icon.cleaning { background: #dbeafe; color: #2563eb; }
    .sa-task-icon.maintenance { background: #fef3c7; color: #d97706; }
    .sa-task-info { flex: 1; min-width: 0; }
    .sa-task-info .room { font-weight: 700; font-size: 14px; color: #1e293b; }
    .sa-task-info .type { font-size: 12px; color: #94a3b8; }
    .sa-task-status {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        flex-shrink: 0;
    }
    .sa-task-status.in-progress { background: #fef3c7; color: #d97706; }
    .sa-task-status.completed { background: #dcfce7; color: #16a34a; }
    .sa-task-status.pending { background: #f1f5f9; color: #64748b; }
    .sa-task-status.dirty { background: #fee2e2; color: #dc2626; }
    .sa-section-title {
        font-size: 13px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .sa-nav-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-bottom: 16px;
    }
    .sa-nav-btn {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px;
        text-align: center;
        text-decoration: none;
        color: #1e293b;
        transition: all 0.2s;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    .sa-nav-btn:hover { border-color: #667eea; background: rgba(102,126,234,0.04); color: #1e293b; }
    .sa-nav-btn i { font-size: 22px; color: #667eea; }
    .sa-nav-btn span { font-size: 12px; font-weight: 600; }
    .sa-checkin-log {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 16px;
    }
    .sa-checkin-log table { width: 100%; font-size: 12px; border-collapse: collapse; }
    .sa-checkin-log th { background: #f8fafc; padding: 10px 12px; font-weight: 700; text-align: left; color: #64748b; text-transform: uppercase; font-size: 10px; }
    .sa-checkin-log td { padding: 10px 12px; border-top: 1px solid #f1f5f9; }
    .sa-time-display { font-variant-numeric: tabular-nums; }
    @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.5; } }
    .sa-pulse { animation: pulse 2s infinite; }
</style>

<div class="staff-app">
    {{-- ═══ HEADER ═══ --}}
    <div class="sa-header">
        <h4><i class="fa-solid fa-broom" style="margin-right:6px;"></i> Staff Mobile App</h4>
        <small>Housekeeping & Maintenance Task Tracker</small>
    </div>

    {{-- ═══ STAFF SELECTOR ═══ --}}
    <select class="sa-staff-select" id="staffSelect" onchange="switchStaff(this.value)">
        <option value="">— Select Staff Member —</option>
        @foreach($staffList as $s)
            <option value="{{ $s->scode }}" {{ $staffId == $s->scode ? 'selected' : '' }}>
                {{ $s->name }} ({{ $s->scode }})
            </option>
        @endforeach
    </select>

    @if($staffId)
    {{-- ═══ CLOCK + CHECK-IN/OUT ═══ --}}
    <div class="sa-clock">
        <div class="time sa-time-display" id="liveClock">{{ date('h:i:s A') }}</div>
        <div class="date">{{ date('l, F d, Y') }}</div>
        <div id="checkinStatus">
            @php
                $activeCheckin = \Illuminate\Support\Facades\DB::table('staff_checkins')
                    ->where('propertyid', $staffId ? \Illuminate\Support\Facades\Auth::user()->propertyid : 0)
                    ->where('staff_id', $staffId)
                    ->where('check_date', date('Y-m-d'))
                    ->whereNull('check_out')
                    ->first();
            @endphp
            @if($activeCheckin)
                <span class="sa-status-badge checked-in"><i class="fa-solid fa-circle-check"></i> Checked In at {{ date('h:i A', strtotime($activeCheckin->check_in)) }}</span>
            @else
                <span class="sa-status-badge checked-out"><i class="fa-solid fa-circle-xmark"></i> Not Checked In</span>
            @endif
        </div>
        @if($activeCheckin)
            <button class="sa-checkin-btn checkout" onclick="doCheckout()">
                <i class="fa-solid fa-right-from-bracket"></i> Check Out
            </button>
        @else
            <button class="sa-checkin-btn checkin" onclick="doCheckin()">
                <i class="fa-solid fa-right-to-bracket"></i> Check In
            </button>
        @endif
    </div>

    {{-- ═══ TASK SUMMARY ═══ --}}
    @php
        $completed = $allCleaning['Completed'] ?? 0;
        $inProgress = $allCleaning['In Progress'] ?? 0;
        $dirty = $allCleaning['Dirty'] ?? 0;
        $total = $completed + $inProgress + $dirty;
    @endphp
    <div class="sa-summary-grid">
        <div class="sa-summary-card">
            <div class="num" style="color:#667eea;">{{ $total }}</div>
            <div class="label">Total Tasks</div>
        </div>
        <div class="sa-summary-card">
            <div class="num" style="color:#10b981;">{{ $completed }}</div>
            <div class="label">Completed</div>
        </div>
        <div class="sa-summary-card">
            <div class="num" style="color:#f59e0b;">{{ $inProgress }}</div>
            <div class="label">In Progress</div>
        </div>
    </div>

    {{-- ═══ QUICK NAVIGATION ═══ --}}
    <div class="sa-section-title"><i class="fa-solid fa-grid-2"></i> Quick Actions</div>
    <div class="sa-nav-grid">
        <a href="{{ route('staff.tasks', ['staff_id' => $staffId]) }}" class="sa-nav-btn">
            <i class="fa-solid fa-list-check"></i>
            <span>My Tasks</span>
        </a>
        <a href="{{ route('staff.productivity') }}" class="sa-nav-btn">
            <i class="fa-solid fa-chart-simple"></i>
            <span>Productivity</span>
        </a>
        <a href="{{ route('staff.task-log') }}" class="sa-nav-btn">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <span>Task Log</span>
        </a>
        <a href="{{ route('housekeepingscreen') }}" class="sa-nav-btn">
            <i class="fa-solid fa-broom"></i>
            <span>Housekeeping</span>
        </a>
    </div>

    {{-- ═══ TODAY'S TASKS ═══ --}}
    <div class="sa-section-title"><i class="fa-solid fa-clipboard-list"></i> My Today's Cleaning Tasks</div>
    <div class="sa-task-list">
        @forelse($cleaningTasks as $task)
            <a href="{{ route('staff.task-detail', ['taskId' => $task->cleaningid, 'taskType' => 'cleaning']) }}" class="sa-task-item" style="text-decoration:none;color:inherit;">
                <div class="sa-task-icon cleaning"><i class="fa-solid fa-bed"></i></div>
                <div class="sa-task-info">
                    <div class="room">Room {{ $task->roommo }}</div>
                    <div class="type">{{ $task->room_name ?? '' }} · {{ $task->cleantype ?? 'Standard' }}</div>
                </div>
                @php
                    $statusClass = match($task->cleaningstatus) {
                        'In Progress' => 'in-progress',
                        'Completed' => 'completed',
                        default => 'pending'
                    };
                @endphp
                <span class="sa-task-status {{ $statusClass }}">{{ $task->cleaningstatus }}</span>
            </a>
        @empty
            <div style="text-align:center;padding:30px;color:#94a3b8;">
                <i class="fa-solid fa-clipboard-check" style="font-size:32px;color:#e2e8f0;"></i>
                <p style="margin-top:8px;font-size:13px;">No tasks assigned today</p>
            </div>
        @endforelse
    </div>

    {{-- ═══ MAINTENANCE TASKS ═══ --}}
    @if($maintenanceTasks->count() > 0)
        <div class="sa-section-title"><i class="fa-solid fa-wrench" style="color:#f59e0b;"></i> Maintenance Tasks</div>
        <div class="sa-task-list">
            @foreach($maintenanceTasks as $mt)
                <a href="{{ route('staff.task-detail', ['taskId' => $mt->sn, 'taskType' => 'maintenance']) }}" class="sa-task-item" style="text-decoration:none;color:inherit;">
                    <div class="sa-task-icon maintenance"><i class="fa-solid fa-wrench"></i></div>
                    <div class="sa-task-info">
                        <div class="room">Room {{ $mt->roomno ?? 'N/A' }}</div>
                        <div class="type">{{ $mt->damage_type ?? 'Repair' }}</div>
                    </div>
                    <span class="sa-task-status in-progress">Pending</span>
                </a>
            @endforeach
        </div>
    @endif

    {{-- ═══ CHECK-IN LOG ═══ --}}
    @if($checkins->count() > 0)
        <div class="sa-section-title"><i class="fa-solid fa-user-clock"></i> Today's Check-ins</div>
        <div class="sa-checkin-log">
            <table>
                <thead><tr><th>Staff</th><th>In</th><th>Out</th><th>Hours</th></tr></thead>
                <tbody>
                @foreach($checkins as $ci)
                    <tr>
                        <td><strong>{{ $ci->staff_name }}</strong></td>
                        <td>{{ date('h:i A', strtotime($ci->check_in)) }}</td>
                        <td>{{ $ci->check_out ? date('h:i A', strtotime($ci->check_out)) : '—' }}</td>
                        <td>{{ $ci->check_out ? round((strtotime($ci->check_out) - strtotime($ci->check_in)) / 3600, 1) . 'h' : '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
    @else
        <div style="text-align:center;padding:60px 20px;color:#94a3b8;">
            <i class="fa-solid fa-user" style="font-size:48px;color:#e2e8f0;"></i>
            <h6 style="margin-top:16px;">Select a staff member to begin</h6>
            <p style="font-size:13px;">Choose from the dropdown above to view tasks and check-in.</p>
        </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
// Live clock
setInterval(function() {
    var now = new Date();
    var time = now.toLocaleTimeString('en-US', { hour12: true, hour: '2-digit', minute: '2-digit', second: '2-digit' });
    var el = document.getElementById('liveClock');
    if (el) el.textContent = time;
}, 1000);

function switchStaff(id) {
    if (id) window.location.href = '{{ route("staff.dashboard") }}?staff_id=' + id;
}

function getLocation(cb) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(p) {
            cb({ latitude: p.coords.latitude, longitude: p.coords.longitude });
        }, function() { cb({}); });
    } else { cb({}); }
}

function doCheckin() {
    var staffId = '{{ $staffId }}';
    if (!staffId) { alert('Select staff first'); return; }
    var staffName = document.getElementById('staffSelect').selectedOptions[0]?.text || '';
    getLocation(function(loc) {
        fetch('{{ route("staff.checkin") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ staff_id: staffId, staff_name: staffName, department: 'Housekeeping', latitude: loc.latitude, longitude: loc.longitude })
        })
        .then(r => r.json())
        .then(function(res) {
            if (res.success) { location.reload(); } else { alert(res.message); }
        });
    });
}

function doCheckout() {
    var staffId = '{{ $staffId }}';
    if (!staffId) return;
    getLocation(function(loc) {
        fetch('{{ route("staff.checkout") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ staff_id: staffId, latitude: loc.latitude, longitude: loc.longitude })
        })
        .then(r => r.json())
        .then(function(res) {
            if (res.success) { location.reload(); } else { alert(res.message); }
        });
    });
}
</script>
@endsection
