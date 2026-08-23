@extends('property.layouts.property')

@section('content')
<style>
    .rc-app { max-width: 600px; margin: 0 auto; }
    .rc-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #fff;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 16px;
        text-align: center;
    }
    .rc-header h4 { font-weight: 800; margin: 0; font-size: 20px; }
    .rc-header .guest { font-size: 13px; opacity: 0.7; margin-top: 4px; }
    .rc-card {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 14px;
    }
    .rc-card h6 { font-weight: 700; font-size: 13px; text-transform: uppercase; color: #64748b; margin-bottom: 12px; letter-spacing: 0.5px; }
    .rc-device-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .rc-device-row:last-child { border-bottom: none; }
    .rc-device-icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .rc-device-icon.light { background: #fef3c7; color: #d97706; }
    .rc-device-icon.ac { background: #dbeafe; color: #2563eb; }
    .rc-device-icon.curtain { background: #ede9fe; color: #7c3aed; }
    .rc-device-icon.tv { background: #fce7f3; color: #db2777; }
    .rc-device-icon.lock { background: #dcfce7; color: #16a34a; }
    .rc-device-icon.speaker { background: #e0f2fe; color: #0284c7; }
    .rc-device-icon.sensor { background: #f1f5f9; color: #475569; }
    .rc-device-icon.thermostat { background: #fee2e2; color: #dc2626; }
    .rc-device-info { flex: 1; }
    .rc-device-info .name { font-weight: 600; font-size: 14px; color: #1e293b; }
    .rc-device-info .type { font-size: 11px; color: #94a3b8; }
    .rc-toggle {
        position: relative;
        width: 48px; height: 26px;
        cursor: pointer;
    }
    .rc-toggle input { display: none; }
    .rc-toggle .slider {
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: #e2e8f0;
        border-radius: 26px;
        transition: all 0.3s;
    }
    .rc-toggle .slider::before {
        content: '';
        position: absolute;
        width: 20px; height: 20px;
        left: 3px; bottom: 3px;
        background: #fff;
        border-radius: 50%;
        transition: all 0.3s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    .rc-toggle input:checked + .slider { background: #10b981; }
    .rc-toggle input:checked + .slider::before { transform: translateX(22px); }
    .rc-dimmer {
        width: 100%;
        margin-top: 6px;
        accent-color: #f59e0b;
    }
    .rc-temp-control {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 6px;
    }
    .rc-temp-btn {
        width: 32px; height: 32px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        font-weight: 700;
        cursor: pointer;
        font-size: 16px;
    }
    .rc-temp-btn:hover { border-color: #667eea; }
    .rc-temp-value {
        font-size: 20px;
        font-weight: 800;
        color: #1e293b;
        min-width: 50px;
        text-align: center;
    }
    .rc-scene-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
    .rc-scene-btn {
        padding: 12px 8px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: #fff;
    }
    .rc-scene-btn:hover { border-color: #667eea; }
    .rc-scene-btn.active { border-color: #10b981; background: #f0fdf4; }
    .rc-scene-btn i { font-size: 20px; display: block; margin-bottom: 4px; }
    .rc-scene-btn span { font-size: 10px; font-weight: 600; }
    .rc-bulk-btns { display: flex; gap: 10px; margin-bottom: 14px; }
    .rc-bulk-btn {
        flex: 1;
        padding: 10px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        text-align: center;
        cursor: pointer;
        font-weight: 700;
        font-size: 13px;
        background: #fff;
        transition: all 0.2s;
    }
    .rc-bulk-btn.on { border-color: #10b981; color: #059669; }
    .rc-bulk-btn.off { border-color: #ef4444; color: #dc2626; }
    .rc-bulk-btn:hover { transform: translateY(-1px); }
    .rc-guest-link {
        display: block;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        text-align: center;
        padding: 12px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 700;
        margin-top: 12px;
    }
    .rc-guest-link:hover { color: #fff; }
</style>

<div class="rc-app">
    {{-- HEADER --}}
    <div class="rc-header">
        <h4><i class="fa-solid fa-microchip" style="margin-right:6px;"></i> Room {{ $roomNo }}</h4>
        <div class="guest">
            @if($guest) Guest: {{ $guest->name }} · {{ $guest->chkindate }} to {{ $guest->depdate }}
            @else <span style="opacity:0.5;">Vacant — No guest</span>
            @endif
        </div>
        <div style="margin-top:10px;">
            <a href="{{ route('smartroom.guest-portal', $roomNo) }}" class="rc-guest-link" target="_blank">
                <i class="fa-solid fa-mobile-screen-button"></i> Open Guest Portal
            </a>
        </div>
    </div>

    {{-- BULK CONTROLS --}}
    <div class="rc-bulk-btns">
        <div class="rc-bulk-btn on" onclick="bulkAction('on')"><i class="fa-solid fa-power-off"></i> All On</div>
        <div class="rc-bulk-btn off" onclick="bulkAction('off')"><i class="fa-solid fa-power-off"></i> All Off</div>
    </div>

    {{-- SCENES --}}
    @if($scenes->count() > 0)
    <div class="rc-card">
        <h6><i class="fa-solid fa-wand-magic-sparkles" style="margin-right:4px;"></i> Scenes</h6>
        <div class="rc-scene-grid">
            @foreach($scenes as $scene)
                <div class="rc-scene-btn {{ $scene->is_active ? 'active' : '' }}" onclick="activateScene({{ $scene->id }})">
                    <i class="fa-solid {{ $scene->icon ?? 'fa-lightbulb' }}" style="color:{{ $scene->color ?? '#667eea' }}"></i>
                    <span>{{ $scene->name }}</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- DEVICES --}}
    @forelse($devices as $device)
        @php
            $icons = ['light' => 'fa-lightbulb', 'ac' => 'fa-snowflake', 'curtain' => 'fa-bars-staggered', 'tv' => 'fa-tv', 'sensor' => 'fa-satellite-dish', 'lock' => 'fa-lock', 'thermostat' => 'fa-temperature-half', 'speaker' => 'fa-volume-high', 'camera' => 'fa-video', 'doorbell' => 'fa-bell', 'motion' => 'fa-person-walking', 'power' => 'fa-plug'];
        @endphp
        <div class="rc-card">
            <div class="rc-device-row">
                <div class="rc-device-icon {{ $device->device_type }}">
                    <i class="fa-solid {{ $icons[$device->device_type] ?? 'fa-microchip' }}"></i>
                </div>
                <div class="rc-device-info">
                    <div class="name">{{ $device->device_name }}</div>
                    <div class="type">{{ ucfirst($device->device_type) }} · {{ ucfirst($device->protocol ?? 'WiFi') }}</div>
                </div>
                <label class="rc-toggle">
                    <input type="checkbox" {{ $device->status ? 'checked' : '' }}
                           onchange="toggleDevice({{ $device->id }}, this.checked ? 'on' : 'off')">
                    <span class="slider"></span>
                </label>
            </div>

            {{-- Dimmer for lights --}}
            @if($device->device_type === 'light')
                <input type="range" class="rc-dimmer" min="0" max="100" value="{{ $device->brightness ?? 100 }}"
                       onchange="toggleDevice({{ $device->id }}, 'dim', this.value)">
            @endif

            {{-- Thermostat for AC --}}
            @if($device->device_type === 'ac' || $device->device_type === 'thermostat')
                <div class="rc-temp-control">
                    <button class="rc-temp-btn" onclick="changeTemp({{ $device->id }}, -1)">−</button>
                    <div class="rc-temp-value" id="temp{{ $device->id }}">{{ $device->temperature ?? 24 }}°C</div>
                    <button class="rc-temp-btn" onclick="changeTemp({{ $device->id }}, 1)">+</button>
                </div>
            @endif
        </div>
    @empty
        <div class="rc-card" style="text-align:center;padding:40px;">
            <i class="fa-solid fa-plug" style="font-size:40px;color:#e2e8f0;"></i>
            <h6 style="color:#94a3b8;margin-top:12px;">No IoT Devices</h6>
            <p style="font-size:13px;color:#cbd5e1;">Add devices from the Devices management page.</p>
            <a href="{{ route('smartroom.devices') }}" class="btn btn-primary btn-sm" style="border-radius:8px;margin-top:8px;">Add Devices</a>
        </div>
    @endforelse

    {{-- DEVICE LOG --}}
    @if($logs->count() > 0)
    <div class="rc-card">
        <h6><i class="fa-solid fa-clock-rotate-left" style="margin-right:4px;"></i> Recent Activity (24h)</h6>
        <div style="max-height:200px;overflow-y:auto;">
            @foreach($logs as $log)
                <div style="padding:6px 0;border-bottom:1px solid #f1f5f9;font-size:12px;">
                    <span style="font-weight:600;">{{ $log->device_name ?? 'System' }}</span>
                    <span style="color:#667eea;">{{ $log->action }}</span>
                    @if($log->value !== null) <span style="color:#94a3b8;">({{ $log->value }})</span> @endif
                    <span style="float:right;color:#cbd5e1;">{{ date('h:i A', strtotime($log->created_at)) }}</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
var roomId = '{{ $roomNo }}';

function toggleDevice(deviceId, action, value) {
    var body = { device_id: deviceId, action: action };
    if (value !== undefined) body.brightness = value;

    fetch('{{ route("smartroom.toggle") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(body)
    }).then(r => r.json()).then(res => {
        if (!res.success) alert(res.message);
    });
}

function changeTemp(deviceId, delta) {
    var el = document.getElementById('temp' + deviceId);
    var current = parseInt(el.textContent) || 24;
    var newTemp = current + delta;
    if (newTemp < 16 || newTemp > 30) return;
    el.textContent = newTemp + '°C';
    toggleDevice(deviceId, 'set_temp', newTemp);
}

function activateScene(sceneId) {
    fetch('/smartroom/scenes/' + sceneId + '/activate', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
    }).then(r => r.json()).then(res => {
        if (res.success) { alert(res.message); location.reload(); }
        else alert(res.message);
    });
}

function bulkAction(action) {
    if (!confirm(action === 'on' ? 'Turn ON all devices?' : 'Turn OFF all devices?')) return;
    var url = action === 'on' ? '/smartroom/room/' + roomId + '/all-on' : '/smartroom/room/' + roomId + '/all-off';
    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
    }).then(r => r.json()).then(res => {
        if (res.success) location.reload();
    });
}
</script>
@endsection
