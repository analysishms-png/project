@extends('property.layouts.property')
@section('content')
<div class="nk-block">
    <h4 style="font-weight:800;color:#1e293b;margin-bottom:20px;"><i class="fa-solid fa-receipt" style="color:#667eea;margin-right:8px;"></i> Member Sales Register</h4>
    <form class="d-flex gap-3 mb-4" method="GET">
        <div><label class="form-label" style="font-size:12px;font-weight:600;">From</label><input type="date" name="fromdate" value="{{ $fromdate }}" class="form-control form-control-sm" style="border-radius:8px;"></div>
        <div><label class="form-label" style="font-size:12px;font-weight:600;">To</label><input type="date" name="todate" value="{{ $todate }}" class="form-control form-control-sm" style="border-radius:8px;"></div>
        <div class="d-flex align-items-end"><button type="submit" class="btn btn-sm btn-primary" style="border-radius:8px;">View</button></div>
    </form>
    <div class="card" style="border-radius:14px;border:1px solid #e8ecf1;">
        <div style="overflow-x:auto;">
            <table class="table table-sm" style="font-size:12px;">
                <thead><tr style="background:#f8fafc;"><th>#</th><th>Member Code</th><th>Member Name</th><th class="text-right">Txns</th><th class="text-right">Total Debit</th><th class="text-right">Total Credit</th><th class="text-right">Net Amount</th></tr></thead>
                <tbody>
                @forelse($sales as $s)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $s->accode }}</td>
                        <td><strong>{{ $s->member_name }}</strong></td>
                        <td class="text-right">{{ $s->txn_count }}</td>
                        <td class="text-right">₹{{ number_format($s->total_debit, 2) }}</td>
                        <td class="text-right">₹{{ number_format($s->total_credit, 2) }}</td>
                        <td class="text-right" style="font-weight:700;color:{{ $s->net_amount > 0 ? '#ef4444' : '#10b981' }};">₹{{ number_format(abs($s->net_amount), 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted" style="padding:30px;">No sales found</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
