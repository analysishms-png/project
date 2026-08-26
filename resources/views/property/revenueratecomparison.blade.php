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
                                <i class="mdi mdi-compare me-2"></i>Rate Comparison
                            </h4>
                            <p class="text-muted mb-0">Compare current, AI-recommended, and channel rates</p>
                        </div>
                        <a href="{{ url('revenue') }}" class="btn btn-soft-primary btn-sm">
                            <i class="mdi mdi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-2 d-flex flex-wrap align-items-center gap-3">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-secondary btn-sm {{ $occtype === 'singleuser' ? 'active' : '' }}">
                                    <input type="radio" name="rcOcc" value="singleuser" {{ $occtype === 'singleuser' ? 'checked' : '' }}> Single User
                                </label>
                                <label class="btn btn-outline-secondary btn-sm {{ $occtype === 'multiuser' ? 'active' : '' }}">
                                    <input type="radio" name="rcOcc" value="multiuser" {{ $occtype === 'multiuser' ? 'checked' : '' }}> Multi User
                                </label>
                            </div>
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-primary btn-sm active">
                                    <input type="radio" name="rcView" value="table" checked> <i class="mdi mdi-table me-1"></i>Table
                                </label>
                                <label class="btn btn-outline-primary btn-sm">
                                    <input type="radio" name="rcView" value="cards"> <i class="mdi mdi-view-dashboard-outline me-1"></i>Cards
                                </label>
                            </div>
                            <small class="text-muted ms-auto"><i class="mdi mdi-flash me-1"></i>Updates automatically on change</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comparison Table -->
            <div class="row rcViewTable">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Room Category</th>
                                            <th class="text-right">Current Rate</th>
                                            <th class="text-right">AI Recommended</th>
                                            <th class="text-right">Channel Rate</th>
                                            <th class="text-right">Difference (AI vs Current)</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rcBody">
                                        @forelse($comparison as $comp)
                                        <tr>
                                            <td>
                                                <strong>{{ $comp['name'] }}</strong>
                                                <br><small class="text-muted">{{ $comp['cat_code'] }}</small>
                                            </td>
                                            <td class="text-right">₹{{ number_format($comp['current_rate']) }}</td>
                                            <td class="text-right fw-bold text-primary">₹{{ number_format($comp['ai_rate']) }}</td>
                                            <td class="text-right">
                                                @if($comp['channel_rate'] > 0)
                                                    ₹{{ number_format($comp['channel_rate']) }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if($comp['difference'] > 0)
                                                    <span class="text-success fw-bold">+₹{{ number_format(abs($comp['difference'])) }}</span>
                                                @elseif($comp['difference'] < 0)
                                                    <span class="text-danger fw-bold">-₹{{ number_format(abs($comp['difference'])) }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($comp['difference'] > 0)
                                                    <span class="badge badge-soft-success">Increase Recommended</span>
                                                @elseif($comp['difference'] < 0)
                                                    <span class="badge badge-soft-warning">Decrease Recommended</span>
                                                @else
                                                    <span class="badge badge-soft-secondary">Optimal</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">No room categories found</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visual Comparison -->
            <div class="row rcViewCards d-none">
                @forelse($comparison as $comp)
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <h6 class="card-title">{{ $comp['name'] }}</h6>
                            <div class="my-3">
                                <small class="text-muted d-block">Current</small>
                                <div style="font-size: 20px; font-weight: 600;">₹{{ number_format($comp['current_rate']) }}</div>
                            </div>
                            <div class="my-3">
                                <small class="text-muted d-block">AI Recommended</small>
                                <div style="font-size: 24px; font-weight: 700; color: #3b82f6;">₹{{ number_format($comp['ai_rate']) }}</div>
                            </div>
                            @if($comp['channel_rate'] > 0)
                            <div class="my-3">
                                <small class="text-muted d-block">Channel Rate</small>
                                <div style="font-size: 18px; font-weight: 600; color: #f59e0b;">₹{{ number_format($comp['channel_rate']) }}</div>
                            </div>
                            @endif
                            <div class="mt-2">
                                @if($comp['difference'] > 0)
                                    <span class="badge badge-soft-success">+{{ round(($comp['difference'] / max($comp['current_rate'], 1)) * 100) }}%</span>
                                @elseif($comp['difference'] < 0)
                                    <span class="badge badge-soft-danger">{{ round(($comp['difference'] / max($comp['current_rate'], 1)) * 100) }}%</span>
                                @else
                                    <span class="badge badge-soft-secondary">Optimal</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center text-muted py-5">No room categories found</div>
                </div>
                @endforelse
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    function rcEsc(v) {
        return $('<div>').text(v == null ? '' : String(v)).html();
    }

    var rcRows = null;

    function rcRender() {
        if (!rcRows) return;
        var mode = window.hmsRadioVal('rcView') || 'table';
        $('.rcViewTable').toggleClass('d-none', mode === 'cards');
        $('.rcViewCards').toggleClass('d-none', mode !== 'cards');
    }

    function rcFetch() {
        var occtype = window.hmsRadioVal('rcOcc') || 'singleuser';
        $.getJSON('{{ url("revenue/rate-comparison/data") }}', { occtype: occtype }, function (res) {
            if (!res || !res.rows) return;
            rcRows = res.rows;

            var h = '';
            $.each(rcRows, function (i, c) {
                var diff, status;
                if (c.difference > 0) {
                    diff = '<span class="text-success fw-bold">+₹' + window.hmsFmt(c.difference) + '</span>';
                    status = '<span class="badge badge-soft-success">Increase Recommended</span>';
                } else if (c.difference < 0) {
                    diff = '<span class="text-danger fw-bold">-₹' + window.hmsFmt(Math.abs(c.difference)) + '</span>';
                    status = '<span class="badge badge-soft-warning">Decrease Recommended</span>';
                } else {
                    diff = '<span class="text-muted">—</span>';
                    status = '<span class="badge badge-soft-secondary">Optimal</span>';
                }
                h += '<tr><td><strong>' + rcEsc(c.name || '') + '</strong><br><small class="text-muted">' + rcEsc(c.cat_code || '') + '</small></td>' +
                    '<td class="text-right">₹' + window.hmsFmt(c.current_rate) + '</td>' +
                    '<td class="text-right fw-bold text-primary">₹' + window.hmsFmt(c.ai_rate) + '</td>' +
                    '<td class="text-right">' + (c.channel_rate > 0 ? '₹' + window.hmsFmt(c.channel_rate) : '<span class="text-muted">—</span>') + '</td>' +
                    '<td class="text-right">' + diff + '</td>' +
                    '<td class="text-center">' + status + '</td></tr>';
            });
            $('#rcBody').html(h);
            rcRender();
        });
    }

    $(function () {
        $(document).on('change', 'input[name="rcOcc"]', rcFetch);
        $(document).on('change', 'input[name="rcView"]', rcRender);
    });
})();
</script>

@endsection
