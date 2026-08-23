@extends('property.layouts.property')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
    .bi-card {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        transition: box-shadow 0.2s;
    }
    .bi-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
    .bi-card h6 { font-weight: 700; color: #1e293b; margin-bottom: 16px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
    .bi-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .bi-kpi {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 14px;
        padding: 20px 24px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .bi-kpi::after {
        content: '';
        position: absolute;
        top: -20px; right: -20px;
        width: 80px; height: 80px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .bi-kpi.kpi-green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .bi-kpi.kpi-blue { background: linear-gradient(135deg, #2196F3 0%, #21CBF3 100%); }
    .bi-kpi.kpi-orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .bi-kpi.kpi-purple { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .bi-kpi .kpi-label { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.85; margin-bottom: 6px; }
    .bi-kpi .kpi-value { font-size: 28px; font-weight: 800; line-height: 1.1; }
    .bi-kpi .kpi-sub { font-size: 12px; opacity: 0.75; margin-top: 4px; }
    .chart-wrap { position: relative; height: 300px; }
    .chart-wrap canvas { width: 100% !important; }
    .table-bi { font-size: 13px; }
    .table-bi th { background: #f1f5f9; font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: #475569; border: none; }
    .table-bi td { border-color: #f1f5f9; padding: 10px 12px; }
    .badge-revenue { background: #dbeafe; color: #1d4ed8; padding: 3px 10px; border-radius: 20px; font-weight: 600; font-size: 11px; }
    .filter-bar {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .filter-bar label { font-size: 12px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; }
    .filter-bar input, .filter-bar select {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
        background: #fff;
    }
    .filter-bar .btn-apply {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border: none;
        padding: 8px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
    }
    .section-divider {
        border: none;
        border-top: 1px dashed #e2e8f0;
        margin: 12px 0 20px;
    }
    .perf-bar {
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
    }
    .perf-bar .fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.5s ease;
    }
</style>

<div class="nk-block">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 style="font-weight:800;color:#1e293b;margin:0;">
                <i class="fa-solid fa-chart-line" style="color:#667eea;margin-right:8px;"></i>
                Analytics Dashboard
            </h4>
            <small style="color:#94a3b8;">Business Intelligence & Performance Insights</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('analytics.report-builder') }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Report Builder
            </a>
            <a href="{{ route('analytics.saved-reports') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                <i class="fa-solid fa-bookmark"></i> Saved Reports
            </a>
        </div>
    </div>

    {{-- ═══ FILTER BAR ═══ --}}
    <form class="filter-bar" id="filterForm" method="GET">
        <div>
            <label>From</label>
            <input type="date" name="fromdate" value="{{ $fromdate }}">
        </div>
        <div>
            <label>To</label>
            <input type="date" name="todate" value="{{ $todate }}">
        </div>
        <div>
            <label>Period</label>
            <select name="period">
                <option value="7" {{ $period == '7' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="30" {{ $period == '30' ? 'selected' : '' }}>Last 30 Days</option>
                <option value="90" {{ $period == '90' ? 'selected' : '' }}>Last 90 Days</option>
                <option value="180" {{ $period == '180' ? 'selected' : '' }}>Last 6 Months</option>
                <option value="365" {{ $period == '365' ? 'selected' : '' }}>Last 1 Year</option>
            </select>
        </div>
        <button type="submit" class="btn-apply"><i class="fa-solid fa-magnifying-glass"></i> Apply</button>
        <button type="button" class="btn btn-sm btn-outline-success" onclick="refreshData()" style="border-radius:8px;">
            <i class="fa-solid fa-rotate"></i> Refresh
        </button>
    </form>

    {{-- ═══ KPI SUMMARY ═══ --}}
    <div class="bi-kpi-grid">
        <div class="bi-kpi">
            <div class="kpi-label">Total Rooms</div>
            <div class="kpi-value">{{ number_format($kpi['total_rooms']) }}</div>
            <div class="kpi-sub">{{ $kpi['occupied'] }} occupied</div>
        </div>
        <div class="bi-kpi kpi-green">
            <div class="kpi-label">Occupancy</div>
            <div class="kpi-value">{{ $kpi['occupancy_pct'] }}%</div>
            <div class="kpi-sub">{{ $kpi['checkins'] }} check-ins in period</div>
        </div>
        <div class="bi-kpi kpi-blue">
            <div class="kpi-label">Total Revenue</div>
            <div class="kpi-value">₹{{ number_format($kpi['total_revenue']) }}</div>
            <div class="kpi-sub">Room + POS + Banquet</div>
        </div>
        <div class="bi-kpi kpi-orange">
            <div class="kpi-label">ADR</div>
            <div class="kpi-value">₹{{ number_format($kpi['adr']) }}</div>
            <div class="kpi-sub">Avg Daily Rate</div>
        </div>
        <div class="bi-kpi kpi-purple">
            <div class="kpi-label">RevPAR</div>
            <div class="kpi-value">₹{{ number_format($kpi['revpar']) }}</div>
            <div class="kpi-sub">Revenue Per Available Room</div>
        </div>
        <div class="bi-kpi" style="background:linear-gradient(135deg,#f5af19 0%,#f12711 100%);">
            <div class="kpi-label">Payments Received</div>
            <div class="kpi-value">₹{{ number_format($kpi['payments_received']) }}</div>
            <div class="kpi-sub">Cash/Card/UPI</div>
        </div>
        <div class="bi-kpi" style="background:linear-gradient(135deg,#a18cd1 0%,#fbc2eb 100%);">
            <div class="kpi-label">Outstanding</div>
            <div class="kpi-value">₹{{ number_format($kpi['outstanding']) }}</div>
            <div class="kpi-sub">Unsettled balance</div>
        </div>
        <div class="bi-kpi" style="background:linear-gradient(135deg,#89f7fe 0%,#66a6ff 100%);">
            <div class="kpi-label">Checkouts</div>
            <div class="kpi-value">{{ number_format($kpi['checkouts']) }}</div>
            <div class="kpi-sub">{{ $kpi['days'] }} days in period</div>
        </div>
    </div>

    {{-- ═══ ROW 1: Revenue Trend + Occupancy Trend ═══ --}}
    <div class="row">
        <div class="col-xl-8">
            <div class="bi-card">
                <h6><i class="fa-solid fa-chart-area" style="color:#667eea;margin-right:6px;"></i> Revenue Trend</h6>
                <div class="chart-wrap"><canvas id="revenueTrendChart"></canvas></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="bi-card">
                <h6><i class="fa-solid fa-chart-pie" style="color:#38bdf8;margin-right:6px;"></i> Revenue Sources</h6>
                <div class="chart-wrap"><canvas id="revenueSourcesChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- ═══ ROW 2: Occupancy + Room Performance ═══ --}}
    <div class="row">
        <div class="col-xl-8">
            <div class="bi-card">
                <h6><i class="fa-solid fa-bed" style="color:#f59e0b;margin-right:6px;"></i> Occupancy Trend</h6>
                <div class="chart-wrap"><canvas id="occupancyTrendChart"></canvas></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="bi-card">
                <h6><i class="fa-solid fa-building" style="color:#10b981;margin-right:6px;"></i> Room Type Performance</h6>
                <div style="max-height:300px;overflow-y:auto;">
                    <table class="table table-bi mb-0">
                        <thead><tr><th>Type</th><th class="text-right">Stays</th><th class="text-right">Avg Rate</th><th class="text-right">Revenue</th></tr></thead>
                        <tbody>
                        @forelse($roomPerformance as $rp)
                            <tr>
                                <td><strong>{{ $rp['category'] }}</strong></td>
                                <td class="text-right">{{ $rp['stays'] }}</td>
                                <td class="text-right">₹{{ number_format($rp['avg_rate']) }}</td>
                                <td class="text-right"><span class="badge-revenue">₹{{ number_format($rp['total_revenue']) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No data</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ ROW 3: POS + Day of Week ═══ --}}
    <div class="row">
        <div class="col-xl-6">
            <div class="bi-card">
                <h6><i class="fa-solid fa-utensils" style="color:#ef4444;margin-right:6px;"></i> POS / Restaurant Performance</h6>
                <div class="chart-wrap"><canvas id="posChart"></canvas></div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="bi-card">
                <h6><i class="fa-solid fa-calendar-week" style="color:#8b5cf6;margin-right:6px;"></i> Day of Week Pattern</h6>
                <div class="chart-wrap"><canvas id="dayOfWeekChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- ═══ ROW 4: Top Companies + Top Sources ═══ --}}
    <div class="row">
        <div class="col-xl-6">
            <div class="bi-card">
                <h6><i class="fa-solid fa-building" style="color:#0ea5e9;margin-right:6px;"></i> Top Companies</h6>
                <div style="max-height:280px;overflow-y:auto;">
                    <table class="table table-bi mb-0">
                        <thead><tr><th>Company Code</th><th class="text-right">Bookings</th><th class="text-right">Total Rate</th></tr></thead>
                        <tbody>
                        @forelse($topCompanies as $tc)
                            <tr>
                                <td><strong>{{ $tc->companycode }}</strong></td>
                                <td class="text-right">{{ $tc->bookings }}</td>
                                <td class="text-right">₹{{ number_format($tc->total_rate) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">No data</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="bi-card">
                <h6><i class="fa-solid fa-share-nodes" style="color:#06b6d4;margin-right:6px;"></i> Top Booking Sources</h6>
                <div style="max-height:280px;overflow-y:auto;">
                    <table class="table table-bi mb-0">
                        <thead><tr><th>Source</th><th class="text-right">Bookings</th><th class="text-right">Share</th></tr></thead>
                        <tbody>
                        @php $totalSrc = collect($topSources)->sum('bookings') ?: 1; @endphp
                        @forelse($topSources as $ts)
                            <tr>
                                <td><strong>{{ $ts->sourcedetails }}</strong></td>
                                <td class="text-right">{{ $ts->bookings }}</td>
                                <td class="text-right">
                                    {{ round(($ts->bookings / $totalSrc) * 100, 1) }}%
                                    <div class="perf-bar" style="width:60px;display:inline-block;vertical-align:middle;margin-left:6px;">
                                        <div class="fill" style="width:{{ round(($ts->bookings / $totalSrc) * 100) }}%;background:linear-gradient(90deg,#06b6d4,#3b82f6);"></div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">No data</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ ROW 5: Guest Demographics ═══ --}}
    <div class="row">
        <div class="col-xl-4">
            <div class="bi-card">
                <h6><i class="fa-solid fa-users" style="color:#ec4899;margin-right:6px;"></i> Guest Mix</h6>
                <div class="chart-wrap" style="height:200px;"><canvas id="guestMixChart"></canvas></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="bi-card">
                <h6><i class="fa-solid fa-globe" style="color:#14b8a6;margin-right:6px;"></i> Nationality</h6>
                <div class="chart-wrap" style="height:200px;"><canvas id="nationalityChart"></canvas></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="bi-card">
                <h6><i class="fa-solid fa-heart" style="color:#f43f5e;margin-right:6px;"></i> Guest Loyalty</h6>
                <div style="text-align:center;padding:20px 0;">
                    <div style="font-size:48px;font-weight:800;color:#667eea;">{{ $guestDemo['repeat_pct'] }}%</div>
                    <div style="font-size:13px;color:#64748b;margin-top:4px;">Repeat Guests</div>
                    <hr style="border-color:#f1f5f9;">
                    <div class="d-flex justify-content-around">
                        <div><div style="font-size:20px;font-weight:700;color:#10b981;">{{ $guestDemo['repeat_guests'] }}</div><div style="font-size:11px;color:#94a3b8;">Returning</div></div>
                        <div><div style="font-size:20px;font-weight:700;color:#f59e0b;">{{ $guestDemo['total_unique'] - $guestDemo['repeat_guests'] }}</div><div style="font-size:11px;color:#94a3b8;">New</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const COLORS = {
        primary: '#667eea', secondary: '#764ba2', green: '#10b981', blue: '#3b82f6',
        orange: '#f59e0b', red: '#ef4444', purple: '#8b5cf6', cyan: '#06b6d4',
        pink: '#ec4899', teal: '#14b8a6', slate: '#64748b'
    };

    // ── Revenue Trend ──
    new Chart(document.getElementById('revenueTrendChart'), {
        type: 'line',
        data: {
            labels: @json(array_column($revenueTrend, 'date')),
            datasets: [
                { label: 'Room Revenue', data: @json(array_column($revenueTrend, 'room')), borderColor: COLORS.primary, backgroundColor: 'rgba(102,126,234,0.1)', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 2 },
                { label: 'POS Revenue', data: @json(array_column($revenueTrend, 'pos')), borderColor: COLORS.orange, backgroundColor: 'rgba(245,158,11,0.1)', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 2 },
                { label: 'Payments', data: @json(array_column($revenueTrend, 'payments')), borderColor: COLORS.green, borderDash: [5,5], tension: 0.4, borderWidth: 2, pointRadius: 0 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { usePointStyle: true, padding: 16, font: { size: 11 } } } },
            scales: { y: { ticks: { callback: v => '₹' + v.toLocaleString('en-IN') }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } }
        }
    });

    // ── Revenue Sources Doughnut ──
    new Chart(document.getElementById('revenueSourcesChart'), {
        type: 'doughnut',
        data: {
            labels: @json(array_column($revenueSources, 'source')),
            datasets: [{ data: @json(array_column($revenueSources, 'amount')), backgroundColor: [COLORS.primary, COLORS.orange, COLORS.purple], borderWidth: 0, hoverOffset: 8 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '65%',
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12, font: { size: 11 } } } }
        }
    });

    // ── Occupancy Trend ──
    new Chart(document.getElementById('occupancyTrendChart'), {
        type: 'bar',
        data: {
            labels: @json(array_column($occupancyTrend, 'date')),
            datasets: [
                { label: 'Occupied', data: @json(array_column($occupancyTrend, 'occupied')), backgroundColor: 'rgba(102,126,234,0.8)', borderRadius: 4, barPercentage: 0.7 },
                { label: 'Vacant', data: @json(array_column($occupancyTrend, 'vacant')), backgroundColor: 'rgba(226,232,240,0.8)', borderRadius: 4, barPercentage: 0.7 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { usePointStyle: true, padding: 16, font: { size: 11 } } } },
            scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, grid: { color: '#f1f5f9' } } }
        }
    });

    // ── POS Performance ──
    var posData = @json($posPerformance['by_outlet']);
    new Chart(document.getElementById('posChart'), {
        type: 'bar',
        data: {
            labels: posData.map(d => d.outlet),
            datasets: [
                { label: 'Revenue', data: posData.map(d => d.revenue), backgroundColor: 'rgba(239,68,68,0.8)', borderRadius: 4 },
                { label: 'Avg Bill', data: posData.map(d => d.avg_bill), backgroundColor: 'rgba(245,158,11,0.6)', borderRadius: 4 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false, indexAxis: 'y',
            plugins: { legend: { position: 'top', labels: { usePointStyle: true, font: { size: 11 } } } },
            scales: { x: { ticks: { callback: v => '₹' + v.toLocaleString('en-IN') }, grid: { color: '#f1f5f9' } }, y: { grid: { display: false } } }
        }
    });

    // ── Day of Week ──
    var dowData = @json($dayOfWeekPattern);
    new Chart(document.getElementById('dayOfWeekChart'), {
        type: 'bar',
        data: {
            labels: dowData.map(d => d.day_name ? d.day_name.substring(0,3) : ''),
            datasets: [{ label: 'Check-ins', data: dowData.map(d => d.checkins), backgroundColor: [COLORS.primary, COLORS.blue, COLORS.cyan, COLORS.teal, COLORS.green, COLORS.orange, COLORS.red], borderRadius: 6, barPercentage: 0.6 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } }
        }
    });

    // ── Guest Mix ──
    new Chart(document.getElementById('guestMixChart'), {
        type: 'doughnut',
        data: {
            labels: ['Adults', 'Children'],
            datasets: [{ data: [{{ $guestDemo['total_adults'] }}, {{ $guestDemo['total_children'] }}], backgroundColor: [COLORS.blue, COLORS.pink], borderWidth: 0 }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 11 } } } } }
    });

    // ── Nationality ──
    var natData = @json($guestDemo['nationality']);
    new Chart(document.getElementById('nationalityChart'), {
        type: 'doughnut',
        data: {
            labels: natData.map(d => d.label),
            datasets: [{ data: natData.map(d => d.value), backgroundColor: [COLORS.primary, COLORS.green, COLORS.orange, COLORS.purple, COLORS.cyan, COLORS.pink], borderWidth: 0 }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 11 } } } } }
    });
});

function refreshData() {
    var params = new URLSearchParams(new FormData(document.getElementById('filterForm')));
    window.location.href = '{{ route("analytics.bi-dashboard") }}?' + params.toString();
}
</script>
@endsection
