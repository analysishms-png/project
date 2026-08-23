@extends('property.layouts.property')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
    .pr-app { max-width: 800px; margin: 0 auto; }
    .pr-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .pr-header h5 { font-weight: 800; margin: 0; font-size: 16px; }
    .pr-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 16px;
    }
    .pr-card h6 { font-weight: 700; font-size: 13px; text-transform: uppercase; color: #64748b; margin-bottom: 12px; letter-spacing: 0.5px; }
    .pr-table { width: 100%; font-size: 13px; border-collapse: collapse; }
    .pr-table th { background: #f8fafc; padding: 10px 12px; font-weight: 700; text-align: left; color: #64748b; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; }
    .pr-table td { padding: 10px 12px; border-top: 1px solid #f1f5f9; }
    .pr-table tr:hover td { background: #f8fafc; }
    .pr-progress {
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        width: 80px;
        display: inline-block;
        vertical-align: middle;
        margin-left: 6px;
    }
    .pr-progress .fill { height: 100%; border-radius: 4px; }
    .pr-chart-wrap { height: 250px; position: relative; }
    .pr-filter-bar {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
        align-items: end;
    }
    .pr-filter-bar input {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
    }
    .pr-filter-bar button {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        border: none;
        padding: 8px 20px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
    }
</style>

<div class="pr-app">
    <div class="pr-header">
        <h5><i class="fa-solid fa-chart-simple" style="margin-right:6px;"></i> Staff Productivity Report</h5>
        <a href="{{ route('staff.dashboard') }}" style="color:#fff;text-decoration:none;font-size:13px;"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>

    {{-- Filters --}}
    <form class="pr-filter-bar">
        <div>
            <label style="font-size:11px;font-weight:600;color:#64748b;display:block;margin-bottom:4px;">From</label>
            <input type="date" name="fromdate" value="{{ $fromdate }}">
        </div>
        <div>
            <label style="font-size:11px;font-weight:600;color:#64748b;display:block;margin-bottom:4px;">To</label>
            <input type="date" name="todate" value="{{ $todate }}">
        </div>
        <button type="submit">Apply</button>
    </form>

    {{-- Staff Productivity Table --}}
    <div class="pr-card">
        <h6><i class="fa-solid fa-users" style="margin-right:4px;"></i> Staff Performance</h6>
        <div style="overflow-x:auto;">
            <table class="pr-table">
                <thead>
                    <tr>
                        <th>Staff</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Completed</th>
                        <th class="text-right">In Progress</th>
                        <th class="text-right">Cancelled</th>
                        <th class="text-right">Completion %</th>
                        <th class="text-right">Avg Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productivity as $p)
                        @php
                            $completionPct = $p->total_tasks > 0 ? round(($p->completed / $p->total_tasks) * 100) : 0;
                            $barColor = $completionPct >= 80 ? '#10b981' : ($completionPct >= 50 ? '#f59e0b' : '#ef4444');
                        @endphp
                        <tr>
                            <td><strong>{{ $p->staff_name }}</strong></td>
                            <td class="text-right">{{ $p->total_tasks }}</td>
                            <td class="text-right" style="color:#10b981;font-weight:600;">{{ $p->completed }}</td>
                            <td class="text-right" style="color:#f59e0b;">{{ $p->in_progress }}</td>
                            <td class="text-right" style="color:#ef4444;">{{ $p->cancelled }}</td>
                            <td class="text-right">
                                {{ $completionPct }}%
                                <div class="pr-progress"><div class="fill" style="width:{{ $completionPct }}%;background:{{ $barColor }};"></div></div>
                            </td>
                            <td class="text-right">{{ $p->avg_mins ? $p->avg_mins . ' min' : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted" style="padding:30px;">No data for this period</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Daily Summary Chart --}}
    <div class="pr-card">
        <h6><i class="fa-solid fa-chart-bar" style="margin-right:4px;"></i> Daily Task Summary</h6>
        <div class="pr-chart-wrap"><canvas id="dailyChart"></canvas></div>
    </div>

    {{-- Check-in Summary --}}
    @if($checkinSummary->count() > 0)
    <div class="pr-card">
        <h6><i class="fa-solid fa-user-clock" style="margin-right:4px;"></i> Daily Attendance</h6>
        <table class="pr-table">
            <thead><tr><th>Date</th><th class="text-right">Staff Present</th><th class="text-right">Avg Hours</th></tr></thead>
            <tbody>
                @foreach($checkinSummary as $cs)
                    <tr>
                        <td>{{ date('M d, D', strtotime($cs->check_date)) }}</td>
                        <td class="text-right">{{ $cs->staff_count }}</td>
                        <td class="text-right">{{ round($cs->avg_hours / 60, 1) }}h</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var dailyData = @json($dailySummary);
    var dates = [...new Set(dailyData.map(d => d.cleaningdate))].sort();
    var statuses = ['Completed', 'In Progress', 'Cancelled'];
    var colors = { 'Completed': '#10b981', 'In Progress': '#f59e0b', 'Cancelled': '#ef4444' };

    var datasets = statuses.map(function(status) {
        return {
            label: status,
            data: dates.map(function(date) {
                var match = dailyData.find(d => d.cleaningdate === date && d.cleaningstatus === status);
                return match ? match.cnt : 0;
            }),
            backgroundColor: colors[status],
            borderRadius: 4,
        };
    });

    new Chart(document.getElementById('dailyChart'), {
        type: 'bar',
        data: { labels: dates.map(d => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })), datasets: datasets },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { usePointStyle: true, font: { size: 11 } } } },
            scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, grid: { color: '#f1f5f9' } } }
        }
    });
});
</script>
@endsection
