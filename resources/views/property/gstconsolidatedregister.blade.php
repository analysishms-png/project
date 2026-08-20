@extends('property.layouts.main')
@section('main-container')
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <style>
        @media print {
            .none { display: none !important; }
            .titlep { display: block !important; text-align: center !important; }
            #gsttable thead th.none { display: none !important; }
            #gsttable tbody td.none { display: none !important; }
        }
        .summary-table th { background: #4472C4 !important; color: #fff !important; }
        .grand-total td { font-weight: 700 !important; background: #f0f0f0 !important; }
    </style>

    <div class="content-body">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">GST Consolidated Register</h4>
                            <p class="text-muted mb-3">Unified outward-supply tax view across Room Revenue, POS &amp; Banquet — for GSTR-1/3B reconciliation.</p>

                            {{-- Filters --}}
                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <label>From Date</label>
                                    <input type="date" id="fromdate" class="form-control" value="{{ $fromdate }}">
                                </div>
                                <div class="col-md-2">
                                    <label>To Date</label>
                                    <input type="date" id="todate" class="form-control" value="{{ $fromdate }}">
                                </div>
                                <div class="col-md-2">
                                    <label>Source</label>
                                    <select id="source" class="form-control">
                                        <option value="all">All Sources</option>
                                        <option value="rooms">Room Revenue</option>
                                        <option value="pos">POS</option>
                                        <option value="banquet">Banquet</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button id="fetchBtn" class="btn btn-primary mr-2" onclick="fetchData()">Fetch</button>
                                    <button class="btn btn-success mr-2" onclick="window.print()">🖨️ Print</button>
                                    <a id="excelBtn" href="#" class="btn btn-info" onclick="exportExcel()">📥 Excel</a>
                                </div>
                            </div>

                            {{-- Detail Table --}}
                            <div class="table-responsive">
                                <table id="gsttable" class="table table-bordered table-sm" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Source</th>
                                            <th>Bill No</th>
                                            <th>Date</th>
                                            <th>GSTIN</th>
                                            <th>Party</th>
                                            <th class="text-right">Taxable (₹)</th>
                                            <th class="text-right">Rate %</th>
                                            <th class="text-right">CGST (₹)</th>
                                            <th class="text-right">SGST (₹)</th>
                                            <th class="text-right">IGST (₹)</th>
                                            <th class="text-right">Total Tax (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr class="grand-total">
                                            <td colspan="5"><strong>GRAND TOTAL</strong></td>
                                            <td class="text-right" id="gBaseValue">0.00</td>
                                            <td></td>
                                            <td class="text-right" id="gCGST">0.00</td>
                                            <td class="text-right" id="gSGST">0.00</td>
                                            <td class="text-right" id="gIGST">0.00</td>
                                            <td class="text-right" id="gTotalTax">0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            {{-- Summary by GSTIN + Rate --}}
                            <h5 class="mt-4">Summary by GSTIN + Rate</h5>
                            <div class="table-responsive">
                                <table id="summarytable" class="table table-bordered table-sm summary-table" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>GSTIN</th>
                                            <th class="text-right">Rate %</th>
                                            <th class="text-right">Base Value (₹)</th>
                                            <th class="text-right">CGST (₹)</th>
                                            <th class="text-right">SGST (₹)</th>
                                            <th class="text-right">IGST (₹)</th>
                                            <th class="text-right">Total Tax (₹)</th>
                                            <th class="text-right">Bills</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr class="grand-total">
                                            <td colspan="2"><strong>GRAND TOTAL</strong></td>
                                            <td class="text-right" id="sBaseValue">0.00</td>
                                            <td class="text-right" id="sCGST">0.00</td>
                                            <td class="text-right" id="sSGST">0.00</td>
                                            <td class="text-right" id="sIGST">0.00</td>
                                            <td class="text-right" id="sTotalTax">0.00</td>
                                            <td class="text-right" id="sBills">0</td>
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
    var dt, st;

    function fmt(n) { return parseFloat(n || 0).toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2}); }

    function fetchData() {
        $.ajax({
            url: '{{ route("gstconsolidatedregisterfetch") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                fromdate: $('#fromdate').val(),
                todate:   $('#todate').val(),
                source:   $('#source').val()
            },
            success: function(r) {
                // Detail table
                dt.clear();
                var gBase=0, gCGST=0, gSGST=0, gIGST=0, gTax=0;
                (r.data || []).forEach(function(row) {
                    gBase += row.BaseValue; gCGST += row.CGSTAmt; gSGST += row.SGSTAmt;
                    gIGST += row.IGSTAmt; gTax += row.TotalTax;
                    dt.row.add([
                        row.Source, row.BillNo, row.VDate ? row.VDate.substring(0,10) : '',
                        row.GSTIN, row.PartyName,
                        fmt(row.BaseValue), row.TaxPer + '%',
                        fmt(row.CGSTAmt), fmt(row.SGSTAmt), fmt(row.IGSTAmt), fmt(row.TotalTax)
                    ]).draw(false);
                });
                dt.draw();
                $('#gBaseValue').text(fmt(gBase)); $('#gCGST').text(fmt(gCGST));
                $('#gSGST').text(fmt(gSGST)); $('#gIGST').text(fmt(gIGST));
                $('#gTotalTax').text(fmt(gTax));

                // Summary table
                st.clear();
                (r.summary || []).forEach(function(s) {
                    st.row.add([
                        s.GSTIN, s.TaxPer + '%',
                        fmt(s.BaseValue), fmt(s.CGSTAmt), fmt(s.SGSTAmt), fmt(s.IGSTAmt),
                        fmt(s.TotalTax), s.BillCount
                    ]).draw(false);
                });
                st.draw();
                var sg = r.grand || {};
                $('#sBaseValue').text(fmt(sg.BaseValue)); $('#sCGST').text(fmt(sg.CGSTAmt));
                $('#sSGST').text(fmt(sg.SGSTAmt)); $('#sIGST').text(fmt(sg.IGSTAmt));
                $('#sTotalTax').text(fmt(sg.TotalTax)); $('#sBills').text(sg.BillCount || 0);
            },
            error: function(xhr) {
                alert('Error fetching data: ' + (xhr.responseText || 'Unknown error'));
            }
        });
    }

    function exportExcel() {
        window.location.href = '{{ route("exportgstconsolidatedregister") }}?fromdate='
            + $('#fromdate').val() + '&todate=' + $('#todate').val() + '&source=' + $('#source').val();
    }

    $(function() {
        dt = $('#gsttable').DataTable({ dom: 'Bfrtip', buttons: [], pageLength: 50, order: [[2,'asc']] });
        st = $('#summarytable').DataTable({ dom: 'Bfrtip', buttons: [], paging: false, searching: false, order: [[0,'asc']] });
    });
    </script>
@endsection
