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
                                <i class="mdi mdi-domain me-2"></i>Chain Management Dashboard
                            </h4>
                            <p class="text-muted mb-0">Centralized view of {{ $totalProperties }} properties across India</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ url('chain/report') }}" class="btn btn-soft-primary btn-sm">
                                <i class="mdi mdi-chart-bar me-1"></i>Cross-Property Report
                            </a>
                            <a href="{{ url('chain/comparison') }}" class="btn btn-soft-info btn-sm">
                                <i class="mdi mdi-compare me-1"></i>Comparison
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chain KPIs -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-sm rounded-circle bg-soft-primary d-flex align-items-center justify-content-center">
                                        <i class="mdi mdi-domain text-primary font-size-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-0">{{ number_format($totalProperties) }}</h3>
                                    <p class="text-muted mb-0 font-size-13">Total Properties</p>
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
                                    <h3 class="mb-0">{{ number_format($totalRoomCount) }}</h3>
                                    <p class="text-muted mb-0 font-size-13">Total Rooms ({{ $avgOccupancy }}% occ.)</p>
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
                                        <i class="mdi mdi-currency-inr text-warning font-size-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-0">₹{{ number_format($totalRevenue) }}</h3>
                                    <p class="text-muted mb-0 font-size-13">Total Revenue (MTD)</p>
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
                                        <i class="mdi mdi-tag text-info font-size-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-0">₹{{ number_format($avgADR) }}</h3>
                                    <p class="text-muted mb-0 font-size-13">Average ADR</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performers + State Breakdown -->
            <div class="row">
                <!-- Top by Revenue -->
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0">
                            <h5 class="card-title mb-0"><i class="mdi mdi-trophy me-2"></i>Top 5 by Revenue</h5>
                        </div>
                        <div class="card-body">
                            @foreach($topByRevenue as $prop)
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-sm rounded-circle bg-soft-{{ $loop->index < 3 ? 'warning' : 'secondary' }} d-flex align-items-center justify-content-center">
                                        <span class="fw-bold">{{ $loop->index + 1 }}</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $prop['name'] }}</h6>
                                    <small class="text-muted">{{ $prop['city'] }} • {{ $prop['total_rooms'] }} rooms</small>
                                </div>
                                <div class="text-end">
                                    <h6 class="mb-0 text-success">₹{{ number_format($prop['total_revenue']) }}</h6>
                                    <small class="text-muted">{{ $prop['occupancy_pct'] }}% occ.</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Top by Occupancy -->
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0">
                            <h5 class="card-title mb-0"><i class="mdi mdi-chart-line me-2"></i>Top 5 by Occupancy</h5>
                        </div>
                        <div class="card-body">
                            @foreach($topByOccupancy as $prop)
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-sm rounded-circle bg-soft-{{ $prop['occupancy_pct'] >= 80 ? 'success' : ($prop['occupancy_pct'] >= 50 ? 'warning' : 'danger') }} d-flex align-items-center justify-content-center">
                                        <span class="fw-bold" style="font-size: 11px;">{{ $prop['occupancy_pct'] }}%</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $prop['name'] }}</h6>
                                    <small class="text-muted">{{ $prop['city'] }} • {{ $prop['occupied'] }}/{{ $prop['total_rooms'] }} rooms</small>
                                </div>
                                <div class="text-end">
                                    <div class="progress" style="width: 80px; height: 6px;">
                                        <div class="progress-bar bg-{{ $prop['occupancy_pct'] >= 80 ? 'success' : ($prop['occupancy_pct'] >= 50 ? 'warning' : 'danger') }}" style="width: {{ $prop['occupancy_pct'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- State-wise Breakdown -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0">
                            <h5 class="card-title mb-0"><i class="mdi mdi-map-marker me-2"></i>State-wise Breakdown</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>State</th>
                                            <th class="text-center">Properties</th>
                                            <th class="text-center">Total Rooms</th>
                                            <th class="text-center">Occupied</th>
                                            <th class="text-center">Occupancy %</th>
                                            <th class="text-right">Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($byState as $state => $data)
                                        <tr>
                                            <td><strong>{{ $state ?: 'N/A' }}</strong></td>
                                            <td class="text-center">{{ $data['properties'] }}</td>
                                            <td class="text-center">{{ number_format($data['total_rooms']) }}</td>
                                            <td class="text-center">{{ number_format($data['occupied']) }}</td>
                                            <td class="text-center">
                                                @php $cls = $data['avg_occupancy'] >= 80 ? 'success' : ($data['avg_occupancy'] >= 50 ? 'warning' : 'danger'); @endphp
                                                <span class="badge badge-soft-{{ $cls }}">{{ $data['avg_occupancy'] }}%</span>
                                            </td>
                                            <td class="text-right">₹{{ number_format($data['revenue']) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Properties Grid -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="mdi mdi-domain me-2"></i>All Properties</h5>
                            <span class="badge badge-soft-primary">{{ $totalProperties }} properties</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0" id="propertiesTable">
                                    <thead>
                                        <tr>
                                            <th>Property</th>
                                            <th>City</th>
                                            <th>State</th>
                                            <th class="text-center">Rooms</th>
                                            <th class="text-center">Occupied</th>
                                            <th class="text-center">Occ. %</th>
                                            <th class="text-right">Revenue</th>
                                            <th class="text-right">ADR</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($propertyData as $prop)
                                        <tr>
                                            <td>
                                                <strong>{{ $prop['name'] }}</strong>
                                                <br><small class="text-muted">{{ $prop['code'] }}</small>
                                            </td>
                                            <td>{{ $prop['city'] }}</td>
                                            <td><small>{{ $prop['state'] }}</small></td>
                                            <td class="text-center">{{ $prop['total_rooms'] }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-soft-danger">{{ $prop['occupied'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                @php $cls = $prop['occupancy_pct'] >= 80 ? 'success' : ($prop['occupancy_pct'] >= 50 ? 'warning' : 'danger'); @endphp
                                                <span class="badge badge-soft-{{ $cls }}">{{ $prop['occupancy_pct'] }}%</span>
                                            </td>
                                            <td class="text-right">₹{{ number_format($prop['total_revenue']) }}</td>
                                            <td class="text-right">₹{{ number_format($prop['adr']) }}</td>
                                            <td class="text-center">
                                                <a href="{{ url('chain/switch/' . $prop['propertyid']) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Switch to this property">
                                                    <i class="mdi mdi-login"></i>
                                                </a>
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
<script>
$(document).ready(function() {
    $('#propertiesTable').DataTable({
        pageLength: 25,
        order: [[5, 'desc']],
        language: { search: 'Search properties...' }
    });
});
</script>
@endsection
