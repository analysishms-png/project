@extends('property.layouts.property')

@section('content')
<div class="nk-block">
    <h4 style="font-weight:800;color:#1e293b;margin-bottom:20px;"><i class="fa-solid fa-hand-holding-dollar" style="color:#667eea;margin-right:8px;"></i> Gratuity Report</h4>

    <div class="card" style="border-radius:14px;border:1px solid #e8ecf1;">
        <div style="overflow-x:auto;">
            <table class="table table-sm" style="font-size:12px;">
                <thead><tr style="background:#f8fafc;"><th>#</th><th>Employee</th><th>Code</th><th>Designation</th><th>Department</th><th>Joining Date</th><th class="text-right">Basic</th><th class="text-right">Years</th><th class="text-right">Gratuity</th><th>Eligible</th></tr></thead>
                <tbody>
                @forelse($employees as $emp)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $emp['name'] }}</strong></td>
                        <td>{{ $emp['empcode'] }}</td>
                        <td>{{ $emp['designation'] ?? '—' }}</td>
                        <td>{{ $emp['department'] ?? '—' }}</td>
                        <td>{{ $emp['dateofjoining'] ? date('d-M-Y', strtotime($emp['dateofjoining'])) : '—' }}</td>
                        <td class="text-right">₹{{ number_format($emp['basic'], 0) }}</td>
                        <td class="text-right">{{ $emp['years_of_service'] }} yrs</td>
                        <td class="text-right" style="font-weight:700;color:{{ $emp['eligible'] == 'Yes' ? '#10b981' : '#94a3b8' }};">₹{{ number_format($emp['gratuity_amount'], 0) }}</td>
                        <td><span class="badge" style="background:{{ $emp['eligible'] == 'Yes' ? '#dcfce7; color:#16a34a' : '#f1f5f9; color:#64748b' }};">{{ $emp['eligible'] }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted" style="padding:30px;">No employees found</td></tr>
                @endforelse
                </tbody>
                @if($employees->count() > 0)
                <tfoot><tr style="font-weight:700;background:#f8fafc;border-top:2px solid #e2e8f0;">
                    <td colspan="8">TOTAL ELIGIBLE GRATUITY</td>
                    <td class="text-right" style="color:#10b981;">₹{{ number_format($employees->where('eligible', 'Yes')->sum('gratuity_amount'), 0) }}</td>
                    <td></td>
                </tr></tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="text-center mt-3"><button onclick="window.print()" class="btn btn-sm btn-outline-primary" style="border-radius:8px;"><i class="fa-solid fa-print"></i> Print</button></div>
</div>
@endsection
