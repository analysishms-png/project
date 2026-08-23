@extends('property.layouts.property')

@section('content')
<div class="nk-block">
    <h4 style="font-weight:800;color:#1e293b;margin-bottom:20px;"><i class="fa-solid fa-table" style="color:#667eea;margin-right:8px;"></i> Payroll Register — {{ date('F Y', strtotime($month . '-01')) }}</h4>

    <form class="d-flex gap-3 mb-4" method="GET">
        <div><label class="form-label" style="font-size:12px;font-weight:600;">Month</label><input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" style="border-radius:8px;"></div>
        <div><label class="form-label" style="font-size:12px;font-weight:600;">Department</label>
            <select name="department" class="form-select form-select-sm" style="border-radius:8px;">
                <option value="">All</option>
                @foreach($departments as $d)<option value="{{ $d->dcode }}" {{ $department == $d->dcode ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach
            </select>
        </div>
        <div class="d-flex align-items-end"><button type="submit" class="btn btn-sm btn-primary" style="border-radius:8px;">View</button></div>
    </form>

    <div class="card" style="border-radius:14px;border:1px solid #e8ecf1;">
        <div style="overflow-x:auto;">
            <table class="table table-sm" style="font-size:12px;">
                <thead><tr style="background:#f8fafc;">
                    <th>#</th><th>Employee</th><th>Code</th><th>Days</th>
                    <th class="text-right">Basic</th><th class="text-right">DA</th><th class="text-right">HRA</th>
                    <th class="text-right">Conv</th><th class="text-right">Medical</th><th class="text-right">LTA</th>
                    <th class="text-right">PF</th><th class="text-right">ESI</th><th class="text-right">Loan</th>
                    <th class="text-right">Net Salary</th>
                </tr></thead>
                <tbody>
                @forelse($registers as $r)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $r->emp_name ?? $r->emp_code }}</strong></td>
                        <td>{{ $r->emp_code }}</td>
                        <td>{{ $r->work_day }}</td>
                        <td class="text-right">{{ number_format($r->Basic, 0) }}</td>
                        <td class="text-right">{{ number_format($r->da, 0) }}</td>
                        <td class="text-right">{{ number_format($r->hra, 0) }}</td>
                        <td class="text-right">{{ number_format($r->conveyance, 0) }}</td>
                        <td class="text-right">{{ number_format($r->medical, 0) }}</td>
                        <td class="text-right">{{ number_format($r->lta, 0) }}</td>
                        <td class="text-right">{{ number_format($r->pf, 0) }}</td>
                        <td class="text-right">{{ number_format($r->esi, 0) }}</td>
                        <td class="text-right">{{ number_format($r->loan, 0) }}</td>
                        <td class="text-right" style="font-weight:700;color:#10b981;">₹{{ number_format($r->net_salary, 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="14" class="text-center text-muted" style="padding:30px;">No salary records for {{ $month }}</td></tr>
                @endforelse
                </tbody>
                @if($registers->count() > 0)
                <tfoot><tr style="font-weight:700;background:#f8fafc;border-top:2px solid #e2e8f0;">
                    <td colspan="4">TOTAL ({{ $registers->count() }} employees)</td>
                    <td class="text-right">₹{{ number_format($totals['basic'], 0) }}</td>
                    <td class="text-right">₹{{ number_format($totals['da'], 0) }}</td>
                    <td class="text-right">₹{{ number_format($totals['hra'], 0) }}</td>
                    <td class="text-right">₹{{ number_format($totals['conveyance'], 0) }}</td>
                    <td class="text-right">₹{{ number_format($totals['medical'], 0) }}</td>
                    <td class="text-right">₹{{ number_format($totals['lta'], 0) }}</td>
                    <td class="text-right">₹{{ number_format($totals['pf'], 0) }}</td>
                    <td class="text-right">₹{{ number_format($totals['esi'], 0) }}</td>
                    <td class="text-right">₹{{ number_format($totals['loan'], 0) }}</td>
                    <td class="text-right" style="color:#10b981;">₹{{ number_format($totals['net_salary'], 0) }}</td>
                </tr></tfoot>
                @endif
            </table>
        </div>
    </div>

    @if($registers->count() > 0)
    <div class="text-center mt-3">
        <button onclick="window.print()" class="btn btn-sm btn-outline-primary" style="border-radius:8px;"><i class="fa-solid fa-print"></i> Print</button>
    </div>
    @endif
</div>
@endsection
