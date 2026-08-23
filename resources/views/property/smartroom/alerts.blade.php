@extends('property.layouts.property')

@section('content')
<style>
    .al-card { background: #fff; border: 1px solid #e8ecf1; border-radius: 14px; padding: 20px; margin-bottom: 16px; }
    .al-item { display: flex; align-items: flex-start; gap: 12px; padding: 14px 0; border-bottom: 1px solid #f1f5f9; }
    .al-item:last-child { border-bottom: none; }
    .al-dot { width: 10px; height: 10px; border-radius: 50%; margin-top: 4px; flex-shrink: 0; }
    .al-dot.critical { background: #ef4444; } .al-dot.warning { background: #f59e0b; } .al-dot.info { background: #3b82f6; }
    .al-resolved { opacity: 0.5; }
</style>

<div class="nk-block">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 style="font-weight:800;color:#1e293b;margin:0;"><i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;margin-right:8px;"></i> Device Alerts</h4>
        <a href="{{ route('smartroom.dashboard') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
    </div>

    <div class="al-card">
        @forelse($alerts as $alert)
            <div class="al-item {{ $alert->is_resolved ? 'al-resolved' : '' }}">
                <div class="al-dot {{ $alert->severity ?? 'info' }}"></div>
                <div style="flex:1;">
                    <div style="font-weight:700;font-size:14px;">{{ $alert->title ?? 'Alert' }}</div>
                    <div style="font-size:12px;color:#64748b;margin-top:2px;">{{ $alert->message ?? '' }}</div>
                    <div style="font-size:11px;color:#94a3b8;margin-top:4px;">
                        {{ $alert->device_name ?? '' }} · {{ $alert->room_no ?? '' }}
                        · {{ $alert->created_at ? date('M d, Y h:i A', strtotime($alert->created_at)) : '' }}
                        @if($alert->is_resolved) · ✅ Resolved by {{ $alert->resolved_by }} @endif
                    </div>
                </div>
                @if(!$alert->is_resolved)
                    <button class="btn btn-sm btn-outline-success" style="border-radius:8px;font-size:11px;" onclick="resolve({{ $alert->id }})">Resolve</button>
                @endif
            </div>
        @empty
            <div style="text-align:center;padding:60px;color:#10b981;"><i class="fa-solid fa-check-circle" style="font-size:48px;"></i><h6 style="margin-top:12px;">All Clear</h6><p style="font-size:13px;color:#94a3b8;">No device alerts at this time.</p></div>
        @endforelse
    </div>
</div>

@endsection

@section('scripts')
<script>
function resolve(id) {
    fetch('/smartroom/alerts/' + id + '/resolve', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } })
    .then(r => r.json()).then(res => { if (res.success) location.reload(); });
}
</script>
@endsection
