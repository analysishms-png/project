@extends('property.layouts.property')

@section('content')
<div class="nk-block">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 style="font-weight:800;color:#1e293b;margin:0;"><i class="fa-solid fa-clock" style="color:#667eea;margin-right:8px;"></i> Backup Schedule</h4>
        <a href="{{ route('backup.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>

    <div class="card" style="border-radius:14px;border:1px solid #e8ecf1;max-width:600px;">
        <div class="card-body">
            <form method="POST" action="{{ route('backup.save-schedule') }}">
                @csrf
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="enabled" value="1" {{ ($schedule['enabled'] ?? false) ? 'checked' : '' }} id="enabled">
                        <label class="form-check-label" for="enabled" style="font-weight:600;">Enable Automated Backup</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px;font-weight:600;">Frequency</label>
                    <select name="frequency" class="form-select" style="border-radius:8px;">
                        <option value="daily" {{ ($schedule['frequency'] ?? 'daily') == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ ($schedule['frequency'] ?? '') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ ($schedule['frequency'] ?? '') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px;font-weight:600;">Time</label>
                    <input type="time" name="time" value="{{ $schedule['time'] ?? '02:00' }}" class="form-control" style="border-radius:8px;">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px;font-weight:600;">Keep Backups (days)</label>
                    <input type="number" name="keep_days" value="{{ $schedule['keep_days'] ?? 30 }}" min="1" max="365" class="form-control" style="border-radius:8px;">
                    <small class="text-muted">Backups older than this will be automatically deleted</small>
                </div>
                @if($schedule['last_run'] ?? null)
                    <div class="alert alert-success" style="border-radius:8px;font-size:12px;">
                        <i class="fa-solid fa-check-circle"></i> Last run: {{ $schedule['last_run'] }}
                    </div>
                @endif
                <button type="submit" class="btn btn-primary" style="border-radius:10px;background:linear-gradient(135deg,#667eea,#764ba2);border:none;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Schedule
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
