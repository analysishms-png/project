@extends('property.layouts.main')
@section('main-container')

{{-- DataTables --}}
<link  href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet"/>
<link  href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" rel="stylesheet"/>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<style>
    /* ── Page wrapper ───────────────────────────────── */
    .dr-wrap { font-family: inherit; }

    /* ── Banner (damage / danger theme) ─────────────── */
    .dr-banner {
        background: linear-gradient(135deg, #7f1d1d 0%, #b91c1c 55%, #dc2626 100%);
        border-radius: .6rem;
    }

    /* ── Filter card accent ─────────────────────────── */
    .dr-filter-card { border-top: 3px solid #dc2626; border-radius: .6rem; }

    /* ── Stat cards ─────────────────────────────────── */
    .dr-stat {
        border-radius: .6rem;
        border: 1px solid #e5e7eb;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .dr-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 .35rem .8rem rgba(0,0,0,.08);
    }
    .dr-stat .stat-icon {
        width: 42px; height: 42px; border-radius: .55rem;
        display: flex; align-items: center; justify-content: center; font-size: 18px;
    }
    .dr-stat .stat-num { font-size: 26px; font-weight: 800; line-height: 1; }

    /* ── Table header ───────────────────────────────── */
    #dr-table thead th {
        background: #7f1d1d;
        color: #fff;
        font-size: 12px;
        text-align: center;
        white-space: nowrap;
        border: 1px solid #dc2626;
    }
    #dr-table tbody td {
        font-size: 13px;
        vertical-align: middle;
    }

    /* ── Compact rows ───────────────────────────────── */
    #dr-table tbody td,
    #dr-table tfoot td { padding: 4px 8px !important; }
    /* Description column wrap */
    #dr-table tbody td:nth-child(7),
    #dr-table thead th:nth-child(7) { white-space: normal; word-break: break-word; min-width: 180px; }

    /* ── Status select ──────────────────────────────── */
    .dr-status-select {
        font-size: 12px;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: .35rem;
        cursor: pointer;
    }

    /* ── Print ──────────────────────────────────────── */
    .dr-print-header { display: none; }
    @media print {
        .dr-banner,
        .dr-filter-card,
        .dr-stats,
        .dt-buttons            { display: none !important; }
        .dr-print-header       { display: block !important; text-align: center; margin-bottom: 12px; }
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate { display: none !important; }
    }
</style>

<div class="content-body dr-wrap">
<div class="container-fluid px-4 py-3">

    {{-- ── BANNER ── --}}
    <div class="dr-banner d-flex align-items-center justify-content-between text-white p-3 mb-3 shadow-sm">
        <div>
            <h3 class="mb-0 font-weight-bold text-white" style="text-shadow:0 2px 4px rgba(0,0,0,.3); letter-spacing:.4px;">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i>Damage Report
            </h3>
            <small class="d-none d-md-block" style="opacity:.75">Housekeeping Module &bull; Room-wise Damage Records</small>
        </div>
        <i class="fa-solid fa-hammer fa-2x d-none d-md-block" style="opacity:.4"></i>
    </div>

    {{-- ── HIDDEN FIELDS ── --}}
    <input type="hidden" id="dr-csrf"     value="{{ csrf_token() }}">
    <input type="hidden" id="dr-compname" value="{{ $company->comp_name }}">
    <input type="hidden" id="dr-address"  value="{{ $company->address1 }}">
    <input type="hidden" id="dr-state"    value="{{ $statename }}">
    <input type="hidden" id="dr-city"     value="{{ $company->city ?? '' }}">
    <input type="hidden" id="dr-pin"      value="{{ $company->pin  ?? '' }}">

    {{-- ── FILTER CARD ── --}}
    <div class="card shadow-sm mb-3 dr-filter-card">
        <div class="card-header bg-light border-bottom py-2 px-3 font-weight-bold small text-uppercase text-danger">
            <i class="fa-solid fa-filter mr-1"></i>Filter
        </div>
        <div class="card-body py-3 px-3">
            <div class="row align-items-end">

                <div class="col-6 col-sm-3 col-md-2 mb-2">
                    <label class="font-weight-bold small text-uppercase mb-1">
                        From Date <i class="fa-regular fa-calendar ml-1"></i>
                    </label>
                    <input type="date" id="dr-fromdate" class="form-control form-control-sm" value="{{ $ncurdate }}">
                </div>

                <div class="col-6 col-sm-3 col-md-2 mb-2">
                    <label class="font-weight-bold small text-uppercase mb-1">
                        To Date <i class="fa-regular fa-calendar ml-1"></i>
                    </label>
                    <input type="date" id="dr-todate" class="form-control form-control-sm" value="{{ $ncurdate }}">
                </div>

                <div class="col-6 col-sm-3 col-md-2 mb-2">
                    <label class="font-weight-bold small text-uppercase mb-1">
                        Room No <i class="fa-solid fa-door-closed ml-1"></i>
                    </label>
                    <input type="text" id="dr-roomno" class="form-control form-control-sm"
                           placeholder="e.g. 101" maxlength="20" autocomplete="off">
                </div>

                <div class="col-6 col-sm-3 col-md-2 mb-2">
                    <label class="font-weight-bold small text-uppercase mb-1">
                        Status <i class="fa-solid fa-circle-check ml-1"></i>
                    </label>
                    <select id="dr-status" class="form-control form-control-sm">
                        <option value="All">All</option>
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Resolved">Resolved</option>
                    </select>
                </div>

                <div class="col-6 col-sm-3 col-md-2 mb-2">
                    <button type="button" id="dr-refresh" class="btn btn-danger btn-sm w-100 font-weight-bold">
                        <i class="fa-solid fa-arrows-rotate mr-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SUMMARY STAT CARDS ── --}}
    <div class="row mb-3 dr-stats">
        <div class="col-6 col-md-3 mb-2">
            <div class="card dr-stat shadow-sm">
                <div class="card-body py-2 px-3 d-flex align-items-center">
                    <div class="stat-icon bg-secondary text-white mr-3">
                        <i class="fa-solid fa-clipboard-list"></i>
                    </div>
                    <div>
                        <div class="text-uppercase small text-muted font-weight-bold">Total</div>
                        <div class="stat-num" id="dr-cnt-total">0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
            <div class="card dr-stat shadow-sm">
                <div class="card-body py-2 px-3 d-flex align-items-center">
                    <div class="stat-icon bg-warning text-dark mr-3">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <div class="text-uppercase small text-muted font-weight-bold">Pending</div>
                        <div class="stat-num" id="dr-cnt-pending">0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
            <div class="card dr-stat shadow-sm">
                <div class="card-body py-2 px-3 d-flex align-items-center">
                    <div class="stat-icon bg-info text-white mr-3">
                        <i class="fa-solid fa-person-digging"></i>
                    </div>
                    <div>
                        <div class="text-uppercase small text-muted font-weight-bold">In Progress</div>
                        <div class="stat-num" id="dr-cnt-inprogress">0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
            <div class="card dr-stat shadow-sm">
                <div class="card-body py-2 px-3 d-flex align-items-center">
                    <div class="stat-icon bg-success text-white mr-3">
                        <i class="fa-solid fa-check-double"></i>
                    </div>
                    <div>
                        <div class="text-uppercase small text-muted font-weight-bold">Resolved</div>
                        <div class="stat-num" id="dr-cnt-resolved">0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── PRINT HEADER ── --}}
    <div class="dr-print-header">
        <h4 id="dr-ph-comp"></h4>
        <p style="margin:0;font-size:13px;" id="dr-ph-addr"></p>
        <p style="margin:0;font-size:13px;" id="dr-ph-loc"></p>
        <p style="margin:4px 0 0;font-size:14px;font-weight:700;">Damage Report</p>
        <p style="margin:0;font-size:13px;text-align:left;">
            From: <span id="dr-ph-from"></span> &nbsp; To: <span id="dr-ph-to"></span>
            &nbsp;&nbsp; Room: <span id="dr-ph-room"></span> &nbsp;&nbsp; Status: <span id="dr-ph-status"></span>
        </p>
        <hr>
    </div>

    {{-- ── RESULTS CARD ── --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center py-2 px-3"
             style="background:linear-gradient(135deg,#7f1d1d,#dc2626);">
            <span class="font-weight-bold text-white small text-uppercase">
                <i class="fa-solid fa-table-list mr-2"></i>Damage Records
            </span>
            <span class="text-white small" id="dr-count" style="opacity:.75"></span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="dr-table"
                       class="table table-hover table-striped table-bordered align-middle mb-0"
                       style="width:100%;">
                    <thead>
                        <tr>
                            <th style="width:45px;">SN</th>
                            <th style="width:110px;">Damage ID</th>
                            <th style="width:95px;">Date</th>
                            <th style="width:80px;">Room No</th>
                            <th style="width:110px;">Damage Type</th>
                            <th>Item</th>
                            <th>Description</th>
                            <th style="width:150px;">Status</th>
                            <th style="width:100px;">Reported By</th>
                            <th style="width:120px;">Entry Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="fa-solid fa-filter mr-1"></i>
                                Select date range and click <strong>Refresh</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-light py-2 px-3 text-muted small" id="dr-footer">
            &nbsp;
        </div>
    </div>

</div>
</div>

<script>
$(document).ready(function () {

    var drTable = null;

    /* ── tiny escape helper (XSS-safe) ─────────────── */
    function esc(s) {
        if (s === null || s === undefined) return '';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;')
                        .replace(/>/g,'&gt;').replace(/"/g,'&quot;')
                        .replace(/'/g,'&#039;');
    }

    /* ── date display helper ───────────────────────── */
    function drDmy(dt) {
        if (!dt) return '';
        var p = String(dt).split('-');
        if (p.length === 3) {
            return p[2] + '-' + p[1] + '-' + p[0];
        }
        var d = new Date(dt);
        if (isNaN(d)) return dt;
        var m = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return ('0'+d.getDate()).slice(-2)+'-'+m[d.getMonth()]+'-'+d.getFullYear();
    }

    /* ── datetime display helper (u_entdt) ─────────── */
    function drDmyHms(dt) {
        if (!dt) return '';
        return drDmy(dt.substring(0,10)) + (dt.length > 10 ? ' ' + dt.substring(11,16) : '');
    }

    /* ── status badge html ─────────────────────────── */
    function drBadge(status) {
        switch (status) {
            case 'Pending':     return '<span class="badge bg-warning text-dark">Pending</span>';
            case 'In Progress': return '<span class="badge bg-info text-dark">In Progress</span>';
            case 'Resolved':    return '<span class="badge bg-success">Resolved</span>';
            default:            return '<span class="badge bg-secondary">' + esc(status) + '</span>';
        }
    }

    /* ── status select options ─────────────────────── */
    function drStatusSelect(sn, current) {
        var opts = ['Pending', 'In Progress', 'Resolved'];
        var html = '<select class="dr-status-select form-control" data-sn="' + sn + '" data-prev="' + current + '">';
        opts.forEach(function (o) {
            html += '<option value="' + o + '"' + (o === current ? ' selected' : '') + '>' + o + '</option>';
        });
        return html + '</select>';
    }

    /* ── Load data ─────────────────────────────────── */
    function drLoad() {
        var from = $('#dr-fromdate').val();
        var to   = $('#dr-todate').val();
        var room = $.trim($('#dr-roomno').val() || '');
        var status = $('#dr-status').val();
        var csrf = $('#dr-csrf').val();

        if (!from || !to) { Swal.fire({ icon:'warning', title:'Please select both From Date and To Date.' }); return; }
        if (from > to)    { Swal.fire({ icon:'warning', title:'From Date cannot be greater than To Date.' }); return; }

        /* update print header */
        $('#dr-ph-comp').text($('#dr-compname').val());
        $('#dr-ph-addr').text($('#dr-address').val());
        $('#dr-ph-loc').text($('#dr-state').val()+' - '+$('#dr-city').val()+' - '+$('#dr-pin').val());
        $('#dr-ph-from').text(drDmy(from));
        $('#dr-ph-to').text(drDmy(to));
        $('#dr-ph-room').text(room || 'All');
        $('#dr-ph-status').text(status);

        /* destroy old table */
        if (drTable) { drTable.destroy(); drTable = null; }

        $('#dr-table tbody').html(
            '<tr><td colspan="10" class="text-center text-muted py-4">' +
            '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Loading...</td></tr>'
        );
        $('#dr-count').text('');
        $('#dr-footer').html('&nbsp;');
        $('#dr-cnt-total').text('0');
        $('#dr-cnt-pending').text('0');
        $('#dr-cnt-inprogress').text('0');
        $('#dr-cnt-resolved').text('0');

        $.ajax({
            url: '{{ route("fetchdamagereportdata") }}',
            method: 'POST',
            data: { _token: csrf, fromdate: from, todate: to, roomno: room, status: status },
            success: function (res) {
                var rows = res.data || [];
                var cnt = res.counts || {};

                /* summary counts */
                $('#dr-cnt-total').text(cnt.total      || 0);
                $('#dr-cnt-pending').text(cnt.pending   || 0);
                $('#dr-cnt-inprogress').text(cnt.inprogress || 0);
                $('#dr-cnt-resolved').text(cnt.resolved  || 0);

                if (rows.length === 0) {
                    $('#dr-table tbody').html(
                        '<tr><td colspan="10" class="text-center text-muted py-5">' +
                        '<i class="fa-solid fa-circle-info mr-1"></i>' +
                        'No records found for the selected filters.</td></tr>'
                    );
                    $('#dr-count').text('0 records');
                    $('#dr-footer').text('No records found.');
                    return;
                }

                var tbody = '';
                rows.forEach(function (row, i) {
                    tbody +=
                        '<tr>' +
                        '<td class="text-center">'      + (i + 1) + '</td>' +
                        '<td class="text-center font-weight-bold text-danger">DR/' + esc(row.propertyid) + '/' + esc(row.damageid) + '</td>' +
                        '<td class="text-center">'      + drDmy(row.date) + '</td>' +
                        '<td class="text-center font-weight-bold">' + esc(row.roomno) + '</td>' +
                        '<td>'                          + esc(row.damagetype) + '</td>' +
                        '<td>'                          + esc(row.item) + '</td>' +
                        '<td style="max-width:220px;">' + esc(row.description) + '</td>' +
                        '<td class="text-center">'      + drBadge(row.status) + '<br>' + drStatusSelect(row.sn, row.status) + '</td>' +
                        '<td class="text-center">'      + esc(row.u_name) + '</td>' +
                        '<td class="text-center">'      + drDmyHms(row.u_entdt) + '</td>' +
                        '</tr>';
                });

                $('#dr-table tbody').html(tbody);

                /* init DataTable */
                drTable = $('#dr-table').DataTable({
                    dom: 'Bfrtip',
                    pageLength: 25,
                    ordering: true,
                    autoWidth: false,
                    columnDefs: [
                        { targets: 0, width: '45px',  className: 'text-center' },
                        { targets: 1, width: '110px', className: 'text-center' },
                        { targets: 2, width: '95px',  className: 'text-center' },
                        { targets: 3, width: '80px',  className: 'text-center' },
                        { targets: 7, orderable: false },
                        { targets: 9, className: 'text-center' }
                    ],
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel-o mr-1"></i>Excel',
                            className: 'btn btn-success btn-sm',
                            title: $('#dr-compname').val() + ' — Damage Report',
                            filename: 'Damage_Report_' + from + '_to_' + to,
                            /* export text columns only (skip status select HTML) */
                            exportOptions: { columns: [0,1,2,3,4,5,6,8,9] }
                        },
                        {
                            extend: 'print',
                            text: '<i class="fa-solid fa-print mr-1"></i>Print',
                            className: 'btn btn-secondary btn-sm',
                            exportOptions: { columns: ':visible' },
                            customize: function (win) {
                                $(win.document.body).prepend(
                                    '<div style="text-align:center;margin-bottom:12px;">' +
                                    '<h4>'  + $('#dr-compname').val() + '</h4>' +
                                    '<p style="margin:0;font-size:13px;">' + $('#dr-address').val() + '</p>' +
                                    '<p style="margin:0;font-size:13px;">' + $('#dr-state').val() + ' - ' + $('#dr-city').val() + ' - ' + $('#dr-pin').val() + '</p>' +
                                    '<p style="margin:4px 0 0;font-size:14px;font-weight:700;">Damage Report</p>' +
                                    '<p style="margin:0;font-size:13px;text-align:left;">From: ' + drDmy(from) + ' &nbsp; To: ' + drDmy(to) + ' &nbsp;&nbsp; Room: ' + (room || 'All') + ' &nbsp;&nbsp; Status: ' + status + '</p>' +
                                    '<hr></div>'
                                );
                            }
                        }
                    ]
                });

                $('#dr-count').text(rows.length + ' record(s)');
                $('#dr-footer').text(
                    'Showing ' + rows.length + ' record(s) from ' + drDmy(from) + ' to ' + drDmy(to) +
                    (room ? ' | Room: ' + room : '') + ' | Status: ' + status
                );
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error fetching data. Please try again.';
                $('#dr-table tbody').html(
                    '<tr><td colspan="10" class="text-center text-danger py-4">' +
                    '<i class="fa-solid fa-triangle-exclamation mr-1"></i>' + esc(msg) + '</td></tr>'
                );
            }
        });
    }

    /* ── Refresh button ────────────────────────────── */
    $('#dr-refresh').on('click', drLoad);

    /* ── Status change (inline update) ─────────────── */
    $(document).on('change', '.dr-status-select', function () {
        var $sel  = $(this);
        var sn    = $sel.data('sn');
        var prev  = $sel.data('prev');
        var newStatus = $sel.val();

        if (newStatus === prev) { return; }   /* no actual change */

        Swal.fire({
            title: 'Update Status?',
            text:  'Set this damage report to "' + newStatus + '"?',
            icon:  'question',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Yes, update',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (!result.isConfirmed) {
                $sel.val(prev);   /* revert without refetch */
                return;
            }

            $.ajax({
                url: '{{ route("updatedamagereport") }}',
                method: 'POST',
                data: { _token: $('#dr-csrf').val(), sn: sn, status: newStatus },
                success: function (res) {
                    Swal.fire({ icon:'success', title:'Updated!', text: res.message,
                                timer: 1500, showConfirmButton: false });
                    drLoad();
                },
                error: function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Update failed.';
                    Swal.fire({ icon:'error', title:'Error', text: msg });
                    $sel.val(prev);
                }
            });
        });
    });

});
</script>

@endsection
