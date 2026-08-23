@extends('property.layouts.property')

@section('content')
<style>
    .tl-app { max-width: 600px; margin: 0 auto; }
    .tl-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .tl-header h5 { font-weight: 800; margin: 0; font-size: 16px; }
    .log-item {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 8px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .log-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }
    .log-icon.cleaning { background: #dbeafe; color: #2563eb; }
    .log-icon.maintenance { background: #fef3c7; color: #d97706; }
    .log-info { flex: 1; }
    .log-info .title { font-weight: 600; font-size: 13px; color: #1e293b; }
    .log-info .detail { font-size: 12px; color: #94a3b8; margin-top: 2px; }
    .log-info .notes { font-size: 11px; color: #64748b; margin-top: 4px; font-style: italic; }
    .log-badge {
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 700;
        flex-shrink: 0;
    }
    .log-badge.completed { background: #dcfce7; color: #16a34a; }
    .log-badge.in-progress { background: #fef3c7; color: #d97706; }
    .log-badge.on-hold { background: #fee2e2; color: #dc2626; }
    .log-badge.cancelled { background: #f1f5f9; color: #64748b; }
</style>

<div class="tl-app">
    <div class="tl-header">
        <h5><i class="fa-solid fa-clock-rotate-left" style="margin-right:6px;"></i> Task Activity Log</h5>
        <a href="{{ route('staff.dashboard') }}" style="color:#fff;text-decoration:none;font-size:13px;"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>

    @forelse($logs as $log)
        <div class="log-item">
            <div class="log-icon {{ $log->task_type }}">
                <i class="fa-solid {{ $log->task_type === 'cleaning' ? 'fa-broom' : 'fa-wrench' }}"></i>
            </div>
            <div class="log-info">
                <div class="title">{{ ucfirst($log->task_type) }} Task #{{ $log->task_id }}</div>
                <div class="detail">
                    Staff: {{ $log->staff_id }} · By: {{ $log->u_name }}
                    @if($log->latitude) · 📍 {{ $log->latitude }}, {{ $log->longitude }}@endif
                </div>
                @if($log->notes)
                    <div class="notes">{{ $log->notes }}</div>
                @endif
                <div class="detail">{{ date('M d, Y h:i A', strtotime($log->created_at)) }}</div>
            </div>
            @php $cls = str_replace(' ', '-', strtolower($log->status)); @endphp
            <span class="log-badge {{ $cls }}">{{ $log->status }}</span>
        </div>
    @empty
        <div style="text-align:center;padding:40px;color:#94a3b8;">
            <i class="fa-solid fa-clock-rotate-left" style="font-size:40px;color:#e2e8f0;"></i>
            <p style="margin-top:12px;">No task activity logged yet</p>
        </div>
    @endforelse
</div>
@endsection
