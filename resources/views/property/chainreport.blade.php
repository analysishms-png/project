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
                                <i class="mdi mdi-chart-bar me-2"></i>Cross-Property Report
                            </h4>
                            <p class="text-muted mb-0">Revenue comparison across all properties</p>
                        </div>
                        <a href="{{ url('chain') }}" class="btn btn-soft-primary btn-sm">
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
                            <form method="GET" action="{{ url('chain/report') }}" class="row g-3 align-items-end" id="crFilterForm">
                                <div class="col-md-3">
                                    <label class="form-label">From Date</label>
                                    <input type="date" class="form-control form-control-sm" name="start" value="{{ $startDate }}" id="crStart">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">To Date</label>
                                    <input type="date" class="form-control form-control-sm" name="end" value="{{ $endDate }}" id="crEnd">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">View</label>
                                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                        <label class="btn btn-outline-secondary btn-sm active">
                                            <input type="radio" name="crMode" value="all" checked> All Properties
                                        </label>
                                        <label class="btn btn-outline-secondary btn-sm">
                                            <input type="radio" name="crMode" value="chain"> Chain Total
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="mdi mdi-magnify me-1"></i>Generate
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-soft-primary">
                        <div class="card-body text-center">
                            <h3 class="mb-0" id="crTotal">₹{{ number_format(collect($reportData)->sum('total')) }}</h3>
                            <small class="text-muted">Total Chain Revenue</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-soft-success">
                        <div class="card-body text-center">
                            <h3 class="mb-0" id="crRevenue">₹{{ number_format(collect($reportData)->sum('revenue')) }}</h3>
                            <small class="text-muted">Room Revenue</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-soft-warning">
                        <div class="card-body text-center">
                            <h3 class="mb-0" id="crPos">₹{{ number_format(collect($reportData)->sum('pos')) }}</h3>
                            <small class="text-muted">POS Revenue</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-soft-info">
                        <div class="card-body text-center">
                            <h3 class="mb-0" id="crCheckins">{{ number_format(collect($reportData)->sum('checkins')) }}</h3>
                            <small class="text-muted">Total Check-ins</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Property</th>
                                            <th>City</th>
                                            <th class="text-right">Room Revenue</th>
                                            <th class="text-right">POS Revenue</th>
                                            <th class="text-right">Total Revenue</th>
                                            <th class="text-center">Check-ins</th>
                                            <th class="text-center">Room Nights</th>
                                        </tr>
                                    </thead>
                                    <tbody id="crBody">
                                        @foreach($reportData as $i => $row)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td><strong>{{ $row['name'] }}</strong></td>
                                            <td>{{ $row['city'] }}</td>
                                            <td class="text-right">₹{{ number_format($row['revenue']) }}</td>
                                            <td class="text-right">₹{{ number_format($row['pos']) }}</td>
                                            <td class="text-right fw-bold">₹{{ number_format($row['total']) }}</td>
                                            <td class="text-center">{{ number_format($row['checkins']) }}</td>
                                            <td class="text-center">{{ number_format($row['room_nights']) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active fw-bold" id="crFoot">
                                            <td colspan="3">Chain Total</td>
                                            <td class="text-right">₹{{ number_format(collect($reportData)->sum('revenue')) }}</td>
                                            <td class="text-right">₹{{ number_format(collect($reportData)->sum('pos')) }}</td>
                                            <td class="text-right">₹{{ number_format(collect($reportData)->sum('total')) }}</td>
                                            <td class="text-center">{{ number_format(collect($reportData)->sum('checkins')) }}</td>
                                            <td class="text-center">{{ number_format(collect($reportData)->sum('room_nights')) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    function crFetch() {
        var start = $('#crStart').val();
        var end = $('#crEnd').val();
        if (!start || !end) return;

        $.getJSON('{{ url("chain/report/data") }}', { start: start, end: end }, function (res) {
            if (!res || !res.rows) return;

            var t = res.totals;
            $('#crTotal').text('₹' + window.hmsFmt(t.total));
            $('#crRevenue').text('₹' + window.hmsFmt(t.revenue));
            $('#crPos').text('₹' + window.hmsFmt(t.pos));
            $('#crCheckins').text(window.hmsFmt(t.checkins));

            var mode = window.hmsRadioVal('crMode') || 'all';
            var h = '';
            if (mode === 'chain') {
                h += '<tr class="table-active fw-bold">' +
                    '<td>1</td><td>Chain Total (All Properties)</td><td>—</td>' +
                    '<td class="text-right">₹' + window.hmsFmt(t.revenue) + '</td>' +
                    '<td class="text-right">₹' + window.hmsFmt(t.pos) + '</td>' +
                    '<td class="text-right">₹' + window.hmsFmt(t.total) + '</td>' +
                    '<td class="text-center">' + window.hmsFmt(t.checkins) + '</td>' +
                    '<td class="text-center">' + window.hmsFmt(t.room_nights) + '</td></tr>';
            } else {
                $.each(res.rows, function (i, r) {
                    h += '<tr><td>' + (i + 1) + '</td><td><strong>' + (r.name || '') + '</strong></td><td>' + (r.city || '') + '</td>' +
                        '<td class="text-right">₹' + window.hmsFmt(r.revenue) + '</td>' +
                        '<td class="text-right">₹' + window.hmsFmt(r.pos) + '</td>' +
                        '<td class="text-right fw-bold">₹' + window.hmsFmt(r.total) + '</td>' +
                        '<td class="text-center">' + window.hmsFmt(r.checkins) + '</td>' +
                        '<td class="text-center">' + window.hmsFmt(r.room_nights) + '</td></tr>';
                });
            }
            $('#crBody').html(h);

            $('#crFoot').html(
                '<td colspan="3">Chain Total</td>' +
                '<td class="text-right">₹' + window.hmsFmt(t.revenue) + '</td>' +
                '<td class="text-right">₹' + window.hmsFmt(t.pos) + '</td>' +
                '<td class="text-right">₹' + window.hmsFmt(t.total) + '</td>' +
                '<td class="text-center">' + window.hmsFmt(t.checkins) + '</td>' +
                '<td class="text-center">' + window.hmsFmt(t.room_nights) + '</td>'
            );
        });
    }

    $(function () {
        window.hmsAutoFetch('#crStart, #crEnd', crFetch);
        $(document).on('change', 'input[name="crMode"]', crFetch);
    });
})();
</script>

@endsection
