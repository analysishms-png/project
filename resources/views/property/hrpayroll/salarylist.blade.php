@extends('property.layouts.property')

@section('content')
<div class="nk-block">
    <h4 style="font-weight:800;color:#1e293b;margin-bottom:20px;"><i class="fa-solid fa-money-bill-wave" style="color:#667eea;margin-right:8px;"></i> Salary List — {{ date('F Y', strtotime($month . '-01')) }}</h4>

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
                <thead><tr style="background:#f8fafc;"><th>#</th><th>Employee</th><th>Code</th><th>Desig</th><th class="text-right">Days</th><th class="text-right">Gross</th><th class="text-right">Deductions</th><th class="text-right">Net Salary</th></tr></thead>
                <tbody>
                @forelse($salaries as $s)
                    @php
                        $gross = $s->Basic + $s->da + $s->hra + $s->conveyance + $s->medical + $s->lta + $s->other_allow;
                        $deductions = $s->pf + $s->esi + $s->loan + $s->advance;
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $s->emp_name ?? $s->emp_code }}</strong></td>
                        <td>{{ $s->emp_code }}</td>
                        <td>{{ $s->desig_name ?? '—' }}</td>
                        <td class="text-right">{{ $s->work_day }}</td>
                        <td class="text-right">₹{{ number_format($gross, 0) }}</td>
                        <td class="text-right" style="color:#ef4444;">₹{{ number_format($deductions, 0) }}</td>
                        <td class="text-right" style="font-weight:700;color:#10b981;">₹{{ number_format($s->net_salary, 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted" style="padding:30px;">No salary records found</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
