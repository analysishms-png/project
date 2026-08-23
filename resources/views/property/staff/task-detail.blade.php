@extends('property.layouts.property')

@section('content')
<style>
    .td-app { max-width: 480px; margin: 0 auto; }
    .td-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 16px;
    }
    .td-header h5 { font-weight: 800; margin: 0 0 4px; font-size: 16px; }
    .td-header .subtitle { opacity: 0.7; font-size: 12px; }
    .td-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 16px;
    }
    .td-card h6 { font-weight: 700; font-size: 13px; text-transform: uppercase; color: #64748b; margin-bottom: 12px; letter-spacing: 0.5px; }
    .td-detail-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
    }
    .td-detail-row:last-child { border-bottom: none; }
    .td-detail-row .label { color: #94a3b8; }
    .td-detail-row .value { font-weight: 600; color: #1e293b; }
    .td-status-current {
        text-align: center;
        padding: 12px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 14px;
        margin-bottom: 16px;
    }
    .td-status-current.in-progress { background: #fef3c7; color: #d97706; }
    .td-status-current.completed { background: #dcfce7; color: #16a34a; }
    .td-status-current.pending { background: #f1f5f9; color: #64748b; }
    .td-status-current.on-hold { background: #fee2e2; color: #dc2626; }
    .td-action-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-bottom: 16px;
    }
    .td-action-btn {
        padding: 14px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        text-align: center;
        cursor: pointer;
        font-weight: 700;
        font-size: 13px;
        transition: all 0.2s;
        background: #fff;
        color: #1e293b;
    }
    .td-action-btn:hover { border-color: #667eea; }
    .td-action-btn.start { border-color: #f59e0b; color: #d97706; }
    .td-action-btn.start:hover { background: #fef3c7; }
    .td-action-btn.complete { border-color: #10b981; color: #059669; }
    .td-action-btn.complete:hover { background: #dcfce7; }
    .td-action-btn.hold { border-color: #ef4444; color: #dc2626; }
    .td-action-btn.hold:hover { background: #fee2e2; }
    .td-action-btn i { display: block; font-size: 22px; margin-bottom: 6px; }
    .td-checklist-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .td-checklist-item:last-child { border-bottom: none; }
    .td-checklist-item input[type="checkbox"] {
        width: 20px; height: 20px;
        accent-color: #10b981;
        flex-shrink: 0;
    }
    .td-checklist-item label { font-size: 13px; cursor: pointer; flex: 1; }
    .td-checklist-item input.remark-input {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 12px;
        margin-top: 4px;
    }
    .td-notes-area {
        width: 100%;
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 13px;
        resize: vertical;
        min-height: 60px;
        font-family: inherit;
    }
    .td-save-btn {
        width: 100%;
        padding: 14px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .td-save-btn:active { transform: scale(0.98); }
    .td-gps-info {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background: #f0fdf4;
        border-radius: 10px;
        font-size: 12px;
        color: #16a34a;
        margin-bottom: 16px;
    }
</style>

<div class="td-app">
    {{-- HEADER --}}
    <div class="td-header">
        <div style="display:flex;justify-content:space-between;align-items:start;">
            <div>
                <h5>
                    @if($taskType === 'cleaning')
                        <i class="fa-solid fa-bed" style="margin-right:6px;"></i> Room {{ $task->roommo ?? $task->room_no ?? '' }}
                    @else
                        <i class="fa-solid fa-wrench" style="margin-right:6px;"></i> Task #{{ $task->sn ?? $task->id ?? '' }}
                    @endif
                </h5>
                <div class="subtitle">{{ $taskType === 'cleaning' ? 'Cleaning Task' : 'Maintenance Task' }}</div>
            </div>
            <a href="{{ route('staff.tasks', ['staff_id' => request('staff_id', session('staff_id', ''))]) }}" style="color:#fff;text-decoration:none;font-size:13px;">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    {{-- CURRENT STATUS --}}
    @php
        $statusVal = $taskType === 'cleaning' ? ($task->cleaningstatus ?? 'Pending') : ($task->status ?? 'Pending');
        $statusClass = match($statusVal) {
            'In Progress' => 'in-progress',
            'Completed' => 'completed',
            'On Hold' => 'on-hold',
            default => 'pending'
        };
    @endphp
    <div class="td-status-current {{ $statusClass }}">
        @if($statusVal === 'Completed')
            <i class="fa-solid fa-check-circle"></i>
        @elseif($statusVal === 'In Progress')
            <i class="fa-solid fa-spinner fa-spin"></i>
        @else
            <i class="fa-solid fa-clock"></i>
        @endif
        {{ $statusVal }}
    </div>

    {{-- TASK DETAILS --}}
    <div class="td-card">
        <h6><i class="fa-solid fa-circle-info" style="margin-right:4px;"></i> Task Details</h6>
        @if($taskType === 'cleaning')
            <div class="td-detail-row"><span class="label">Room</span><span class="value">{{ $task->roommo ?? '—' }}</span></div>
            <div class="td-detail-row"><span class="label">Room Name</span><span class="value">{{ $task->room_name ?? '—' }}</span></div>
            <div class="td-detail-row"><span class="label">Cleaning Type</span><span class="value">{{ $task->cleantype ?? '—' }}</span></div>
            <div class="td-detail-row"><span class="label">Cleaning #</span><span class="value">{{ $task->cleaningno ?? '—' }}</span></div>
            <div class="td-detail-row"><span class="label">Date</span><span class="value">{{ $task->cleaningdate ?? '—' }}</span></div>
            <div class="td-detail-row"><span class="label">Housekeeper</span><span class="value">{{ $task->hk_name ?? $task->housekeeperid ?? '—' }}</span></div>
            <div class="td-detail-row"><span class="label">Start Time</span><span class="value">{{ $task->starttime ?? '—' }}</span></div>
            <div class="td-detail-row"><span class="label">End Time</span><span class="value">{{ $task->endtime ?? '—' }}</span></div>
        @else
            <div class="td-detail-row"><span class="label">Room</span><span class="value">{{ $task->roomno ?? '—' }}</span></div>
            <div class="td-detail-row"><span class="label">Damage Type</span><span class="value">{{ $task->damage_type ?? '—' }}</span></div>
            <div class="td-detail-row"><span class="label">Date</span><span class="value">{{ $task->vdate ?? '—' }}</span></div>
            <div class="td-detail-row"><span class="label">Description</span><span class="value">{{ $task->description ?? '—' }}</span></div>
            <div class="td-detail-row"><span class="label">Assigned To</span><span class="value">{{ $task->assignedto ?? '—' }}</span></div>
        @endif
    </div>

    {{-- GPS INFO --}}
    <div class="td-gps-info" id="gpsInfo">
        <i class="fa-solid fa-location-dot"></i>
        <span id="gpsText">Getting location...</span>
    </div>

    {{-- ACTION BUTTONS --}}
    <div class="td-action-grid">
        <div class="td-action-btn start" onclick="updateStatus('In Progress')">
            <i class="fa-solid fa-play"></i> Start Task
        </div>
        <div class="td-action-btn complete" onclick="updateStatus('Completed')">
            <i class="fa-solid fa-check"></i> Complete
        </div>
        <div class="td-action-btn hold" onclick="updateStatus('On Hold')">
            <i class="fa-solid fa-pause"></i> On Hold
        </div>
        <div class="td-action-btn" onclick="updateStatus('Cancelled')">
            <i class="fa-solid fa-ban"></i> Cancel
        </div>
    </div>

    {{-- CHECKLIST (cleaning only) --}}
    @if($taskType === 'cleaning' && count($checklist) > 0)
        <div class="td-card">
            <h6><i class="fa-solid fa-list-check" style="margin-right:4px;"></i> Checklist</h6>
            @foreach($checklist as $item)
                <div class="td-checklist-item">
                    <input type="checkbox" id="cl{{ $loop->index }}" class="check-item"
                           value="{{ $item->itemname ?? $item->sno }}"
                           {{ in_array($item->itemname ?? $item->sno, $inspectionItems['existingChecklist'] ?? []) ? 'checked' : '' }}>
                    <label for="cl{{ $loop->index }}">{{ $item->itemname ?? 'Item ' . $item->sno }}</label>
                </div>
            @endforeach
            <button class="td-save-btn" style="margin-top:12px;" onclick="saveChecklist()">
                <i class="fa-solid fa-floppy-disk"></i> Save Checklist
            </button>
        </div>
    @endif

    {{-- AMENITIES (cleaning only) --}}
    @if($taskType === 'cleaning' && count($amenities) > 0)
        <div class="td-card">
            <h6><i class="fa-solid fa-box-open" style="margin-right:4px;"></i> Amenities Check</h6>
            @foreach($amenities as $am)
                <div class="td-checklist-item">
                    <input type="checkbox" id="am{{ $loop->index }}" class="amenity-item"
                           value="{{ $am->itemcode ?? $am->sn }}"
                           {{ in_array($am->itemcode ?? $am->sn, $inspectionItems['existingAmenities'] ?? []) ? 'checked' : '' }}>
                    <label for="am{{ $loop->index }}">{{ $am->itemname ?? $am->item }}</label>
                </div>
            @endforeach
        </div>
    @endif

    {{-- NOTES --}}
    <div class="td-card">
        <h6><i class="fa-solid fa-sticky-note" style="margin-right:4px;"></i> Notes</h6>
        <textarea class="td-notes-area" id="taskNotes" placeholder="Add notes about this task..."></textarea>
    </div>
</div>

@endsection

@section('scripts')
<script>
var currentLat = null, currentLng = null;
var taskId = '{{ $task->cleaningid ?? $task->sn ?? "" }}';
var taskType = '{{ $taskType }}';

// Get GPS on load
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(p) {
        currentLat = p.coords.latitude;
        currentLng = p.coords.longitude;
        document.getElementById('gpsText').textContent = 'GPS: ' + currentLat.toFixed(5) + ', ' + currentLng.toFixed(5);
    }, function() {
        document.getElementById('gpsText').textContent = 'GPS unavailable';
    });
}

function updateStatus(status) {
    if (!confirm('Update task to "' + status + '"?')) return;

    fetch('{{ route("staff.update-task-status") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            task_id: taskId,
            task_type: taskType,
            status: status,
            notes: document.getElementById('taskNotes').value,
            latitude: currentLat,
            longitude: currentLng
        })
    })
    .then(r => r.json())
    .then(function(res) {
        if (res.success) {
            // Update status display
            var el = document.querySelector('.td-status-current');
            el.textContent = status;
            el.className = 'td-status-current ' + status.toLowerCase().replace(' ', '-');
            alert(res.message);
            location.reload();
        } else {
            alert(res.message || 'Error updating task');
        }
    });
}

function saveChecklist() {
    var items = [];
    document.querySelectorAll('.check-item').forEach(function(cb) {
        items.push({ item: cb.value, status: cb.checked ? 'Y' : 'N', remark: '' });
    });

    fetch('{{ route("staff.save-checklist") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ cleaning_id: taskId, items: items })
    })
    .then(r => r.json())
    .then(function(res) {
        if (res.success) alert(res.message);
    });
}
</script>
@endsection
