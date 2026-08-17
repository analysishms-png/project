@extends('property.layouts.main')

@section('main-container')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <style>
        .report-card {
            margin-top: 20px;
        }

        .report-toolbar {
            margin-bottom: 20px;
        }

        .text-right {
            text-align: right;
        }
    </style>

    <div class="content-body">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card shadow report-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Day Book</h5>
                        </div>
                        <div class="card-body">
                            <div class="report-header text-center mb-4">
                                <h4 class="mb-1">{{ $companyName }}</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-start mb-3 gap-2">
                                <button type="button" id="backButton" class="btn btn-secondary btn-sm">← Back</button>
                                <button type="button" id="excelButton" class="btn btn-success btn-sm">Excel</button>
                                <button type="button" id="printButton" class="btn btn-info btn-sm text-white">Print</button>
                                <span id="printUrl" style="display:none;">{{ route('printdaybook') }}</span>
                            </div>
                            <div class="row report-toolbar align-items-center">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fromdate" class="col-form-label mb-0">From Date</label>
                                        <input type="date" id="fromdate" class="form-control" value="{{ $fromdate }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="todate" class="col-form-label mb-0">To Date</label>
                                        <input type="date" id="todate" class="form-control" value="{{ $todate }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="vtypeFilter" class="col-form-label mb-0">Voucher Type (optional)</label>
                                        <select id="vtypeFilter" class="form-control">
                                            <option value="">-- All Voucher Types --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex align-items-center">
                                    <button id="refreshButton" class="btn btn-success">Refresh <i class="fa-solid fa-arrows-rotate"></i></button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div id="report-status" class="text-end text-muted"></div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="day-book-table" class="table table-bordered table-striped table-hover">
                                    <thead class="bg-secondary text-white">
                                        <tr>
                                            <th style="width: 9%;">Date</th>
                                            <th style="width: 8%;">Vch Type</th>
                                            <th style="width: 11%;">Vch No</th>
                                            <th style="width: 17%;">Doc ID</th>
                                            <th style="width: 18%;">A/C Name</th>
                                            <th style="width: 22%;">Narration</th>
                                            <th class="text-right" style="width: 8%;">Debit</th>
                                            <th class="text-right" style="width: 8%;">Credit</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot class="bg-light fw-bold">
                                        <tr>
                                            <td colspan="6" class="text-end">Grand Total</td>
                                            <td class="text-right" id="total-dr">0.00</td>
                                            <td class="text-right" id="total-cr">0.00</td>
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

    <script>
        $(document).ready(function() {
            function formatNumber(value) {
                return Number(value || 0).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function loadVtypes() {
                $.ajax({
                    url: '{{ route('daybook.vtypes') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        const sel = $('#vtypeFilter');
                        sel.empty();
                        sel.append('<option value="">-- All Voucher Types --</option>');
                        (response.data || []).forEach(vt => {
                            sel.append(`<option value="${vt}">${vt}</option>`);
                        });
                    }
                });
            }

            function loadDayBook() {
                const fromdate = $('#fromdate').val();
                const todate = $('#todate').val();

                if (!fromdate || !todate) {
                    alert('Please select both From Date and To Date.');
                    return;
                }

                $('#report-status').text('Loading...');
                $('#day-book-table tbody').empty();
                $('#total-dr').text('0.00');
                $('#total-cr').text('0.00');

                $.ajax({
                    url: '{{ route('daybook.fetch') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        fromdate: fromdate,
                        todate: todate,
                        vtype: $('#vtypeFilter').val() || ''
                    },
                    success: function(response) {
                        const rows = response.data || [];
                        if (rows.length === 0) {
                            $('#report-status').text('No data found for the selected date range.');
                            return;
                        }

                        let html = '';
                        rows.forEach(r => {
                            html += `<tr>
                                <td>${r.vdate}</td>
                                <td>${r.vtype}</td>
                                <td>${r.vprefix || ''} ${r.vno || ''}</td>
                                <td>${r.docid}</td>
                                <td>${r.account_name || r.subcode || ''}</td>
                                <td>${r.narration || ''}</td>
                                <td class="text-right">${r.amtdr > 0 ? formatNumber(r.amtdr) : ''}</td>
                                <td class="text-right">${r.amtcr > 0 ? formatNumber(r.amtcr) : ''}</td>
                            </tr>`;
                        });

                        $('#day-book-table tbody').html(html);
                        $('#total-dr').text(formatNumber(response.total_dr || 0));
                        $('#total-cr').text(formatNumber(response.total_cr || 0));
                        $('#report-status').text('Report refreshed successfully.');
                    },
                    error: function(xhr) {
                        $('#report-status').text('Unable to load data.');
                        console.error(xhr.responseText || xhr.statusText);
                    }
                });
            }

            function exportToExcel() {
                const fromDate = $('#fromdate').val();
                const toDate = $('#todate').val();
                if (!fromDate || !toDate) {
                    alert('Please select both From Date and To Date.');
                    return;
                }
                let url = '{{ route('daybook.export') }}'
                    + '?fromdate=' + encodeURIComponent(fromDate)
                    + '&todate=' + encodeURIComponent(toDate);
                const vtype = $('#vtypeFilter').val();
                if (vtype) {
                    url += '&vtype=' + encodeURIComponent(vtype);
                }
                window.location.href = url;
            }

            function printReport() {
                const fromDate = $('#fromdate').val();
                const toDate = $('#todate').val();
                const baseUrl = $('#printUrl').text().trim();
                if (!baseUrl) {
                    alert('Print route is not configured.');
                    return;
                }
                let url = baseUrl + '?fromdate=' + encodeURIComponent(fromDate) + '&todate=' + encodeURIComponent(toDate);
                const vtype = $('#vtypeFilter').val();
                if (vtype) {
                    url += '&vtype=' + encodeURIComponent(vtype);
                }
                window.open(url, '_blank');
            }

            $('#refreshButton').on('click', loadDayBook);
            $('#backButton').on('click', function() { window.history.back(); });
            $('#excelButton').on('click', exportToExcel);
            $('#printButton').on('click', printReport);

            loadVtypes();
            loadDayBook();
        });
    </script>
@endsection
