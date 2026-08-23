@extends('property.layouts.property')

@section('content')

<style>
    .sr-card {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .sched-item {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .sched-item:hover { border-color: #10b981; }
    .freq-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .freq-daily { background: #dbeafe; color: #1d4ed8; }
    .freq-weekly { background: #fef3c7; color: #b45309; }
    .freq-monthly { background: #ede9fe; color: #7c3aed; }
</style>

<div class="nk-block">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 style="font-weight:800;color:#1e293b;margin:0;">
                <i class="fa-solid fa-clock" style="color:#10b981;margin-right:8px;"></i>
                Scheduled Reports
            </h4>
            <small style="color:#94a3b8;">Automated report delivery schedule</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('analytics.saved-reports') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                <i class="fa-solid fa-bookmark"></i> Saved Reports
            </a>
            <a href="{{ route('analytics.report-builder') }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Report Builder
            </a>
        </div>
    </div>

    <div class="sr-card">
        @forelse($reports as $report)
            <div class="sched-item">
                <div>
                    <div style="font-weight:700;color:#1e293b;">
                        <i class="fa-solid fa-file-lines" style="color:#667eea;margin-right:6px;"></i>
                        {{ $report->report_name }}
                        <span class="freq-badge freq-{{ $report->schedule_frequency }}">
                            {{ ucfirst($report->schedule_frequency) }}
                        </span>
                    </div>
                    <div style="font-size:12px;color:#94a3b8;margin-top:4px;">
                        Deliver to: <strong>{{ $report->schedule_email ?? 'N/A' }}</strong>
                        | Created: {{ \Carbon\Carbon::parse($report->created_at)->format('M d, Y') }}
                    </div>
                    <div style="background:#f8fafc;border-radius:6px;padding:4px 10px;font-family:monospace;font-size:10px;color:#64748b;margin-top:6px;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ substr($report->config_json, 0, 80) }}...
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('analytics.load-report', $report->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;">
                        <i class="fa-solid fa-play"></i> Run
                    </a>
                    <button class="btn btn-sm btn-outline-danger" style="border-radius:8px;"
                            onclick="unschedule({{ $report->id }})">
                        <i class="fa-solid fa-clock-rotate-left"></i> Remove
                    </button>
                </div>
            </div>
        @empty
            <div style="text-align:center;padding:60px 20px;">
                <i class="fa-solid fa-clock" style="font-size:48px;color:#e2e8f0;"></i>
                <h6 style="color:#94a3b8;margin-top:16px;">No Scheduled Reports</h6>
                <p style="color:#cbd5e1;font-size:13px;">Save a report and schedule it from the Saved Reports page.</p>
                <a href="{{ route('analytics.saved-reports') }}" class="btn btn-primary btn-sm" style="border-radius:8px;background:linear-gradient(135deg,#10b981,#38ef7d);border:none;">
                    <i class="fa-solid fa-bookmark"></i> View Saved Reports
                </a>
            </div>
        @endforelse
    </div>
</div>

@endsection

@section('scripts')
<script>
function unschedule(id) {
    if (!confirm('Remove this schedule?')) return;
    fetch('/analytics/schedule/unschedule/' + id, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(function(res) {
        if (res.success) location.reload();
    });
}
</script>
@endsection
