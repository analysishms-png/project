@extends('property.layouts.main')
@section('main-container')
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>
    <link href="https://unpkg.com/tabulator-tables@6.3.0/dist/css/tabulator.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="https://oss.sheetjs.com/sheetjs/xlsx.full.min.js"></script>
    <script src="https://unpkg.com/tabulator-tables@6.3.0/dist/js/tabulator.min.js"></script>
    <style>
        .custom-header {
            background-color: #777575;
            text-align: center;
            padding: 15px;
            font-size: 18px;
            font-weight: bold;
            border: 1px solid #ddd;
            margin: 10px 0 -17px 0;
            color: white;
            display: none;
        }

        .tabulator-col .tabulator-arrow {
            display: none !important;
        }

        .recon-flag-danger {
            background: #dc3545;
            color: #fff;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .recon-flag-success {
            background: #28a745;
            color: #fff;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .recon-flag-warning {
            background: #ffc107;
            color: #212529;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .recon-flag-secondary {
            background: #6c757d;
            color: #fff;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .detail-section-title {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            margin: 14px 0 8px 0;
        }
    </style>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="" method="post">
                                <input type="hidden" value="{{ $company->propertyid }}" id="propertyid" name="propertyid">
                                <input type="hidden" value="{{ $company->comp_name }}" id="compname" name="compname">
                                <div class="text-center titlep">
                                    <h3>{{ $company->comp_name }}</h3>
                                    <p style="margin-top:-10px; font-size:16px;">{{ $company->address1 }}</p>
                                    <p style="margin-top:-10px; font-size:16px;">
                                        {{ $statename . ' - ' . $company->city . ' - ' . $company->pin }}</p>
                                    <p style="margin-top:-10px; font-size:16px;">Advance / Folio Reconciliation Report</p>
                                    <p style="text-align:left;margin-top:-10px; font-size:16px;">From Date: <span
                                            id="fromdatep"></span> To Date: <span id="todatep"></span></p>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="fromdate" class="col-form-label">From Date</label>
                                            <input type="date" value="{{ $ncurdate }}" class="form-control"
                                                name="fromdate" id="fromdate">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="todate" class="col-form-label">To Date</label>
                                            <input type="date" value="{{ $ncurdate }}" class="form-control"
                                                name="todate" id="todate">
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="button" id="refreshbutton" class="btn btn-primary">Refresh</button>
                                    </div>
                                </div>
                            </form>
                            <div class="mt-3">
                                <button id="print-table" class="btn btn-primary">Print <i
                                        class="fa-solid fa-print"></i></button>
                                <button id="download-xlsx" class="btn btn-success">Excel <i
                                        class="fa fa-file-excel-o"></i></button>
                                <span class="text-muted ms-2"><i class="fa-solid fa-circle-info"></i> Click a row to view
                                    the full advance trace (original transaction, folio transfer, deletion history). This
                                    report is read-only.</span>
                            </div>
                            <div class="custom-header">Advance / Folio Reconciliation</div>
                            <div class="mt-3" id="kot-table"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="reconDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title"><i class="fas fa-search-dollar me-2"></i>Advance Trace Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        style="filter: invert(1);"></button>
                </div>
                <div class="modal-body" id="reconDetailBody">
                    <div class="text-center text-muted py-4">Loading...</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function dmy(d) {
            if (!d) return '';
            var parts = String(d).split('-');
            if (parts.length === 3) {
                return parts[2] + '-' + parts[1] + '-' + parts[0];
            }
            return d;
        }

        $(document).ready(function() {
            let table;

            function showLoading() {
                if (typeof showLoader === 'function') showLoader();
            }

            function hideLoading() {
                if (typeof hideLoader === 'function') hideLoader();
            }

            function flagCell(cell, formatterParams, onRendered) {
                const val = cell.getValue();
                let cls = 'recon-flag-success';
                if (formatterParams.flagType === 'danger') cls = 'recon-flag-danger';
                if (formatterParams.flagType === 'warning') cls = 'recon-flag-warning';
                if (formatterParams.flagType === 'secondary') cls = 'recon-flag-secondary';
                return '<span class="' + cls + '">' + val + '</span>';
            }

            $(document).on('click', '#refreshbutton', function() {
                let fromdate = $('#fromdate').val();
                let todate = $('#todate').val();
                if (fromdate == '') {
                    alert('Please select From Date');
                    return;
                }
                if (todate == '') {
                    alert('Please select To Date');
                    return;
                }

                showLoading();
                let fdata = new XMLHttpRequest();
                fdata.open('POST', '/advreconreportfetch', true);
                fdata.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                fdata.onreadystatechange = function() {
                    if (fdata.status === 200 && fdata.readyState === 4) {
                        let results = JSON.parse(fdata.responseText);
                        let tabledata = processData(results);

                        if (table) {
                            table.setData(tabledata);
                        } else {
                            let columns = [{
                                title: "Res No.",
                                field: "resno",
                                sorter: "number",
                                width: 110,
                            }, {
                                title: "Res Date",
                                field: "resdate",
                                sorter: "string",
                                width: 110,
                            }, {
                                title: "Guest Name",
                                field: "guestname",
                                sorter: "string",
                                width: 180,
                            }, {
                                title: "Status",
                                field: "status",
                                sorter: "string",
                                width: 110,
                            }, {
                                title: "Rooms",
                                field: "noofrooms",
                                sorter: "number",
                                width: 70,
                            }, {
                                title: "Room No",
                                field: "roomno",
                                sorter: "string",
                                width: 90,
                            }, {
                                title: "Check-In",
                                field: "checkindate",
                                sorter: "string",
                                width: 110,
                            }, {
                                title: "Check-Out",
                                field: "checkoutdate",
                                sorter: "string",
                                width: 110,
                            }, {
                                title: "Res. Advance",
                                field: "resadvance",
                                sorter: "number",
                                width: 120,
                                formatter: "money",
                                formatterParams: { precision: 2 },
                                bottomCalc: "sum",
                                bottomCalcFormatter: "money",
                                bottomCalcFormatterParams: { precision: 2 },
                            }, {
                                title: "Folio Advance",
                                field: "folioadvance",
                                sorter: "number",
                                width: 120,
                                formatter: "money",
                                formatterParams: { precision: 2 },
                                bottomCalc: "sum",
                                bottomCalcFormatter: "money",
                                bottomCalcFormatterParams: { precision: 2 },
                            }, {
                                title: "Deleted (#)",
                                field: "delcount",
                                sorter: "number",
                                width: 90,
                            }, {
                                title: "Deleted Amt",
                                field: "delamount",
                                sorter: "number",
                                width: 120,
                                formatter: "money",
                                formatterParams: { precision: 2 },
                                bottomCalc: "sum",
                                bottomCalcFormatter: "money",
                                bottomCalcFormatterParams: { precision: 2 },
                            }, {
                                title: "Recon",
                                field: "recon",
                                sorter: "number",
                                width: 110,
                                formatter: "money",
                                formatterParams: { precision: 2 },
                                bottomCalc: "sum",
                                bottomCalcFormatter: "money",
                                bottomCalcFormatterParams: { precision: 2 },
                            }, {
                                title: "Pay Mode",
                                field: "paymode",
                                sorter: "string",
                                width: 140,
                            }, {
                                title: "Flag",
                                field: "flag",
                                width: 150,
                                formatter: flagCell,
                            }, ];

                            $('#fromdatep').text(dmy(fromdate));
                            $('#todatep').text(dmy(todate));

                            table = new Tabulator("#kot-table", {
                                data: tabledata,
                                printHeader: $('.titlep').html(),
                                printFooter: "<h2>Copyright @Analysis</h2>",
                                columns: columns,
                                layout: "fitColumns",
                                pagination: "local",
                                paginationSize: 100,
                                tooltips: true,
                                rowClick: function(e, row) {
                                    openDetail(row.getData().docid);
                                },
                            });
                        }
                    }
                };
                fdata.send(
                    `fromdate=${fromdate}&todate=${todate}&_token={{ csrf_token() }}`
                );
                hideLoading();
            });

            $("#print-table").on("click", function() {
                if (table) table.print(false, true);
            });

            $("#download-xlsx").on("click", function() {
                if (table) table.download("xlsx", "advance_reconciliation.xlsx", {
                    sheetName: "Advance Reconciliation"
                });
            });

            function processData(results) {
                let reportData = [];
                results.forEach(function(row) {
                    // Only surface reservations with advance activity
                    if (parseFloat(row.ResAdvance) === 0 && parseFloat(row.FolioAdvance) === 0 &&
                        parseInt(row.DelCount) === 0) {
                        return;
                    }
                    reportData.push({
                        docid: row.DocId,
                        resno: row.ResNo,
                        resdate: dmy(row.ResDate),
                        guestname: row.GuestName,
                        status: row.ResStatus,
                        noofrooms: row.NoofRooms,
                        roomno: row.RoomNo || '',
                        checkindate: dmy(row.CheckInDate),
                        checkoutdate: dmy(row.CheckOutDate),
                        resadvance: parseFloat(row.ResAdvance),
                        folioadvance: parseFloat(row.FolioAdvance),
                        delcount: row.DelCount,
                        delamount: parseFloat(row.DelAmount),
                        recon: parseFloat(row.Recon),
                        paymode: row.PayMode || '',
                        flag: row.Flag,
                        flagtype: row.FlagType,
                    });
                });
                hideLoading();
                return reportData;
            }

            function openDetail(docid) {
                if (!docid) return;
                $('#reconDetailBody').html('<div class="text-center text-muted py-4">Loading...</div>');
                $('#reconDetailModal').modal('show');

                $.ajax({
                    url: '/advreconreportdetail',
                    method: 'POST',
                    data: {
                        docid: docid,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (!res.status) {
                            $('#reconDetailBody').html('<div class="alert alert-warning">' + (res.message ||
                                'No data') + '</div>');
                            return;
                        }
                        $('#reconDetailBody').html(renderDetail(res));
                    },
                    error: function() {
                        $('#reconDetailBody').html(
                            '<div class="alert alert-danger">Failed to load detail.</div>');
                    }
                });
            }

            function fmt(v) {
                let n = parseFloat(v);
                if (isNaN(n)) n = 0;
                return n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function renderDetail(res) {
                let b = res.booking || {};
                let html = '';

                html += '<div class="row"><div class="col-md-12"><strong>Reservation:</strong> ' +
                    (b.BookNo || '') + ' &nbsp;|&nbsp; <strong>Guest:</strong> ' + (b.GuestName || '') +
                    ' &nbsp;|&nbsp; <strong>Status:</strong> ' + (b.ResStatus || '') +
                    (String(b.Cancel || '').toUpperCase() === 'Y' ? ' <span class="badge bg-secondary">CANCELLED</span>' : '') +
                    ' &nbsp;|&nbsp; <strong>DocId:</strong> <code>' + (b.DocId || '') + '</code>' +
                    renderRestoreButton(b, res) + '</div></div>';

                if (res.folios && res.folios.length) {
                    html += '<div class="detail-section-title">Check-in / Folio</div><table class="table table-sm table-bordered"><thead><tr><th>Folio No</th><th>Folio DocId</th><th>Name</th><th>Folio Date</th></tr></thead><tbody>';
                    res.folios.forEach(function(f) {
                        html += '<tr><td>' + (f.folio_no || '') + '</td><td><code>' + (f.docid || '') +
                            '</code></td><td>' + (f.name || '') + '</td><td>' + (f.vdate || '') + '</td></tr>';
                    });
                    html += '</tbody></table>';
                }
                if (res.rooms && res.rooms.length) {
                    html += '<table class="table table-sm table-bordered"><thead><tr><th>Room</th><th>Check-In</th><th>Check-Out</th></tr></thead><tbody>';
                    res.rooms.forEach(function(r) {
                        html += '<tr><td>' + (r.roomno || '') + '</td><td>' + (r.chkindate || '') + '</td><td>' +
                            (r.chkoutdate || r.depdate || '') + '</td></tr>';
                    });
                    html += '</tbody></table>';
                }

                html += '<div class="detail-section-title">Reservation Advance (ADRES/ARRES) — original transactions</div>';
                if (res.res_advance && res.res_advance.length) {
                    html += '<table class="table table-sm table-bordered"><thead><tr><th>DocId</th><th>VNo</th><th>VDate</th><th>Type</th><th>Pay Mode</th><th>Amt Cr</th><th>Amt Dr</th><th>Entry By</th><th>Entry Date</th><th>Comments</th></tr></thead><tbody>';
                    res.res_advance.forEach(function(r) {
                        html += '<tr><td><code>' + (r.docid || '') + '</code></td><td>' + (r.vno || '') + '</td><td>' +
                            (r.vdate || '') + '</td><td>' + (r.vtype || '') + '</td><td>' + (r.PayModeName || r
                                .paytype || '') + '</td><td>' + fmt(r.amtcr) + '</td><td>' + fmt(r.amtdr) +
                            '</td><td>' + (r.u_name || '') + '</td><td>' + (r.u_entdt || '') + '</td><td>' +
                            (r.comments || '') + '</td></tr>';
                    });
                    html += '</tbody></table>';
                } else {
                    html += '<p class="text-muted">No reservation advance rows found.</p>';
                }

                html += '<div class="detail-section-title">Folio Advance (transferred at check-in)</div>';
                if (res.folio_advance && res.folio_advance.length) {
                    html += '<table class="table table-sm table-bordered"><thead><tr><th>DocId</th><th>FolioNo</th><th>VDate</th><th>Pay Mode</th><th>Amt Cr</th><th>Amt Dr</th><th>Entry By</th><th>Comments</th></tr></thead><tbody>';
                    res.folio_advance.forEach(function(r) {
                        html += '<tr><td><code>' + (r.docid || '') + '</code></td><td>' + (r.foliono || '') +
                            '</td><td>' + (r.vdate || '') + '</td><td>' + (r.PayModeName || r.paytype || '') +
                            '</td><td>' + fmt(r.amtcr) + '</td><td>' + fmt(r.amtdr) + '</td><td>' + (r.u_name ||
                                '') + '</td><td>' + (r.comments || '') + '</td></tr>';
                    });
                    html += '</tbody></table>';
                } else {
                    html += '<p class="text-muted">No folio advance rows found — advance was NOT transferred to any folio.</p>';
                }

                html += '<div class="detail-section-title">Deletion History (paychargelog)</div>';
                if (res.log && res.log.length) {
                    html += '<table class="table table-sm table-bordered"><thead><tr><th>DocId</th><th>VNo</th><th>VDate</th><th>PayCode</th><th>Amt Cr</th><th>Amt Dr</th><th>Deleted By</th><th>Deleted At</th><th>Reason</th></tr></thead><tbody>';
                    res.log.forEach(function(r) {
                        html += '<tr><td><code>' + (r.docid || '') + '</code></td><td>' + (r.vno || '') + '</td><td>' +
                            (r.vdate || '') + '</td><td>' + (r.paycode || '') + '</td><td>' + fmt(r.amtcr) +
                            '</td><td>' + fmt(r.amtdr) + '</td><td>' + (r.u_name || '') + '</td><td>' + (r.u_entdt ||
                                '') + '</td><td>' + (r.remarks || '') + '</td></tr>';
                    });
                    html += '</tbody></table>';
                    html += '<p class="text-muted"><i class="fa-solid fa-circle-info"></i> Historical log rows may show Amt Cr as 0 — the old deletion routine did not copy credit amounts. Presence of a row still proves the deletion, with user, time and reason.</p>';
                } else {
                    html += '<p class="text-muted">No deletion history found for this reservation.</p>';
                }

                return html;
            }

            function renderRestoreButton(b, res) {
                // Show restore only for checked-in, non-cancelled reservations with advance activity
                if (!res.folios || !res.folios.length) return '';
                if (String(b.Cancel || '').toUpperCase() === 'Y') return '';
                let resAdv = 0, folAdv = 0, delAmt = 0;
                (res.res_advance || []).forEach(function(r) { resAdv += (parseFloat(r.amtcr) || 0) - (parseFloat(r.amtdr) || 0); });
                (res.folio_advance || []).forEach(function(r) { folAdv += (parseFloat(r.amtcr) || 0) - (parseFloat(r.amtdr) || 0); });
                (res.log || []).forEach(function(r) { delAmt += (parseFloat(r.amtcr) || 0) - (parseFloat(r.amtdr) || 0); });
                let missing = Math.round((resAdv - folAdv - delAmt) * 100) / 100;
                if (missing <= 0.01) return '';

                return ' &nbsp;|&nbsp; <span class="badge bg-danger">Missing folio advance: Rs ' + fmt(missing) +
                    '</span> <button type="button" class="btn btn-sm btn-danger ms-2" id="btnRestoreAdvance" data-docid="' +
                    (b.DocId || '') + '"><i class="fas fa-undo-alt me-1"></i>Restore / Re-post Advance</button>';
            }

            $(document).on('click', '#btnRestoreAdvance', function() {
                let docid = $(this).data('docid');
                if (!docid) return;
                Swal.fire({
                    title: 'Restore / Re-post Advance?',
                    html: 'This will re-post the <strong>missing folio advance</strong> onto the guest folio for this reservation.<br><br>' +
                        '<span class="text-danger"><i class="fa-solid fa-triangle-exclamation"></i> Financial operation — it is guarded (no duplicates, settled folios and cancelled reservations are refused) and fully audited.</span>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Restore',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: '/advreconrestore',
                        method: 'POST',
                        data: {
                            docid: docid,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (res.status) {
                                Swal.fire('Restored', res.message, 'success');
                                setTimeout(function() { location.reload(); }, 1500);
                            } else {
                                Swal.fire('Not restored', res.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Restore failed.', 'error');
                        }
                    });
                });
            });
        });
    </script>
@endsection
