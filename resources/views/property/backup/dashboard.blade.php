@extends('property.layouts.property')

@section('content')
<style>
    .bk-card { background: #fff; border: 1px solid #e8ecf1; border-radius: 14px; padding: 20px; margin-bottom: 16px; }
    .bk-card h6 { font-weight: 700; font-size: 13px; text-transform: uppercase; color: #64748b; margin-bottom: 12px; }
    .bk-kpi { display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; border-radius: 12px; margin-right: 12px; }
    .bk-item { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
    .bk-item:last-child { border-bottom: none; }
</style>

<div class="nk-block">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 style="font-weight:800;color:#1e293b;margin:0;"><i class="fa-solid fa-database" style="color:#667eea;margin-right:8px;"></i> Database Backup</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('backup.schedule') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;"><i class="fa-solid fa-clock"></i> Schedule</a>
            <button class="btn btn-sm btn-primary" style="border-radius:8px;background:linear-gradient(135deg,#10b981,#38ef7d);border:none;" onclick="createBackup()">
                <i class="fa-solid fa-download"></i> Create Backup
            </button>
        </div>
    </div>

    <div class="mb-3">
        <div class="bk-kpi" style="background:#f0fdf4;color:#16a34a;"><i class="fa-solid fa-database"></i> <strong>{{ $dbName }}</strong></div>
        <div class="bk-kpi" style="background:#eff6ff;color:#2563eb;"><i class="fa-solid fa-table"></i> {{ $tableCount }} tables</div>
        <div class="bk-kpi" style="background:#fef3c7;color:#d97706;"><i class="fa-solid fa-file"></i> {{ $backups->count() }} backups</div>
    </div>

    <div id="backupStatus" style="display:none;" class="alert alert-info" style="border-radius:10px;">
        <i class="fa-solid fa-spinner fa-spin"></i> Creating backup...
    </div>

    <div class="bk-card">
        <h6><i class="fa-solid fa-folder-open" style="margin-right:4px;"></i> Available Backups</h6>
        @forelse($backups as $backup)
            <div class="bk-item">
                <div style="width:40px;height:40px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-file-zipper" style="color:#10b981;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-weight:600;font-size:13px;">{{ $backup['name'] }}</div>
                    <div style="font-size:11px;color:#94a3b8;">{{ $backup['date'] }} · {{ $backup['size'] }}</div>
                </div>
                <a href="{{ route('backup.download', $backup['name']) }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;" title="Download"><i class="fa-solid fa-download"></i></a>
                <form action="{{ route('backup.delete', $backup['name']) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this backup?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius:8px;" title="Delete"><i class="fa-solid fa-trash"></i></button>
                </form>
            </div>
        @empty
            <div style="text-align:center;padding:40px;color:#94a3b8;">
                <i class="fa-solid fa-database" style="font-size:40px;color:#e2e8f0;"></i>
                <p style="margin-top:12px;">No backups yet. Click "Create Backup" to generate your first backup.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<script>
function createBackup() {
    document.getElementById('backupStatus').style.display = 'block';
    fetch('{{ route("backup.create") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        document.getElementById('backupStatus').style.display = 'none';
        if (res.success) {
            alert('✅ ' + res.message + ' (' + res.size + ')');
            location.reload();
        } else {
            alert('❌ ' + res.message);
        }
    })
    .catch(err => {
        document.getElementById('backupStatus').style.display = 'none';
        alert('Error: ' + err.message);
    });
}
</script>
@endsection
