@extends('property.layouts.property')

@section('content')
<style>
    .dm-card { background: #fff; border: 1px solid #e8ecf1; border-radius: 14px; padding: 20px; margin-bottom: 16px; }
    .dm-card h6 { font-weight: 700; font-size: 13px; text-transform: uppercase; color: #64748b; margin-bottom: 12px; }
    .dm-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
    .dm-kpi { background: #fff; border: 1px solid #e8ecf1; border-radius: 12px; padding: 16px; text-align: center; }
    .dm-kpi .val { font-size: 28px; font-weight: 800; }
    .dm-kpi .lbl { font-size: 11px; color: #94a3b8; text-transform: uppercase; margin-top: 4px; }
    .dm-table { width: 100%; font-size: 12px; border-collapse: collapse; }
    .dm-table th { background: #f8fafc; padding: 10px 12px; font-weight: 700; text-transform: uppercase; font-size: 10px; color: #64748b; letter-spacing: 0.5px; }
    .dm-table td { padding: 10px 12px; border-top: 1px solid #f1f5f9; }
    .dm-table tr:hover td { background: #f8fafc; }
    .dm-status { display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 10px; font-size: 10px; font-weight: 700; }
    .dm-status.online { background: #dcfce7; color: #16a34a; }
    .dm-status.offline { background: #fee2e2; color: #dc2626; }
    .dm-add-form { display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 16px; }
    .dm-add-form.show { display: block; }
    .dm-add-form .form-control, .dm-add-form .form-select { border-radius: 8px; font-size: 13px; }
</style>

<div class="nk-block">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 style="font-weight:800;color:#1e293b;margin:0;"><i class="fa-solid fa-plug" style="color:#667eea;margin-right:8px;"></i> IoT Device Management</h4>
        <button class="btn btn-primary btn-sm" style="border-radius:8px;background:linear-gradient(135deg,#667eea,#764ba2);border:none;" onclick="document.getElementById('addForm').classList.toggle('show')">
            <i class="fa-solid fa-plus"></i> Add Device
        </button>
    </div>

    {{-- STATS --}}
    <div class="dm-kpi-grid">
        <div class="dm-kpi"><div class="val" style="color:#667eea;">{{ $stats->total ?? 0 }}</div><div class="lbl">Total Devices</div></div>
        <div class="dm-kpi"><div class="val" style="color:#10b981;">{{ $stats->online ?? 0 }}</div><div class="lbl">Online</div></div>
        <div class="dm-kpi"><div class="val" style="color:#ef4444;">{{ $stats->offline ?? 0 }}</div><div class="lbl">Offline</div></div>
        <div class="dm-kpi"><div class="val" style="color:#f59e0b;">{{ $stats->low_battery ?? 0 }}</div><div class="lbl">Low Battery</div></div>
    </div>

    {{-- ADD FORM --}}
    <div class="dm-add-form" id="addForm">
        <h6 style="margin-bottom:12px;"><i class="fa-solid fa-plus-circle" style="margin-right:4px;"></i> Add New Device</h6>
        <form id="deviceForm" onsubmit="submitDevice(event)">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label" style="font-size:12px;font-weight:600;">Room</label>
                    <select name="room_no" class="form-select" required>
                        <option value="">Select Room</option>
                        @foreach($rooms as $rm)<option value="{{ $rm }}">{{ $rm }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-size:12px;font-weight:600;">Device Type</label>
                    <select name="device_type" class="form-select" required>
                        <option value="light">Light</option><option value="ac">AC</option><option value="curtain">Curtain</option>
                        <option value="tv">TV</option><option value="sensor">Sensor</option><option value="lock">Lock</option>
                        <option value="thermostat">Thermostat</option><option value="speaker">Speaker</option>
                        <option value="camera">Camera</option><option value="doorbell">Doorbell</option>
                        <option value="motion">Motion</option><option value="power">Power</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-size:12px;font-weight:600;">Device Name</label>
                    <input type="text" name="device_name" class="form-control" placeholder="e.g. Ceiling Light" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-size:12px;font-weight:600;">Protocol</label>
                    <select name="protocol" class="form-select" required>
                        <option value="wifi">WiFi</option><option value="zigbee">Zigbee</option><option value="z-wave">Z-Wave</option>
                        <option value="bluetooth">Bluetooth</option><option value="mqtt">MQTT</option><option value="http">HTTP</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-size:12px;font-weight:600;">IP Address</label>
                    <input type="text" name="ip_address" class="form-control" placeholder="192.168.1.x">
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-size:12px;font-weight:600;">Power (Watts)</label>
                    <input type="number" name="power_watts" class="form-control" value="0">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary" style="border-radius:8px;background:linear-gradient(135deg,#667eea,#764ba2);border:none;width:100%;">Save Device</button>
                </div>
            </div>
        </form>
    </div>

    {{-- DEVICE TABLE --}}
    <div class="dm-card">
        <h6>All Devices ({{ $devices->count() }})</h6>
        <div style="overflow-x:auto;">
            <table class="dm-table">
                <thead><tr><th>Room</th><th>Device</th><th>Type</th><th>Protocol</th><th>IP</th><th>Power</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($devices as $d)
                    <tr>
                        <td><strong>{{ $d->room_no }}</strong></td>
                        <td>{{ $d->device_name }}</td>
                        <td>{{ ucfirst($d->device_type) }}</td>
                        <td>{{ strtoupper($d->protocol ?? 'WiFi') }}</td>
                        <td style="font-family:monospace;font-size:11px;">{{ $d->ip_address ?? '—' }}</td>
                        <td>{{ $d->power_watts ?? 0 }}W</td>
                        <td><span class="dm-status {{ $d->status ? 'online' : 'offline' }}">{{ $d->status ? 'Online' : 'Offline' }}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-danger" style="border-radius:6px;font-size:10px;" onclick="deleteDevice({{ $d->id }})"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted" style="padding:30px;">No devices configured. Click "Add Device" to start.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function submitDevice(e) {
    e.preventDefault();
    var fd = new FormData(e.target);
    var data = {};
    fd.forEach(function(v,k){ data[k] = v; });

    fetch('{{ route("smartroom.add-device") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(data)
    }).then(r => r.json()).then(function(res) {
        if (res.success) { location.reload(); } else { alert(res.message || 'Error adding device'); }
    });
}

function deleteDevice(id) {
    if (!confirm('Remove this device?')) return;
    fetch('/smartroom/devices/' + id + '/delete', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
    }).then(r => r.json()).then(res => { if (res.success) location.reload(); });
}
</script>
@endsection
