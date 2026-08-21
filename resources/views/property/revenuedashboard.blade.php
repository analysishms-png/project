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
                                <i class="mdi mdi-chart-line me-2"></i>Revenue Management
                            </h4>
                            <p class="text-muted mb-0">AI-powered dynamic pricing & revenue analytics</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ url('revenue/history') }}" class="btn btn-soft-info btn-sm">
                                <i class="mdi mdi-history me-1"></i>Pricing History
                            </a>
                            <a href="{{ url('revenue/rate-comparison') }}" class="btn btn-soft-warning btn-sm">
                                <i class="mdi mdi-compare me-1"></i>Rate Comparison
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue KPIs -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-sm rounded-circle bg-soft-primary d-flex align-items-center justify-content-center">
                                        <i class="mdi mdi-currency-inr text-primary font-size-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-0">₹{{ number_format($monthRevenue) }}</h3>
                                    <p class="text-muted mb-0 font-size-13">Monthly Revenue</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-sm rounded-circle bg-soft-success d-flex align-items-center justify-content-center">
                                        <i class="mdi mdi-door-open text-success font-size-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-0">{{ $occPct }}%</h3>
                                    <p class="text-muted mb-0 font-size-13">Occupancy Rate</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-sm rounded-circle bg-soft-warning d-flex align-items-center justify-content-center">
                                        <i class="mdi mdi-tag text-warning font-size-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-0">₹{{ number_format($adr) }}</h3>
                                    <p class="text-muted mb-0 font-size-13">ADR (Avg Daily Rate)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-sm rounded-circle bg-soft-info d-flex align-items-center justify-content-center">
                                        <i class="mdi mdi-chart-bar text-info font-size-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-0">₹{{ number_format($revpar) }}</h3>
                                    <p class="text-muted mb-0 font-size-13">RevPAR</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Pricing Recommendations -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="mdi mdi-robot me-2 text-primary"></i>AI Pricing Recommendations
                            </h5>
                            <button class="btn btn-sm btn-primary" onclick="applyAIRates()" id="applyBtn">
                                <i class="mdi mdi-check-all me-1"></i>Apply AI Rates
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Room Category</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Occupied</th>
                                            <th class="text-center">Available</th>
                                            <th class="text-center">Occupancy %</th>
                                            <th class="text-right">Current Rate</th>
                                            <th class="text-right">AI Recommended</th>
                                            <th class="text-right">Change</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($occupancyData as $cat)
                                        <tr>
                                            <td>
                                                <strong>{{ $cat['name'] }}</strong>
                                                <br><small class="text-muted">{{ $cat['cat_code'] }}</small>
                                            </td>
                                            <td class="text-center">{{ $cat['total_rooms'] }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-soft-danger">{{ $cat['occupied'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-soft-success">{{ $cat['available'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                @php $occClass = $cat['occupancy_pct'] >= 80 ? 'danger' : ($cat['occupancy_pct'] >= 50 ? 'warning' : 'success'); @endphp
                                                <span class="badge badge-soft-{{ $occClass }}">{{ $cat['occupancy_pct'] }}%</span>
                                                <div class="progress mt-1" style="height: 4px;">
                                                    <div class="progress-bar bg-{{ $occClass }}" style="width: {{ $cat['occupancy_pct'] }}%"></div>
                                                </div>
                                            </td>
                                            <td class="text-right">₹{{ number_format($cat['current_rate']) }}</td>
                                            <td class="text-right">
                                                <strong class="text-primary">₹{{ number_format($cat['recommended_rate']) }}</strong>
                                            </td>
                                            <td class="text-right">
                                                @if($cat['rate_diff'] > 0)
                                                    <span class="text-success">
                                                        <i class="mdi mdi-arrow-up"></i> +₹{{ number_format(abs($cat['rate_diff'])) }}
                                                        <small>(+{{ $cat['rate_change_pct'] }}%)</small>
                                                    </span>
                                                @elseif($cat['rate_diff'] < 0)
                                                    <span class="text-danger">
                                                        <i class="mdi mdi-arrow-down"></i> -₹{{ number_format(abs($cat['rate_diff'])) }}
                                                        <small>({{ $cat['rate_change_pct'] }}%)</small>
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <input type="number" class="form-control form-control-sm" style="width: 100px;"
                                                       name="categories[{{ $cat['cat_code'] }}]"
                                                       value="{{ $cat['recommended_rate'] }}">
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">No room categories found</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Occupancy Trend Chart -->
            <div class="row">
                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0">
                            <h5 class="card-title mb-0"><i class="mdi mdi-chart-line me-2"></i>7-Day Occupancy Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="occupancyTrendChart" style="height: 250px;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Pricing Factors -->
                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0">
                            <h5 class="card-title mb-0"><i class="mdi mdi-robot me-2"></i>AI Pricing Factors</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Current Occupancy</small>
                                    <small class="fw-bold">{{ $occPct }}%</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-{{ $occPct >= 80 ? 'danger' : ($occPct >= 50 ? 'warning' : 'success') }}" style="width: {{ $occPct }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Day of Week Factor</small>
                                    <small class="fw-bold">{{ \Carbon\Carbon::today()->isWeekend() ? 'Weekend (+15%)' : 'Weekday' }}</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Season Factor</small>
                                    @php $month = \Carbon\Carbon::now()->month; @endphp
                                    <small class="fw-bold">{{ in_array($month, [10,11,12,3,4]) ? 'Peak (+12%)' : (in_array($month, [6,7,8]) ? 'Off-season (-8%)' : 'Normal') }}</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Advance Booking</small>
                                    <small class="fw-bold">Same Day (+10%)</small>
                                </div>
                            </div>
                            <div class="divider" style="border-top: 1px solid #e2e8f0; margin: 12px 0;"></div>
                            <div class="text-center">
                                <small class="text-muted">Dynamic Rate Range</small>
                                <div class="fw-bold text-primary" style="font-size: 18px;">
                                    50% — 200% of Base Rate
                                </div>
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
    // Occupancy Trend Chart
    var trendData = {!! json_encode($trend) !!};
    var ctx = document.getElementById('occupancyTrendChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: trendData.map(d => d.day),
                datasets: [{
                    label: 'Occupancy %',
                    data: trendData.map(d => d.pct),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: '#3b82f6',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(ctx) {
                                var d = trendData[ctx.dataIndex];
                                return d.occupied + ' / ' + d.total + ' rooms';
                            }
                        }
                    }
                },
                scales: {
                    y: { min: 0, max: 100, ticks: { callback: v => v + '%' } }
                }
            }
        });
    }
});

function applyAIRates() {
    if (!confirm('Apply AI recommended rates to all room categories?')) return;

    var formData = {};
    $('input[name^="categories"]').each(function() {
        var name = $(this).attr('name').replace('categories[', '').replace(']', '');
        formData[name] = $(this).val();
    });

    $.ajax({
        url: '{{ url("revenue/apply-ai-rates") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            categories: formData
        },
        success: function(res) {
            if (res.success) {
                toastr.success(res.message);
            } else {
                toastr.error('Failed to apply rates');
            }
        },
        error: function() {
            toastr.error('Error applying rates');
        }
    });
}
</script>
@endsection
