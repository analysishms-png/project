@extends('property.layouts.property')
@section('content')

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="page-title mb-1">
                                <i class="mdi mdi-cloud-sync me-2"></i>Channel Manager
                            </h4>
                            <p class="text-muted mb-0">Manage OTA connectivity — Booking.com, MakeMyTrip, Goibibo & more via eGlobe</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ url('channel/rooms') }}" class="btn btn-soft-primary btn-sm">
                                <i class="mdi mdi-door-open me-1"></i>Room Inventory
                            </a>
                            <a href="{{ url('channel/rates') }}" class="btn btn-soft-success btn-sm">
                                <i class="mdi mdi-currency-inr me-1"></i>Channel Rates
                            </a>
                            <a href="{{ url('channel/availability') }}" class="btn btn-soft-info btn-sm">
                                <i class="mdi mdi-calendar me-1"></i>Availability
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Connection Status -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm bg-soft-{{ $connectionColor }}">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        @if($connectionColor == 'success')
                                            <div class="avatar-sm rounded-circle bg-success d-flex align-items-center justify-content-center">
                                                <i class="mdi mdi-cloud-check text-white font-size-20"></i>
                                            </div>
                                        @else
                                            <div class="avatar-sm rounded-circle bg-danger d-flex align-items-center justify-content-center">
                                                <i class="mdi mdi-cloud-alert text-white font-size-20"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <h5 class="mb-0">eGlobe Channel Manager — {{ $connectionStatus }}</h5>
                                        <small class="text-muted">
                                            Provider: eGlobe Solutions | 
                                            API Key: {{ $channelenviro->apikey ? substr($channelenviro->apikey, 0, 8) . '...' : 'Not configured' }} |
                                            Property ID: {{ $channelenviro->eglobepropertyid ?? 'N/A' }}
                                        </small>
                                    </div>
                                </div>
                                <a href="{{ url('channelenviro') }}" class="btn btn-sm btn-outline-{{ $connectionColor }}">
                                    <i class="mdi mdi-cog me-1"></i>Configure
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-sm rounded-circle bg-soft-primary d-flex align-items-center justify-content-center">
                                        <i class="mdi mdi-door-open text-primary font-size-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-0">{{ $roomcat->count() }}</h3>
                                    <p class="text-muted mb-0 font-size-13">Room Categories Mapped</p>
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
                                        <i class="mdi mdi-calendar-check text-success font-size-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-0">{{ number_format($todayBookings) }}</h3>
                                    <p class="text-muted mb-0 font-size-13">Today's Channel Bookings</p>
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
                                    <h3 class="mb-0">{{ $rates->count() }}</h3>
                                    <p class="text-muted mb-0 font-size-13">Channel Rates Configured</p>
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
                                        <i class="mdi mdi-variant text-info font-size-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-0">{{ $derived->count() }}</h3>
                                    <p class="text-muted mb-0 font-size-13">Derived Pricing Rules</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Room Mapping + Quick Actions -->
            <div class="row">
                <!-- Room Category Mapping -->
                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="mdi mdi-door-open me-2"></i>Room Category Mapping</h5>
                            <a href="{{ url('channel/rooms') }}" class="btn btn-sm btn-soft-primary">Manage</a>
                        </div>
                        <div class="card-body">
                            @if($roomcat->count())
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Channel Map Code</th>
                                            <th>Channel Status</th>
                                            <th>Inventory</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($roomcat as $cat)
                                        <tr>
                                            <td>
                                                <strong>{{ $cat->name }}</strong>
                                                <br><small class="text-muted">Code: {{ $cat->cat_code }}</small>
                                            </td>
                                            <td>
                                                @if($cat->map_code)
                                                    <span class="badge badge-soft-success">{{ $cat->map_code }}</span>
                                                @else
                                                    <span class="badge badge-soft-danger">Not Mapped</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($cat->map_code)
                                                    <span class="badge badge-soft-success"><i class="mdi mdi-check"></i> Synced</span>
                                                @else
                                                    <span class="badge badge-soft-warning"><i class="mdi mdi-alert"></i> Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ url('channel/rooms') }}" class="btn btn-sm btn-outline-primary">Push</a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center text-muted py-4">
                                <i class="mdi mdi-door-open font-size-48 mb-2"></i>
                                <p>No room categories found. Configure rooms first.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0">
                            <h5 class="card-title mb-0"><i class="mdi mdi-lightning-bolt me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ url('channel/rooms') }}" class="btn btn-outline-primary text-start">
                                    <i class="mdi mdi-door-open me-2"></i>Push Room Inventory
                                    <small class="text-muted d-block">Update room availability on all channels</small>
                                </a>
                                <a href="{{ url('channel/rates') }}" class="btn btn-outline-success text-start">
                                    <i class="mdi mdi-currency-inr me-2"></i>Update Channel Rates
                                    <small class="text-muted d-block">Set rates for each room category per channel</small>
                                </a>
                                <a href="{{ url('channel/derivedpricing') }}" class="btn btn-outline-warning text-start">
                                    <i class="mdi mdi-variant me-2"></i>Derived Pricing
                                    <small class="text-muted d-block">Auto-calculate rates from base rate</small>
                                </a>
                                <a href="{{ url('channel/availability') }}" class="btn btn-outline-info text-start">
                                    <i class="mdi mdi-calendar me-2"></i>Availability Calendar
                                    <small class="text-muted d-block">View 14-day room availability grid</small>
                                </a>
                                <a href="{{ url('bookingfetch') }}" class="btn btn-outline-secondary text-start">
                                    <i class="mdi mdi-download me-2"></i>Fetch Channel Bookings
                                    <small class="text-muted d-block">Pull latest bookings from eGlobe/OTAs</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Channel Activity -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="mdi mdi-history me-2"></i>Recent Channel Activity</h5>
                        </div>
                        <div class="card-body">
                            @if($pushes->count())
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>Channel</th>
                                            <th>Action</th>
                                            <th>Status</th>
                                            <th>By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pushes as $push)
                                        <tr>
                                            <td><small>{{ \Carbon\Carbon::parse($push->u_entdt)->format('d-M H:i') }}</small></td>
                                            <td><span class="badge badge-soft-primary">{{ $push->name ?? 'eGlobe' }}</span></td>
                                            <td><small>{{ $push->url ? 'Push' : 'Pull' }}</small></td>
                                            <td>
                                                @if($push->httpcode >= 200 && $push->httpcode < 300)
                                                    <span class="badge badge-soft-success">Success ({{ $push->httpcode }})</span>
                                                @elseif($push->httpcode > 0)
                                                    <span class="badge badge-soft-danger">Failed ({{ $push->httpcode }})</span>
                                                @else
                                                    <span class="badge badge-soft-secondary">Pending</span>
                                                @endif
                                            </td>
                                            <td><small>{{ $push->u_name }}</small></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center text-muted py-4">
                                <i class="mdi mdi-cloud-sync font-size-48 mb-2"></i>
                                <p>No channel activity yet. Push room inventory to start.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
