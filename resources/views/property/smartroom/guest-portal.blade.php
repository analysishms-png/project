<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Smart Room — {{ $roomNo }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        body { background: #0f172a; color: #fff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; min-height: 100vh; padding: 0; }
        .gp-header { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); padding: 30px 20px 20px; text-align: center; }
        .gp-header h4 { font-weight: 800; font-size: 22px; margin: 0; }
        .gp-header .room { font-size: 14px; opacity: 0.6; margin-top: 4px; }
        .gp-header .guest { font-size: 12px; opacity: 0.4; margin-top: 2px; }
        .gp-content { padding: 16px; max-width: 480px; margin: 0 auto; }
        .gp-section { margin-bottom: 20px; }
        .gp-section-title { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; font-weight: 700; margin-bottom: 12px; }
        .gp-device {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.2s;
        }
        .gp-device.on { border-color: #10b981; background: #0f2922; }
        .gp-device-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .gp-device-info { flex: 1; }
        .gp-device-info .name { font-weight: 600; font-size: 15px; }
        .gp-device-info .type { font-size: 11px; color: #64748b; }
        .gp-toggle {
            width: 52px; height: 28px;
            background: #334155;
            border-radius: 28px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s;
            flex-shrink: 0;
        }
        .gp-toggle.on { background: #10b981; }
        .gp-toggle::before {
            content: '';
            position: absolute;
            width: 22px; height: 22px;
            left: 3px; top: 3px;
            background: #fff;
            border-radius: 50%;
            transition: all 0.3s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }
        .gp-toggle.on::before { transform: translateX(24px); }
        .gp-slider { width: 100%; margin-top: 8px; accent-color: #f59e0b; }
        .gp-temp { display: flex; align-items: center; justify-content: center; gap: 16px; margin-top: 8px; }
        .gp-temp-btn { width: 40px; height: 40px; border-radius: 12px; background: #334155; border: none; color: #fff; font-size: 20px; font-weight: 700; cursor: pointer; }
        .gp-temp-btn:active { background: #475569; }
        .gp-temp-val { font-size: 28px; font-weight: 800; }
        .gp-scene-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .gp-scene-btn {
            background: #1e293b;
            border: 2px solid #334155;
            border-radius: 14px;
            padding: 16px 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            color: #fff;
        }
        .gp-scene-btn:active { transform: scale(0.95); }
        .gp-scene-btn.active { border-color: #10b981; background: #0f2922; }
        .gp-scene-btn i { font-size: 24px; display: block; margin-bottom: 6px; }
        .gp-scene-btn span { font-size: 11px; font-weight: 600; }
        .gp-footer { text-align: center; padding: 20px; font-size: 11px; color: #475569; }
    </style>
</head>
<body>
    <div class="gp-header">
        <h4><i class="fa-solid fa-microchip" style="margin-right:6px;"></i> Smart Room</h4>
        <div class="room">Room {{ $roomNo }} · {{ $room->name ?? $room->room_cat ?? '' }}</div>
        @if($guest)
            <div class="guest">Welcome, {{ $guest->name }}</div>
        @endif
    </div>

    <div class="gp-content">
        {{-- SCENES --}}
        @if($scenes->count() > 0)
        <div class="gp-section">
            <div class="gp-section-title">Quick Scenes</div>
            <div class="gp-scene-grid">
                @foreach($scenes as $scene)
                    <div class="gp-scene-btn" onclick="activateGuestScene({{ $scene->id }}, this)">
                        <i class="fa-solid {{ $scene->icon ?? 'fa-lightbulb' }}" style="color:{{ $scene->color ?? '#f59e0b' }}"></i>
                        <span>{{ $scene->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- DEVICES --}}
        <div class="gp-section">
            <div class="gp-section-title">Room Controls</div>
            @forelse($devices as $device)
                @php
                    $icons = ['light' => 'fa-lightbulb', 'ac' => 'fa-snowflake', 'curtain' => 'fa-bars-staggered', 'tv' => 'fa-tv', 'lock' => 'fa-lock', 'thermostat' => 'fa-temperature-half', 'speaker' => 'fa-volume-high'];
                    $colors = ['light' => '#f59e0b', 'ac' => '#3b82f6', 'curtain' => '#8b5cf6', 'tv' => '#ec4899', 'lock' => '#10b981', 'thermostat' => '#ef4444', 'speaker' => '#06b6d4'];
                @endphp
                <div class="gp-device {{ $device->status ? 'on' : '' }}" id="device{{ $device->id }}">
                    <div class="gp-device-icon" style="background:{{ $colors[$device->device_type] ?? '#667eea' }}20; color:{{ $colors[$device->device_type] ?? '#667eea' }}">
                        <i class="fa-solid {{ $icons[$device->device_type] ?? 'fa-microchip' }}"></i>
                    </div>
                    <div class="gp-device-info">
                        <div class="name">{{ $device->device_name }}</div>
                        <div class="type">{{ ucfirst($device->device_type) }}</div>
                        @if($device->device_type === 'light')
                            <input type="range" class="gp-slider" min="0" max="100" value="{{ $device->brightness ?? 100 }}"
                                   onchange="guestDimmer({{ $device->id }}, this.value)">
                        @endif
                        @if($device->device_type === 'ac' || $device->device_type === 'thermostat')
                            <div class="gp-temp">
                                <button class="gp-temp-btn" onclick="guestTemp({{ $device->id }}, -1)">−</button>
                                <div class="gp-temp-val" id="gtemp{{ $device->id }}">{{ $device->temperature ?? 24 }}°</div>
                                <button class="gp-temp-btn" onclick="guestTemp({{ $device->id }}, 1)">+</button>
                            </div>
                        @endif
                    </div>
                    <div class="gp-toggle {{ $device->status ? 'on' : '' }}" id="gtoggle{{ $device->id }}" onclick="guestToggle({{ $device->id }}, this)"></div>
                </div>
            @empty
                <div style="text-align:center;padding:40px;color:#475569;"><i class="fa-solid fa-plug" style="font-size:32px;"></i><p>No smart devices available</p></div>
            @endforelse
        </div>
    </div>

    <div class="gp-footer">Powered by Analysis HMS Smart Room</div>

    <script>
    var csrfToken = '{{ csrf_token() }}';

    function guestToggle(deviceId, el) {
        var isOn = el.classList.contains('on');
        el.classList.toggle('on');
        document.getElementById('device' + deviceId).classList.toggle('on');
        fetch('/smartroom/api/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ device_id: deviceId, action: isOn ? 'off' : 'on' })
        });
    }

    function guestDimmer(deviceId, value) {
        fetch('/smartroom/api/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ device_id: deviceId, action: 'dim', brightness: value })
        });
    }

    function guestTemp(deviceId, delta) {
        var el = document.getElementById('gtemp' + deviceId);
        var current = parseInt(el.textContent) || 24;
        var next = current + delta;
        if (next < 16 || next > 30) return;
        el.textContent = next + '°';
        fetch('/smartroom/api/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ device_id: deviceId, action: 'set_temp', temperature: next })
        });
    }

    function activateGuestScene(sceneId, el) {
        document.querySelectorAll('.gp-scene-btn').forEach(b => b.classList.remove('active'));
        el.classList.add('active');
        fetch('/smartroom/api/scenes/' + sceneId + '/activate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        }).then(r => r.json()).then(res => {
            if (res.success) {
                // Refresh page to reflect new device states
                setTimeout(function() { location.reload(); }, 500);
            }
        });
    }
    </script>
</body>
</html>
