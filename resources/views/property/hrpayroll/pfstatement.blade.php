@extends('property.layouts.property')

@section('content')
<div class="nk-block">
    <h4 style="font-weight:800;color:#1e293b;margin-bottom:20px;"><i class="fa-solid fa-file-invoice" style="color:#667eea;margin-right:8px;"></i> PF Statement — {{ date('F Y', strtotime($month . '-01')) }}</h4>

    <form class="d-flex gap-3 mb-4" method="GET">
        <div><label class="form-label" style="font-size:12px;font-weight:600;">Month</label><input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" style="border-radius:8px;"></div>
        <div class="d-flex align-items-end"><button type="submit" class="btn btn-sm btn-primary" style="border-radius:8px;">View</button></div>
    </form>

    <div class="row mb-3">
        <div class="col-md-4"><div class="card" style="border-radius:12px;padding:16px;text-align:center;border:1px solid #e8ecf1;"><div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Employee PF</div><div style="font-size:24px;font-weight:800;color:#667eea;">₹{{ number_format($totals['employee_pf']) }}</div></div></div>
        <div class="col-md-4"><div class="card" style="border-radius:12px;padding:16px;text-align:center;border:1px solid #e8ecf1;"><div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Employer PF</div><div style="font-size:24px;font-weight:800;color:#10b981;">₹{{ number_format($totals['employer_pf']) }}</div></div></div>
        <div class="col-md-4"><div class="card" style="border-radius:12px;padding:16px;text-align:center;border:1px solid #e8ecf1;"><div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Total PF</div><div style="font-size:24px;font-weight:800;color:#f59e0b;">₹{{ number_format($totals['total_pf']) }}</div></div></div>
    </div>

    <div class="card" style="border-radius:14px;border:1px solid #e8ecf1;">
        <div style="overflow-x:auto;">
            <table class="table table-sm" style="font-size:12px;">
                <thead><tr style="background:#f8fafc;"><th>#</th><th>Employee</th><th>Code</th><th class="text-right">Basic</th><th class="text-right">Employee PF</th><th class="text-right">Employer PF</th><th class="text-right">Total</th></tr></thead>
                <tbody>
                @forelse($pfData as $pf)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $pf->emp_name }}</strong></td>
                        <td>{{ $pf->empcode }}</td>
                        <td class="text-right">₹{{ number_format($pf->emp_basic, 0) }}</td>
                        <td class="text-right">₹{{ number_format($pf->pf, 0) }}</td>
                        <td class="text-right">₹{{ number_format($pf->epf, 0) }}</td>
                        <td class="text-right" style="font-weight:700;">₹{{ number_format($pf->pf + $pf->epf, 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted" style="padding:30px;">No PF records for {{ $month }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-center mt-3"><button onclick="window.print()" class="btn btn-sm btn-outline-primary" style="border-radius:8px;"><i class="fa-solid fa-print"></i> Print</button></div>
</div>
@endsection
