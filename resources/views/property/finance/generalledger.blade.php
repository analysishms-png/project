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

        .acct-header {
            background-color: #e9ecef;
            font-weight: bold;
        }

        .acct-subtotal {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .txn-row td {
            border-top: 1px solid #dee2e6;
        }
    </style>

    <div class="content-body">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card shadow report-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">General Ledger</h5>
                        </div>
                        <div class="card-body">
                            <div class="report-header text-center mb-4">
                                <h4 class="mb-1">{{ $companyName }}</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-start mb-3 gap-2">
                                <button type="button" id="backButton" class="btn btn-secondary btn-sm">← Back</button>
                                <button type="button" id="excelButton" class="btn btn-success btn-sm">Excel</button>
                                <button type="button" id="printButton" class="btn btn-info btn-sm text-white">Print</button>
                                <span id="printUrl" style="display:none;">{{ route('printgeneralledger') }}</span>
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
                                        <label for="accountFilter" class="col-form-label mb-0">Account (optional)</label>
                                        <select id="accountFilter" class="form-control">
                                            <option value="">-- All Accounts --</option>
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
                                <table id="general-ledger-table" class="table table-bordered table-striped table-hover">
                                    <thead class="bg-secondary text-white">
                                        <tr>
                                            <th style="width: 22%;">A/C Name</th>
                                            <th style="width: 10%;">Date</th>
                                            <th style="width: 15%;">Doc ID</th>
                                            <th style="width: 25%;">Narration</th>
                                            <th style="width: 10%;">Contra</th>
                                            <th class="text-right" style="width: 9%;">Debit</th>
                                            <th class="text-right" style="width: 9%;">Credit</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot class="bg-light fw-bold">
                                        <tr>
                                            <td colspan="5" class="text-end">Grand Total</td>
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

            function loadAccounts() {
                $.ajax({
                    url: '{{ route('generalledger.accounts') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        const sel = $('#accountFilter');
                        sel.empty();
                        sel.append('<option value="">-- All Accounts --</option>');
                        (response.data || []).forEach(acc => {
                            sel.append(`<option value="${acc.sub_code}">${acc.name} (${acc.sub_code})</option>`);
                        });
                    }
                });
            }

            function getSelectedSubcodes() {
                const val = $('#accountFilter').val();
                return val ? [val] : [];
            }

            function loadGeneralLedger() {
                const fromdate = $('#fromdate').val();
                const todate = $('#todate').val();

                if (!fromdate || !todate) {
                    alert('Please select both From Date and To Date.');
                    return;
                }

                $('#report-status').text('Loading...');
                $('#general-ledger-table tbody').empty();
                $('#total-dr').text('0.00');
                $('#total-cr').text('0.00');

                $.ajax({
                    url: '{{ route('generalledger.fetch') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        fromdate: fromdate,
                        todate: todate,
                        subcodes: getSelectedSubcodes()
                    },
                    success: function(response) {
                        const accounts = response.data || [];
                        if (accounts.length === 0) {
                            $('#report-status').text('No data found for the selected date range.');
                            return;
                        }

                        let html = '';
                        let grandDr = 0;
                        let grandCr = 0;

                        accounts.forEach(acc => {
                            html += `<tr class="acct-header">
                                <td colspan="5"><strong>${acc.name}</strong>
                                    <span class="text-muted small">[${acc.group_name || 'Other'}]</span></td>
                                <td class="text-right"><strong>Opening ${formatNumber(acc.opening_balance)}</strong></td>
                                <td></td>
                            </tr>`;

                            (acc.transactions || []).forEach(tx => {
                                html += `<tr class="txn-row">
                                    <td></td>
                                    <td>${tx.vdate}</td>
                                    <td>${tx.docid}</td>
                                    <td>${tx.narration || ''}</td>
                                    <td>${tx.contrasub || ''}</td>
                                    <td class="text-right">${tx.amtdr > 0 ? formatNumber(tx.amtdr) : ''}</td>
                                    <td class="text-right">${tx.amtcr > 0 ? formatNumber(tx.amtcr) : ''}</td>
                                </tr>`;
                            });

                            html += `<tr class="acct-subtotal">
                                <td colspan="4" class="text-end">Sub Total</td>
                                <td class="text-end small text-muted">Closing ${formatNumber(acc.closing_balance)}</td>
                                <td class="text-right">${formatNumber(acc.total_dr)}</td>
                                <td class="text-right">${formatNumber(acc.total_cr)}</td>
                            </tr>`;

                            grandDr += Number(acc.total_dr || 0);
                            grandCr += Number(acc.total_cr || 0);
                        });

                        $('#general-ledger-table tbody').html(html);
                        $('#total-dr').text(formatNumber(grandDr));
                        $('#total-cr').text(formatNumber(grandCr));
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
                const subcodes = getSelectedSubcodes();
                let url = '{{ route('generalledger.export') }}'
                    + '?fromdate=' + encodeURIComponent(fromDate)
                    + '&todate=' + encodeURIComponent(toDate);
                if (subcodes.length) {
                    url += '&subcodes=' + encodeURIComponent(subcodes.join(','));
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
                const subcodes = getSelectedSubcodes();
                let url = baseUrl + '?fromdate=' + encodeURIComponent(fromDate) + '&todate=' + encodeURIComponent(toDate);
                if (subcodes.length) {
                    url += '&subcodes=' + encodeURIComponent(subcodes.join(','));
                }
                window.open(url, '_blank');
            }

            $('#refreshButton').on('click', loadGeneralLedger);
            $('#backButton').on('click', function() { window.history.back(); });
            $('#excelButton').on('click', exportToExcel);
            $('#printButton').on('click', printReport);

            loadAccounts();
            loadGeneralLedger();
        });
    </script>
@endsection
