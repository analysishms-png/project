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
                                <i class="mdi mdi-compare me-2"></i>Property Comparison
                            </h4>
                            <p class="text-muted mb-0">Side-by-side performance comparison of all properties</p>
                        </div>
                        <a href="{{ url('chain') }}" class="btn btn-soft-primary btn-sm">
                            <i class="mdi mdi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>

            <!-- Comparison Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="comparisonTable">
                                    <thead>
                                        <tr>
                                            <th>Property</th>
                                            <th>City</th>
                                            <th class="text-center">Rooms</th>
                                            <th class="text-center">Occupied</th>
                                            <th class="text-center">Occupancy %</th>
                                            <th class="text-right">Revenue (MTD)</th>
                                            <th class="text-right">ADR</th>
                                            <th class="text-right">RevPAR</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($comparison as $comp)
                                        <tr>
                                            <td>
                                                <strong>{{ $comp['name'] }}</strong>
                                            </td>
                                            <td>{{ $comp['city'] }}</td>
                                            <td class="text-center">{{ $comp['total_rooms'] }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-soft-danger">{{ $comp['occupied'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                @php $cls = $comp['occupancy_pct'] >= 80 ? 'success' : ($comp['occupancy_pct'] >= 50 ? 'warning' : 'danger'); @endphp
                                                <span class="badge badge-soft-{{ $cls }}">{{ $comp['occupancy_pct'] }}%</span>
                                                <div class="progress mt-1" style="height: 4px;">
                                                    <div class="progress-bar bg-{{ $cls }}" style="width: {{ $comp['occupancy_pct'] }}%"></div>
                                                </div>
                                            </td>
                                            <td class="text-right">₹{{ number_format($comp['revenue']) }}</td>
                                            <td class="text-right">₹{{ number_format($comp['adr']) }}</td>
                                            <td class="text-right">₹{{ number_format($comp['revpar']) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visual Cards -->
            <div class="row">
                @foreach($comparison as $comp)
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <h6 class="card-title text-truncate" title="{{ $comp['name'] }}">{{ $comp['name'] }}</h6>
                            <small class="text-muted">{{ $comp['city'] }}</small>
                            <div class="my-3">
                                @php $cls = $comp['occupancy_pct'] >= 80 ? 'success' : ($comp['occupancy_pct'] >= 50 ? 'warning' : 'danger'); @endphp
                                <div style="font-size: 28px; font-weight: 700;" class="text-{{ $cls }}">{{ $comp['occupancy_pct'] }}%</div>
                                <small class="text-muted">{{ $comp['occupied'] }}/{{ $comp['total_rooms'] }} rooms</small>
                            </div>
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="fw-bold">₹{{ number_format($comp['revenue']) }}</div>
                                    <small class="text-muted">Revenue</small>
                                </div>
                                <div class="col-4">
                                    <div class="fw-bold">₹{{ number_format($comp['adr']) }}</div>
                                    <small class="text-muted">ADR</small>
                                </div>
                                <div class="col-4">
                                    <div class="fw-bold">₹{{ number_format($comp['revpar']) }}</div>
                                    <small class="text-muted">RevPAR</small>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="{{ url('chain/switch/' . $comp['propertyid']) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="mdi mdi-login me-1"></i>Switch
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#comparisonTable').DataTable({
        pageLength: 25,
        order: [[4, 'desc']],
        language: { search: 'Search properties...' }
    });
});
</script>
@endsection
