@extends('property.layouts.property')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
    .en-card { background: #fff; border: 1px solid #e8ecf1; border-radius: 14px; padding: 20px; margin-bottom: 16px; }
    .en-card h6 { font-weight: 700; font-size: 13px; text-transform: uppercase; color: #64748b; margin-bottom: 12px; }
    .en-kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 20px; }
    .en-kpi { border-radius: 14px; padding: 20px; color: #fff; }
    .en-kpi .val { font-size: 32px; font-weight: 800; }
    .en-kpi .lbl { font-size: 11px; text-transform: uppercase; opacity: 0.8; }
    .en-chart-wrap { height: 280px; position: relative; }
    .en-table { width: 100%; font-size: 12px; border-collapse: collapse; }
    .en-table th { background: #f8fafc; padding: 10px 12px; font-weight: 700; text-transform: uppercase; font-size: 10px; color: #64748b; }
    .en-table td { padding: 10px 12px; border-top: 1px solid #f1f5f9; }
    .en-bar { height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; display: inline-block; width: 80px; vertical-align: middle; margin-left: 6px; }
    .en-bar .fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, #667eea, #764ba2); }
</style>

<div class="nk-block">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 style="font-weight:800;color:#1e293b;margin:0;"><i class="fa-solid fa-bolt" style="color:#f59e0b;margin-right:8px;"></i> Energy Monitoring</h4>
        <form class="d-flex gap-2" method="GET">
            <input type="date" name="fromdate" value="{{ $fromdate }}" class="form-control form-control-sm" style="border-radius:8px;width:140px;">
            <input type="date" name="todate" value="{{ $todate }}" class="form-control form-control-sm" style="border-radius:8px;width:140px;">
            <button type="submit" class="btn btn-sm btn-primary" style="border-radius:8px;">Apply</button>
        </form>
    </div>

    <div class="en-kpi-grid">
        <div class="en-kpi" style="background:linear-gradient(135deg,#667eea,#764ba2);"><div class="val">{{ number_format($totalKwh, 1) }}</div><div class="lbl">Total kWh</div></div>
        <div class="en-kpi" style="background:linear-gradient(135deg,#f59e0b,#f97316);"><div class="val">₹{{ number_format($estimatedCost) }}</div><div class="lbl">Estimated Cost (@ ₹8/kWh)</div></div>
        <div class="en-kpi" style="background:linear-gradient(135deg,#10b981,#38ef7d);"><div class="val">{{ $totalByType->count() }}</div><div class="lbl">Device Types</div></div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="en-card"><h6><i class="fa-solid fa-chart-area" style="margin-right:4px;"></i> Consumption by Type</h6><div class="en-chart-wrap"><canvas id="typeChart"></canvas></div></div>
        </div>
        <div class="col-xl-4">
            <div class="en-card"><h6><i class="fa-solid fa-chart-pie" style="margin-right:4px;"></i> Breakdown</h6>
                <table class="en-table">
                    <thead><tr><th>Type</th><th class="text-right">kWh</th><th class="text-right">Events</th></tr></thead>
                    <tbody>
                    @php $maxKwh = max($totalByType->max('kwh') ?: 1, 1); @endphp
                    @foreach($totalByType as $t)
                        <tr>
                            <td><strong>{{ ucfirst($t->device_type ?? 'Unknown') }}</strong></td>
                            <td class="text-right">{{ number_format($t->kwh, 1) }}
                                <div class="en-bar"><div class="fill" style="width:{{ ($t->kwh / $maxKwh) * 100 }}%;"></div></div>
                            </td>
                            <td class="text-right">{{ $t->events }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="en-card"><h6><i class="fa-solid fa-door-open" style="margin-right:4px;"></i> Room-wise Consumption</h6>
                <table class="en-table">
                    <thead><tr><th>Room</th><th class="text-right">kWh</th></tr></thead>
                    <tbody>
                    @foreach($byRoom as $r)
                        <tr><td><strong>{{ $r->room_no ?? 'System' }}</strong></td><td class="text-right">{{ number_format($r->kwh, 1) }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="en-card"><h6><i class="fa-solid fa-clock" style="margin-right:4px;"></i> Peak Hours</h6><div class="en-chart-wrap"><canvas id="peakChart"></canvas></div></div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var typeData = @json($totalByType);
    new Chart(document.getElementById('typeChart'), {
        type: 'bar',
        data: {
            labels: typeData.map(d => (d.device_type || 'Unknown').toUpperCase()),
            datasets: [{ label: 'kWh', data: typeData.map(d => d.kwh), backgroundColor: ['#667eea','#f59e0b','#10b981','#ef4444','#8b5cf6','#06b6d4','#ec4899'], borderRadius: 6 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
    });

    var peakData = @json($peakHours);
    new Chart(document.getElementById('peakChart'), {
        type: 'line',
        data: {
            labels: peakData.map(d => d.hour + ':00'),
            datasets: [{ label: 'kWh', data: peakData.map(d => d.kwh), borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.1)', fill: true, tension: 0.4, borderWidth: 2 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
    });
});
</script>
@endsection
