@extends('property.layouts.property')

@section('content')
<div class="nk-block">
    <h4 style="font-weight:800;color:#1e293b;margin-bottom:20px;"><i class="fa-solid fa-receipt" style="color:#667eea;margin-right:8px;"></i> Pay Slip</h4>

    <form class="d-flex gap-3 mb-4" method="GET">
        <div>
            <label class="form-label" style="font-size:12px;font-weight:600;">Month</label>
            <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" style="border-radius:8px;">
        </div>
        <div>
            <label class="form-label" style="font-size:12px;font-weight:600;">Employee</label>
            <select name="emp_code" class="form-select form-select-sm" style="border-radius:8px;min-width:200px;">
                <option value="">Select Employee</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->empcode }}" {{ $empCode == $emp->empcode ? 'selected' : '' }}>{{ $emp->name }} ({{ $emp->empcode }})</option>
                @endforeach
            </select>
        </div>
        <div class="d-flex align-items-end">
            <button type="submit" class="btn btn-sm btn-primary" style="border-radius:8px;">Generate</button>
        </div>
    </form>

    @if($salary)
    <div class="card" style="border-radius:14px;border:2px solid #e2e8f0;max-width:600px;margin:0 auto;">
        <div class="card-body" id="payslipContent">
            <div style="text-align:center;border-bottom:2px solid #667eea;padding-bottom:16px;margin-bottom:16px;">
                <h5 style="font-weight:800;color:#1e293b;">{{ $company->comp_name ?? 'Hotel' }}</h5>
                <div style="font-size:12px;color:#94a3b8;">{{ $company->address1 ?? '' }}, {{ $company->city ?? '' }}</div>
                <h6 style="font-weight:700;margin-top:12px;color:#667eea;">PAY SLIP — {{ date('F Y', strtotime($month . '-01')) }}</h6>
            </div>

            <div class="row mb-3" style="font-size:13px;">
                <div class="col-6"><strong>Employee:</strong> {{ $salary->emp_name }}</div>
                <div class="col-6"><strong>Code:</strong> {{ $salary->emp_code }}</div>
                <div class="col-6"><strong>Designation:</strong> {{ $salary->desig_name ?? '—' }}</div>
                <div class="col-6"><strong>Department:</strong> {{ $salary->dept_name ?? '—' }}</div>
                <div class="col-6"><strong>Working Days:</strong> {{ $salary->work_day }}</div>
            </div>

            <table class="table table-sm" style="font-size:12px;">
                <thead><tr><th>EARNINGS</th><th class="text-right">₹</th><th>DEDUCTIONS</th><th class="text-right">₹</th></tr></thead>
                <tbody>
                    <tr><td>Basic</td><td class="text-right">{{ number_format($salary->Basic, 2) }}</td><td>PF</td><td class="text-right">{{ number_format($salary->pf, 2) }}</td></tr>
                    <tr><td>DA</td><td class="text-right">{{ number_format($salary->da, 2) }}</td><td>ESI</td><td class="text-right">{{ number_format($salary->esi, 2) }}</td></tr>
                    <tr><td>HRA</td><td class="text-right">{{ number_format($salary->hra, 2) }}</td><td>Loan</td><td class="text-right">{{ number_format($salary->loan, 2) }}</td></tr>
                    <tr><td>Conveyance</td><td class="text-right">{{ number_format($salary->conveyance, 2) }}</td><td>Advance</td><td class="text-right">{{ number_format($salary->advance, 2) }}</td></tr>
                    <tr><td>Medical</td><td class="text-right">{{ number_format($salary->medical, 2) }}</td><td></td><td></td></tr>
                    <tr><td>LTA</td><td class="text-right">{{ number_format($salary->lta, 2) }}</td><td></td><td></td></tr>
                    <tr><td>Other Allow</td><td class="text-right">{{ number_format($salary->other_allow, 2) }}</td><td></td><td></td></tr>
                    <tr style="font-weight:700;border-top:2px solid #e2e8f0;">
                        <td>Total Earnings</td>
                        <td class="text-right">{{ number_format($salary->Basic + $salary->da + $salary->hra + $salary->conveyance + $salary->medical + $salary->lta + $salary->other_allow, 2) }}</td>
                        <td>Total Deductions</td>
                        <td class="text-right">{{ number_format($salary->pf + $salary->esi + $salary->loan + $salary->advance, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div style="text-align:center;font-size:18px;font-weight:800;color:#10b981;margin-top:16px;">
                NET SALARY: ₹{{ number_format($salary->net_salary, 2) }}
            </div>
        </div>
    </div>
    <div class="text-center mt-3">
        <button onclick="window.print()" class="btn btn-sm btn-outline-primary" style="border-radius:8px;"><i class="fa-solid fa-print"></i> Print Pay Slip</button>
    </div>
    @elseif($empCode)
        <div class="alert alert-warning" style="border-radius:10px;">No salary record found for this employee in {{ $month }}</div>
    @endif
</div>
@endsection
