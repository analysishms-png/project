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
    .report-item {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.2s;
    }
    .report-item:hover { border-color: #667eea; box-shadow: 0 2px 8px rgba(102,126,234,0.1); }
    .report-item .name { font-weight: 700; color: #1e293b; font-size: 15px; }
    .report-item .desc { font-size: 12px; color: #94a3b8; margin-top: 2px; }
    .report-item .meta { font-size: 11px; color: #cbd5e1; margin-top: 4px; }
    .schedule-badge {
        display: inline-block;
        background: #dcfce7;
        color: #16a34a;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .config-json {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 12px;
        font-family: monospace;
        font-size: 11px;
        color: #64748b;
        max-width: 400px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        margin-top: 6px;
    }
</style>

<div class="nk-block">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 style="font-weight:800;color:#1e293b;margin:0;">
                <i class="fa-solid fa-bookmark" style="color:#667eea;margin-right:8px;"></i>
                Saved Reports
            </h4>
            <small style="color:#94a3b8;">Manage your custom report configurations</small>
        </div>
        <a href="{{ route('analytics.report-builder') }}" class="btn btn-sm btn-primary" style="border-radius:8px;background:linear-gradient(135deg,#667eea,#764ba2);border:none;">
            <i class="fa-solid fa-plus"></i> New Report
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="border-radius:10px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="border-radius:10px;">{{ session('error') }}</div>
    @endif

    <div class="sr-card">
        @forelse($reports as $report)
            <div class="report-item">
                <div style="flex:1;">
                    <div class="name">
                        <i class="fa-solid fa-file-lines" style="color:#667eea;margin-right:6px;"></i>
                        {{ $report->report_name }}
                        @if($report->is_scheduled)
                            <span class="schedule-badge">
                                <i class="fa-solid fa-clock"></i> {{ ucfirst($report->schedule_frequency) }}
                            </span>
                        @endif
                    </div>
                    @if($report->description)
                        <div class="desc">{{ $report->description }}</div>
                    @endif
                    <div class="config-json">{{ substr($report->config_json, 0, 120) }}...</div>
                    <div class="meta">
                        Created: {{ \Carbon\Carbon::parse($report->created_at)->format('M d, Y h:i A') }}
                        @if($report->schedule_email)
                            | Schedule to: {{ $report->schedule_email }}
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('analytics.load-report', $report->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;" title="Load in Report Builder">
                        <i class="fa-solid fa-play"></i> Run
                    </a>
                    @if(!$report->is_scheduled)
                        <button class="btn btn-sm btn-outline-success" style="border-radius:8px;" title="Schedule"
                                onclick="showScheduleModal({{ $report->id }}, '{{ $report->report_name }}')">
                            <i class="fa-solid fa-clock"></i>
                        </button>
                    @else
                        <button class="btn btn-sm btn-outline-warning" style="border-radius:8px;" title="Unschedule"
                                onclick="unschedule({{ $report->id }})">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </button>
                    @endif
                    <form action="{{ route('analytics.delete-report', $report->id) }}" method="POST" style="display:inline;"
                          onsubmit="return confirm('Delete this saved report?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius:8px;" title="Delete">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div style="text-align:center;padding:60px 20px;">
                <i class="fa-solid fa-bookmark" style="font-size:48px;color:#e2e8f0;"></i>
                <h6 style="color:#94a3b8;margin-top:16px;">No Saved Reports</h6>
                <p style="color:#cbd5e1;font-size:13px;">Create your first report in the Report Builder and save it here.</p>
                <a href="{{ route('analytics.report-builder') }}" class="btn btn-primary btn-sm" style="border-radius:8px;background:linear-gradient(135deg,#667eea,#764ba2);border:none;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Open Report Builder
                </a>
            </div>
        @endforelse
    </div>

    {{-- ═══ SCHEDULE MODAL ═══ --}}
    <div class="modal fade" id="scheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:16px;">
                <div class="modal-header" style="border-bottom:1px solid #f1f5f9;">
                    <h6 class="modal-title" style="font-weight:700;">Schedule Report: <span id="schedReportName"></span></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="schedReportId">
                    <div class="mb-3">
                        <label class="form-label" style="font-weight:600;font-size:13px;">Frequency</label>
                        <select id="schedFrequency" class="form-select" style="border-radius:10px;">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-weight:600;font-size:13px;">Email</label>
                        <input type="email" id="schedEmail" class="form-control" value="{{ Auth::user()->email ?? '' }}" style="border-radius:10px;">
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f1f5f9;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="submitSchedule()" style="background:linear-gradient(135deg,#667eea,#764ba2);border:none;">
                        <i class="fa-solid fa-clock"></i> Schedule
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function showScheduleModal(id, name) {
    document.getElementById('schedReportId').value = id;
    document.getElementById('schedReportName').textContent = name;
    new bootstrap.Modal(document.getElementById('scheduleModal')).show();
}

function submitSchedule() {
    fetch('{{ route("analytics.schedule-report") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            report_id: document.getElementById('schedReportId').value,
            frequency: document.getElementById('schedFrequency').value,
            email: document.getElementById('schedEmail').value
        })
    })
    .then(r => r.json())
    .then(function(res) {
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('scheduleModal')).hide();
            location.reload();
        }
    });
}

function unschedule(id) {
    fetch('{{ route("analytics.unschedule-report", 0) }}'.replace('/0', '/' + id), {
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
