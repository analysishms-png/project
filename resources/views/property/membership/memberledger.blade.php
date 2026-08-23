@extends('property.layouts.property')

@section('content')
<div class="nk-block">
    <h4 style="font-weight:800;color:#1e293b;margin-bottom:20px;"><i class="fa-solid fa-book" style="color:#667eea;margin-right:8px;"></i> Member Ledger</h4>

    <form class="d-flex gap-3 mb-4" method="GET">
        <div><label class="form-label" style="font-size:12px;font-weight:600;">From</label><input type="date" name="fromdate" value="{{ $fromdate }}" class="form-control form-control-sm" style="border-radius:8px;"></div>
        <div><label class="form-label" style="font-size:12px;font-weight:600;">To</label><input type="date" name="todate" value="{{ $todate }}" class="form-control form-control-sm" style="border-radius:8px;"></div>
        <div><label class="form-label" style="font-size:12px;font-weight:600;">Member</label>
            <select name="member_code" class="form-select form-select-sm" style="border-radius:8px;min-width:200px;">
                <option value="">Select Member</option>
                @foreach($members as $m)<option value="{{ $m->sub_code }}" {{ $memberCode == $m->sub_code ? 'selected' : '' }}>{{ $m->name }}</option>@endforeach
            </select>
        </div>
        <div class="d-flex align-items-end"><button type="submit" class="btn btn-sm btn-primary" style="border-radius:8px;">View</button></div>
    </form>

    @if($memberCode)
    <div class="card" style="border-radius:14px;border:1px solid #e8ecf1;">
        <div style="overflow-x:auto;">
            <table class="table table-sm" style="font-size:12px;">
                <thead><tr style="background:#f8fafc;"><th>Date</th><th>VType</th><th>VNo</th><th>Narration</th><th>Pay Mode</th><th class="text-right">Debit</th><th class="text-right">Credit</th><th class="text-right">Balance</th></tr></thead>
                <tbody>
                @php $running = 0; @endphp
                @forelse($ledger as $l)
                    @php $running += $l->dramt - $l->cramt; @endphp
                    <tr>
                        <td>{{ date('d-M-Y', strtotime($l->vdate)) }}</td>
                        <td>{{ $l->vtype }}</td>
                        <td>{{ $l->vno }}</td>
                        <td>{{ $l->narration ?? '—' }}</td>
                        <td>{{ $l->paymode ?? '—' }}</td>
                        <td class="text-right">{{ $l->dramt > 0 ? number_format($l->dramt, 2) : '—' }}</td>
                        <td class="text-right">{{ $l->cramt > 0 ? number_format($l->cramt, 2) : '—' }}</td>
                        <td class="text-right" style="font-weight:700;color:{{ $running >= 0 ? '#ef4444' : '#10b981' }};">₹{{ number_format(abs($running), 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted" style="padding:30px;">No transactions found</td></tr>
                @endforelse
                </tbody>
                @if($ledger->count() > 0)
                <tfoot><tr style="font-weight:700;background:#f8fafc;border-top:2px solid #e2e8f0;">
                    <td colspan="5">TOTAL</td>
                    <td class="text-right">₹{{ number_format($ledger->sum('dramt'), 2) }}</td>
                    <td class="text-right">₹{{ number_format($ledger->sum('cramt'), 2) }}</td>
                    <td class="text-right" style="color:#ef4444;">₹{{ number_format(abs($balance), 2) }}</td>
                </tr></tfoot>
                @endif
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
