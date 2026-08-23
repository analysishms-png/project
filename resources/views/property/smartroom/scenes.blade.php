@extends('property.layouts.property')

@section('content')
<style>
    .sc-card { background: #fff; border: 1px solid #e8ecf1; border-radius: 14px; padding: 20px; margin-bottom: 16px; }
    .sc-card h6 { font-weight: 700; font-size: 13px; text-transform: uppercase; color: #64748b; margin-bottom: 12px; }
    .sc-scene-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 14px; }
    .sc-scene-card {
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        text-align: center;
        transition: all 0.2s;
    }
    .sc-scene-card:hover { border-color: #667eea; }
    .sc-scene-card.active { border-color: #10b981; background: #f0fdf4; }
    .sc-scene-icon { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff; margin: 0 auto 10px; }
    .sc-scene-name { font-weight: 700; font-size: 15px; color: #1e293b; }
    .sc-scene-desc { font-size: 12px; color: #94a3b8; margin-top: 4px; }
    .sc-btn { padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 600; cursor: pointer; border: 1px solid; margin-top: 10px; }
    .sc-btn.activate { border-color: #10b981; color: #059669; background: #fff; }
    .sc-btn.deactivate { border-color: #ef4444; color: #dc2626; background: #fff; }
</style>

<div class="nk-block">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 style="font-weight:800;color:#1e293b;margin:0;"><i class="fa-solid fa-wand-magic-sparkles" style="color:#8b5cf6;margin-right:8px;"></i> IoT Scenes</h4>
        <button class="btn btn-primary btn-sm" style="border-radius:8px;background:linear-gradient(135deg,#8b5cf6,#a855f7);border:none;" onclick="document.getElementById('addScene').classList.toggle('show')"><i class="fa-solid fa-plus"></i> New Scene</button>
    </div>

    <div id="addScene" style="display:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:16px;">
        <form onsubmit="createScene(event)">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label" style="font-size:12px;font-weight:600;">Scene Name</label><input type="text" name="name" class="form-control" placeholder="e.g. Good Night" required style="border-radius:8px;"></div>
                <div class="col-md-4"><label class="form-label" style="font-size:12px;font-weight:600;">Description</label><input type="text" name="description" class="form-control" placeholder="Brief description" style="border-radius:8px;"></div>
                <div class="col-md-2"><label class="form-label" style="font-size:12px;font-weight:600;">Color</label><input type="color" name="color" value="#667eea" class="form-control form-control-color" style="border-radius:8px;width:100%;"></div>
                <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary" style="border-radius:8px;width:100%;">Create</button></div>
            </div>
        </form>
    </div>

    <div class="sc-scene-grid">
        @forelse($scenes as $scene)
            <div class="sc-scene-card {{ $scene->is_active ? 'active' : '' }}">
                <div class="sc-scene-icon" style="background:{{ $scene->color ?? '#667eea' }}"><i class="fa-solid {{ $scene->icon ?? 'fa-lightbulb' }}"></i></div>
                <div class="sc-scene-name">{{ $scene->name }}</div>
                <div class="sc-scene-desc">{{ $scene->description ?? '' }}</div>
                @if($scene->is_active)
                    <div style="color:#10b981;font-size:11px;font-weight:700;margin-top:6px;"><i class="fa-solid fa-circle-check"></i> Active</div>
                    <button class="sc-btn deactivate" onclick="deactivateScene({{ $scene->id }})">Deactivate</button>
                @else
                    <button class="sc-btn activate" onclick="activateScene({{ $scene->id }})">Activate</button>
                @endif
            </div>
        @empty
            <div style="text-align:center;padding:60px;grid-column:1/-1;color:#94a3b8;">
                <i class="fa-solid fa-wand-magic-sparkles" style="font-size:48px;color:#e2e8f0;"></i>
                <h6 style="margin-top:12px;">No Scenes Created</h6>
                <p style="font-size:13px;">Create scenes to control multiple devices at once.</p>
            </div>
        @endforelse
    </div>
</div>

@endsection

@section('scripts')
<script>
function createScene(e) {
    e.preventDefault();
    var fd = new FormData(e.target);
    var data = {}; fd.forEach(function(v,k){ data[k] = v; });
    fetch('{{ route("smartroom.create-scene") }}', {
        method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(data)
    }).then(r => r.json()).then(res => { if (res.success) location.reload(); else alert(res.message); });
}
function activateScene(id) {
    fetch('/smartroom/scenes/' + id + '/activate', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } })
    .then(r => r.json()).then(res => { if (res.success) { alert(res.message); location.reload(); } });
}
function deactivateScene(id) {
    fetch('/smartroom/scenes/' + id + '/deactivate', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } })
    .then(r => r.json()).then(res => { if (res.success) location.reload(); });
}
</script>
@endsection
