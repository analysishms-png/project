@extends('property.layouts.property')
@section('content')
<div class="nk-block">
    <h4 style="font-weight:800;color:#1e293b;margin-bottom:20px;"><i class="fa-solid fa-cake-candles" style="color:#667eea;margin-right:8px;"></i> Member Birth & Anniversary Details</h4>
    <form class="d-flex gap-3 mb-4" method="GET">
        <div><label class="form-label" style="font-size:12px;font-weight:600;">Month</label>
            <select name="month" class="form-select form-select-sm" style="border-radius:8px;">
                @foreach($months as $num => $name)<option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>@endforeach
            </select>
        </div>
        <div><label class="form-label" style="font-size:12px;font-weight:600;">Year</label><input type="number" name="year" value="{{ $year }}" class="form-control form-control-sm" style="border-radius:8px;width:80px;"></div>
        <div class="d-flex align-items-end"><button type="submit" class="btn btn-sm btn-primary" style="border-radius:8px;">View</button></div>
    </form>

    <div class="row">
        <div class="col-xl-6">
            <div class="card" style="border-radius:14px;border:1px solid #e8ecf1;">
                <div class="card-header" style="background:#fef3c7;border-bottom:1px solid #fde68a;"><h6 style="margin:0;font-weight:700;"><i class="fa-solid fa-cake-candles" style="margin-right:4px;"></i> Birthdays ({{ $birthdays->count() }})</h6></div>
                <div class="card-body" style="max-height:400px;overflow-y:auto;">
                    @forelse($birthdays as $b)
                        <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                            <div style="width:40px;height:40px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;font-size:16px;">🎂</div>
                            <div><strong>{{ $b->name }}</strong><div style="font-size:11px;color:#94a3b8;">{{ $b->member_name ?? '' }} · {{ date('d-M', strtotime($b->dob)) }}</div></div>
                        </div>
                    @empty
                        <div class="text-center text-muted" style="padding:20px;">No birthdays this month</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card" style="border-radius:14px;border:1px solid #e8ecf1;">
                <div class="card-header" style="background:#fce7f3;border-bottom:1px solid #fbcfe8;"><h6 style="margin:0;font-weight:700;"><i class="fa-solid fa-heart" style="margin-right:4px;"></i> Anniversaries ({{ $anniversaries->count() }})</h6></div>
                <div class="card-body" style="max-height:400px;overflow-y:auto;">
                    @forelse($anniversaries as $a)
                        <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                            <div style="width:40px;height:40px;border-radius:50%;background:#fce7f3;display:flex;align-items:center;justify-content:center;font-size:16px;">💍</div>
                            <div><strong>{{ $a->name }}</strong><div style="font-size:11px;color:#94a3b8;">{{ $a->member_name ?? '' }} · {{ date('d-M', strtotime($a->weddate)) }}</div></div>
                        </div>
                    @empty
                        <div class="text-center text-muted" style="padding:20px;">No anniversaries this month</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
