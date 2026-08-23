@extends('property.layouts.property')
@section('content')
<div class="nk-block">
    <h4 style="font-weight:800;color:#1e293b;margin-bottom:20px;"><i class="fa-solid fa-percent" style="color:#667eea;margin-right:8px;"></i> Member Tax Report</h4>
    <form class="d-flex gap-3 mb-4" method="GET">
        <div><label class="form-label" style="font-size:12px;font-weight:600;">From</label><input type="date" name="fromdate" value="{{ $fromdate }}" class="form-control form-control-sm" style="border-radius:8px;"></div>
        <div><label class="form-label" style="font-size:12px;font-weight:600;">To</label><input type="date" name="todate" value="{{ $todate }}" class="form-control form-control-sm" style="border-radius:8px;"></div>
        <div class="d-flex align-items-end"><button type="submit" class="btn btn-sm btn-primary" style="border-radius:8px;">View</button></div>
    </form>
    <div class="card" style="border-radius:14px;border:1px solid #e8ecf1;">
        <div style="overflow-x:auto;">
            <table class="table table-sm" style="font-size:12px;">
                <thead><tr style="background:#f8fafc;"><th>#</th><th>Member</th><th class="text-right">Bills</th><th class="text-right">Net Amount</th><th class="text-right">Tax Amount</th><th class="text-right">Discount</th></tr></thead>
                <tbody>
                @forelse($taxData as $t)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $t->member_name ?? $t->memcode }}</strong></td>
                        <td class="text-right">{{ $t->bill_count }}</td>
                        <td class="text-right">₹{{ number_format($t->net_amount, 2) }}</td>
                        <td class="text-right" style="color:#ef4444;font-weight:700;">₹{{ number_format($t->tax_amount, 2) }}</td>
                        <td class="text-right">₹{{ number_format($t->disc_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted" style="padding:30px;">No tax data found</td></tr>
                @endforelse
                </tbody>
                @if($taxData->count() > 0)
                <tfoot><tr style="font-weight:700;background:#f8fafc;border-top:2px solid #e2e8f0;">
                    <td colspan="3">TOTAL</td>
                    <td class="text-right">₹{{ number_format($taxData->sum('net_amount'), 2) }}</td>
                    <td class="text-right" style="color:#ef4444;">₹{{ number_format($taxData->sum('tax_amount'), 2) }}</td>
                    <td class="text-right">₹{{ number_format($taxData->sum('disc_amount'), 2) }}</td>
                </tr></tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
