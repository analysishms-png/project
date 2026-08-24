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
                                <i class="mdi mdi-calendar me-2"></i>Availability Calendar
                            </h4>
                            <p class="text-muted mb-0">14-day room availability overview for channel management</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ url('channel/dashboard') }}" class="btn btn-soft-primary btn-sm">
                                <i class="mdi mdi-arrow-left me-1"></i>Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date Navigation -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <a href="{{ url('channel/availability?start=' . date('Y-m-d', strtotime($startDate . ' -7 days'))) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="mdi mdi-chevron-left me-1"></i>Previous Week
                                </a>
                                <strong id="caRange">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>
                                <div class="d-flex gap-2 align-items-center">
                                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                        <label class="btn btn-outline-secondary btn-sm active">
                                            <input type="radio" name="caMode" value="all" checked> All Categories
                                        </label>
                                        <label class="btn btn-outline-secondary btn-sm">
                                            <input type="radio" name="caMode" value="mapped"> OTA-Mapped
                                        </label>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="caRefresh">
                                        <i class="mdi mdi-refresh me-1"></i>Live Refresh
                                    </button>
                                </div>
                                <a href="{{ url('channel/availability?start=' . date('Y-m-d', strtotime($startDate . ' +7 days'))) }}" class="btn btn-sm btn-outline-secondary">
                                    Next Week<i class="mdi mdi-chevron-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Availability Grid -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            @if($roomcat->count())
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0" style="font-size: 12px;">
                                    <thead>
                                        <tr class="bg-light">
                                            <th style="min-width: 140px;">Room Category</th>
                                            @foreach($dates as $date)
                                            <th class="text-center" style="min-width: 70px; {{ \Carbon\Carbon::parse($date)->isToday() ? 'background: #dbeafe;' : '' }}">
                                                {{ \Carbon\Carbon::parse($date)->format('D') }}<br>
                                                <small>{{ \Carbon\Carbon::parse($date)->format('d M') }}</small>
                                            </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody id="caBody">
                                        @foreach($roomcat as $cat)
                                        <tr>
                                            <td>
                                                <strong>{{ $cat->name }}</strong>
                                                <br><small class="text-muted">Total: {{ $cat->totalroom ?? 0 }} rooms</small>
                                            </td>
                                            @foreach($dates as $date)
                                                @php
                                                    $avail = $availability[$cat->cat_code][$date] ?? ['total' => 0, 'occupied' => 0, 'available' => 0, 'pct' => 0];
                                                @endphp
                                                <td class="text-center" style="{{ \Carbon\Carbon::parse($date)->isToday() ? 'background: #eff6ff;' : '' }}">
                                                    @if($avail['available'] > 0)
                                                        <div class="fw-bold text-success">{{ $avail['available'] }}</div>
                                                        <div class="text-muted" style="font-size:10px;">of {{ $avail['total'] }}</div>
                                                        <div class="progress mt-1" style="height: 4px;">
                                                            <div class="progress-bar bg-success" style="width: {{ $avail['pct'] }}%"></div>
                                                        </div>
                                                    @elseif($avail['total'] > 0)
                                                        <div class="fw-bold text-danger">0</div>
                                                        <div class="text-muted" style="font-size:10px;">SOLD OUT</div>
                                                        <div class="progress mt-1" style="height: 4px;">
                                                            <div class="progress-bar bg-danger" style="width: 100%"></div>
                                                        </div>
                                                    @else
                                                        <div class="text-muted">—</div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Legend -->
                            <div class="mt-3 d-flex gap-3">
                                <small><span class="badge badge-soft-success">Green</span> Available</small>
                                <small><span class="badge badge-soft-danger">Red</span> Sold Out</small>
                                <small><span class="badge badge-soft-secondary">—</span> No rooms</small>
                            </div>
                            @else
                            <div class="text-center text-muted py-5">
                                <i class="mdi mdi-calendar-blank font-size-48 mb-2"></i>
                                <p>No room categories configured.</p>
                            </div>
                            @endif
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

    function caCell(av, date) {
        var today = new Date().toISOString().slice(0, 10) === date;
        var style = today ? ' style="background: #eff6ff;"' : '';
        if (av.available > 0) {
            return '<td class="text-center"' + style + '>' +
                '<div class="fw-bold text-success">' + av.available + '</div>' +
                '<div class="text-muted" style="font-size:10px;">of ' + av.total + '</div>' +
                '<div class="progress mt-1" style="height: 4px;">' +
                '<div class="progress-bar bg-success" style="width: ' + av.pct + '%"></div></div></td>';
        }
        if (av.total > 0) {
            return '<td class="text-center"' + style + '>' +
                '<div class="fw-bold text-danger">0</div>' +
                '<div class="text-muted" style="font-size:10px;">SOLD OUT</div>' +
                '<div class="progress mt-1" style="height: 4px;">' +
                '<div class="progress-bar bg-danger" style="width: 100%"></div></div></td>';
        }
        return '<td class="text-center"><div class="text-muted">—</div></td>';
    }

    var caStart = '{{ $startDate }}';

    function caFetch(start) {
        start = start || caStart;
        var mapped = (window.hmsRadioVal('caMode') === 'mapped') ? 1 : 0;

        $.getJSON('{{ url("channel/availability/data") }}', { start: start, mapped: mapped }, function (res) {
            caStart = res.startDate;
            $('#caRange').text(
                new Date(res.startDate).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) +
                ' — ' + new Date(res.endDate).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
            );

            var h = '';
            $.each(res.categories, function (ci, cat) {
                h += '<tr><td><strong>' + cat.name + '</strong><br><small class="text-muted">Total: ' +
                    (cat.totalroom || 0) + ' rooms</small>' +
                    (cat.map_code ? ' <span class="badge badge-soft-success ms-1">' + cat.map_code + '</span>' : '') +
                    '</td>';
                $.each(res.dates, function (di, date) {
                    h += caCell(res.availability[cat.cat_code][date] || { available: 0, total: 0 }, date);
                });
                h += '</tr>';
            });
            $('#caBody').html(h);
        });
    }

    $(function () {
        $(document).on('change', 'input[name="caMode"]', function () { caFetch(); });
        $('#caRefresh').on('click', function () { caFetch(); });
    });
})();
</script>

@endsection
