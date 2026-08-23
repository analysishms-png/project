@extends('property.layouts.property')
@section('content')
<div class="nk-block">
    <h4 style="font-weight:800;color:#1e293b;margin-bottom:20px;"><i class="fa-solid fa-door-open" style="color:#667eea;margin-right:8px;"></i> Member Visit Detail</h4>
    <form class="d-flex gap-3 mb-4" method="GET">
        <div><label class="form-label" style="font-size:12px;font-weight:600;">From</label><input type="date" name="fromdate" value="{{ $fromdate }}" class="form-control form-control-sm" style="border-radius:8px;"></div>
        <div><label class="form-label" style="font-size:12px;font-weight:600;">To</label><input type="date" name="todate" value="{{ $todate }}" class="form-control form-control-sm" style="border-radius:8px;"></div>
        <div><label class="form-label" style="font-size:12px;font-weight:600;">Member</label>
            <select name="member_code" class="form-select form-select-sm" style="border-radius:8px;min-width:200px;">
                <option value="">All Members</option>
                @foreach($members as $m)<option value="{{ $m->sub_code }}" {{ $memberCode == $m->sub_code ? 'selected' : '' }}>{{ $m->name }}</option>@endforeach
            </select>
        </div>
        <div class="d-flex align-items-end"><button type="submit" class="btn btn-sm btn-primary" style="border-radius:8px;">View</button></div>
    </form>
    <div class="card" style="border-radius:14px;border:1px solid #e8ecf1;">
        <div style="overflow-x:auto;">
            <table class="table table-sm" style="font-size:12px;">
                <thead><tr style="background:#f8fafc;"><th>Date</th><th>Member</th><th>Visit Type</th><th>Details</th><th class="text-right">Amount</th></tr></thead>
                <tbody>
                @forelse($visits as $v)
                    <tr>
                        <td>{{ date('d-M-Y', strtotime($v->vdate)) }}</td>
                        <td><strong>{{ $v->member_name ?? $v->memcode }}</strong></td>
                        <td>{{ $v->vtype ?? '—' }}</td>
                        <td>{{ $v->narration ?? '—' }}</td>
                        <td class="text-right">₹{{ number_format($v->amt ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted" style="padding:30px;">No visits found</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
