@extends('property.layouts.property')
@section('content')
<div class="nk-block">
    <h4 style="font-weight:800;color:#1e293b;margin-bottom:20px;"><i class="fa-solid fa-triangle-exclamation" style="color:#f59e0b;margin-right:8px;"></i> Member Bill Missing Report</h4>
    <form class="d-flex gap-3 mb-4" method="GET">
        <div><label class="form-label" style="font-size:12px;font-weight:600;">From</label><input type="date" name="fromdate" value="{{ $fromdate }}" class="form-control form-control-sm" style="border-radius:8px;"></div>
        <div><label class="form-label" style="font-size:12px;font-weight:600;">To</label><input type="date" name="todate" value="{{ $todate }}" class="form-control form-control-sm" style="border-radius:8px;"></div>
        <div class="d-flex align-items-end"><button type="submit" class="btn btn-sm btn-primary" style="border-radius:8px;">View</button></div>
    </form>
    <div class="card" style="border-radius:14px;border:1px solid #e8ecf1;">
        <div style="overflow-x:auto;">
            <table class="table table-sm" style="font-size:12px;">
                <thead><tr style="background:#f8fafc;"><th>#</th><th>Member Code</th><th>Member Name</th><th>Address</th><th>City</th><th>Mobile</th></tr></thead>
                <tbody>
                @forelse($missingBills as $mb)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $mb->sub_code }}</td>
                        <td><strong>{{ $mb->name }}</strong></td>
                        <td>{{ $mb->address1 ?? '—' }}</td>
                        <td>{{ $mb->city ?? '—' }}</td>
                        <td>{{ $mb->mobile ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted" style="padding:30px;">All members have bills generated ✅</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
