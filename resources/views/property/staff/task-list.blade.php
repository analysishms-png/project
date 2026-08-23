@extends('property.layouts.property')

@section('content')
<style>
    .tl-app { max-width: 480px; margin: 0 auto; }
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
    .tl-filters {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .tl-filter-btn {
        padding: 8px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        background: #fff;
        color: #64748b;
        text-decoration: none;
        transition: all 0.2s;
    }
    .tl-filter-btn.active { border-color: #667eea; background: rgba(102,126,234,0.08); color: #667eea; }
    .tl-task-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s;
    }
    .tl-task-card:hover { border-color: #667eea; box-shadow: 0 2px 8px rgba(102,126,234,0.1); }
    .tl-task-card .icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .tl-task-card .icon.cleaning { background: #dbeafe; color: #2563eb; }
    .tl-task-card .icon.maintenance { background: #fef3c7; color: #d97706; }
    .tl-task-card .info { flex: 1; }
    .tl-task-card .info .room { font-weight: 700; font-size: 15px; color: #1e293b; }
    .tl-task-card .info .detail { font-size: 12px; color: #94a3b8; margin-top: 2px; }
    .tl-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        flex-shrink: 0;
    }
    .tl-badge.in-progress { background: #fef3c7; color: #d97706; }
    .tl-badge.completed { background: #dcfce7; color: #16a34a; }
    .tl-badge.pending { background: #f1f5f9; color: #64748b; }
    .tl-badge.dirty { background: #fee2e2; color: #dc2626; }
    .tl-empty { text-align: center; padding: 60px 20px; color: #94a3b8; }
    .tl-staff-select {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        background: #fff;
        margin-bottom: 12px;
    }
    .tl-count {
        background: #f1f5f9;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        font-size: 13px;
    }
    .tl-count strong { color: #667eea; }
</style>

<div class="tl-app">
    <div class="tl-header">
        <div>
            <h5><i class="fa-solid fa-list-check" style="margin-right:6px;"></i> My Tasks</h5>
            <small style="opacity:0.7;">{{ date('l, F d, Y') }}</small>
        </div>
        <a href="{{ route('staff.dashboard', ['staff_id' => $staffId ?? '']) }}" style="color:#fff;text-decoration:none;font-size:13px;">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
    </div>

    {{-- Staff selector --}}
    <select class="tl-staff-select" onchange="window.location='{{ url("staff/tasks") }}?staff_id='+this.value+'&type={{ $type }}'">
        <option value="">— Select Staff —</option>
        @foreach($staffList as $s)
            <option value="{{ $s->scode }}" {{ ($staffId ?? '') == $s->scode ? 'selected' : '' }}>{{ $s->name }}</option>
        @endforeach
    </select>

    {{-- Filters --}}
    <div class="tl-filters">
        <a class="tl-filter-btn {{ $type == 'all' ? 'active' : '' }}" href="?staff_id={{ $staffId }}&type=all">All ({{ $tasks->count() }})</a>
        <a class="tl-filter-btn {{ $type == 'cleaning' ? 'active' : '' }}" href="?staff_id={{ $staffId }}&type=cleaning">🧹 Cleaning</a>
        <a class="tl-filter-btn {{ $type == 'maintenance' ? 'active' : '' }}" href="?staff_id={{ $staffId }}&type=maintenance">🔧 Maintenance</a>
    </div>

    {{-- Task count summary --}}
    @if($tasks->count() > 0)
        @php
            $completedCount = $tasks->where('status', 'Completed')->count();
            $pendingCount = $tasks->where('status', '!=', 'Completed')->count();
        @endphp
        <div class="tl-count">
            <span>✅ Completed: <strong>{{ $completedCount }}</strong></span>
            <span>⏳ Pending: <strong>{{ $pendingCount }}</strong></span>
        </div>
    @endif

    {{-- Task list --}}
    @forelse($tasks as $task)
        <a href="{{ route('staff.task-detail', ['taskId' => $task->id, 'taskType' => $task->task_type]) }}" class="tl-task-card">
            <div class="icon {{ $task->task_type }}">
                <i class="fa-solid {{ $task->task_type === 'cleaning' ? 'fa-bed' : 'fa-wrench' }}"></i>
            </div>
            <div class="info">
                <div class="room">
                    @if($task->task_type === 'cleaning')
                        Room {{ $task->room_no }}
                    @else
                        Task #{{ $task->id }}
                    @endif
                </div>
                <div class="detail">
                    {{ $task->room_name ?? $task->task_subtype ?? $task->task_type }}
                    @if($task->starttime) · Started {{ $task->starttime }}@endif
                    @if($task->description) · {{ Str::limit($task->description, 40) }}@endif
                </div>
            </div>
            @php
                $statusClass = match($task->status) {
                    'In Progress' => 'in-progress',
                    'Completed' => 'completed',
                    'dirty' => 'dirty',
                    default => 'pending'
                };
            @endphp
            <span class="tl-badge {{ $statusClass }}">{{ $task->status }}</span>
        </a>
    @empty
        <div class="tl-empty">
            <i class="fa-solid fa-clipboard-check" style="font-size:48px;color:#e2e8f0;"></i>
            <h6 style="margin-top:12px;">No tasks found</h6>
            <p style="font-size:13px;">@if($staffId) No tasks assigned for this filter. @else Select a staff member first. @endif</p>
        </div>
    @endforelse
</div>
@endsection
