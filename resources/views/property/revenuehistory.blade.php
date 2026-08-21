@extends('property.layouts.property')
@section('content')

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- Header -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="page-title mb-1">
                                <i class="mdi mdi-history me-2"></i>Pricing History
                            </h4>
                            <p class="text-muted mb-0">Occupancy and AI-recommended rate trends</p>
                        </div>
                        <a href="{{ url('revenue') }}" class="btn btn-soft-primary btn-sm">
                            <i class="mdi mdi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-2">
                            <form method="GET" action="{{ url('revenue/history') }}" class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Room Category</label>
                                    <select class="form-select form-select-sm" name="category">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat->cat_code }}" {{ ($catCode ?? '') == $cat->cat_code ? 'selected' : '' }}>
                                            {{ $cat->name }} ({{ $cat->cat_code }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Days</label>
                                    <select class="form-select form-select-sm" name="days">
                                        <option value="7" {{ ($days ?? 30) == 7 ? 'selected' : '' }}>7 Days</option>
                                        <option value="14" {{ ($days ?? 30) == 14 ? 'selected' : '' }}>14 Days</option>
                                        <option value="30" {{ ($days ?? 30) == 30 ? 'selected' : '' }}>30 Days</option>
                                        <option value="90" {{ ($days ?? 30) == 90 ? 'selected' : '' }}>90 Days</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="mdi mdi-magnify me-1"></i>View
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0">
                            <h5 class="card-title mb-0">
                                <i class="mdi mdi-chart-line me-2"></i>Occupancy vs AI Rate
                                @if($catCode)
                                    <span class="badge badge-soft-primary ms-2">{{ $catCode }}</span>
                                @endif
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="historyChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0">
                            <h5 class="card-title mb-0"><i class="mdi mdi-table me-2"></i>Daily Data</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th class="text-center">Day</th>
                                            <th class="text-center">Occupancy %</th>
                                            <th class="text-right">AI Recommended Rate</th>
                                            <th>Trend</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($history as $h)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($h['date'])->format('d M Y') }}</td>
                                            <td class="text-center">{{ \Carbon\Carbon::parse($h['date'])->format('D') }}</td>
                                            <td class="text-center">
                                                @php $cls = $h['occupancy_pct'] >= 80 ? 'danger' : ($h['occupancy_pct'] >= 50 ? 'warning' : 'success'); @endphp
                                                <span class="badge badge-soft-{{ $cls }}">{{ $h['occupancy_pct'] }}%</span>
                                            </td>
                                            <td class="text-right fw-bold">₹{{ number_format($h['recommended_rate']) }}</td>
                                            <td>
                                                <div class="progress" style="height: 6px; width: 100px;">
                                                    <div class="progress-bar bg-{{ $cls }}" style="width: {{ $h['occupancy_pct'] }}%"></div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var historyData = {!! json_encode($history) !!};
    var ctx = document.getElementById('historyChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: historyData.map(d => d.date),
                datasets: [
                    {
                        label: 'Occupancy %',
                        data: historyData.map(d => d.occupancy_pct),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y',
                    },
                    {
                        label: 'AI Rate (₹)',
                        data: historyData.map(d => d.recommended_rate),
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245,158,11,0.1)',
                        fill: false,
                        tension: 0.4,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: { type: 'linear', position: 'left', min: 0, max: 100, ticks: { callback: v => v + '%' } },
                    y1: { type: 'linear', position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: v => '₹' + v } }
                }
            }
        });
    }
});
</script>
@endsection
