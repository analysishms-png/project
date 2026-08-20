@extends('property.layouts.main')
@section('main-container')
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <style>
        .metric-card { text-align: center; padding: 15px; border-radius: 8px; margin-bottom: 10px; }
        .metric-card h3 { margin: 0; font-size: 28px; }
        .metric-card p { margin: 2px 0 0; font-size: 12px; color: #666; }
        .bg-occupancy { background: #e3f2fd; color: #1565c0; }
        .bg-revenue   { background: #e8f5e9; color: #2e7d32; }
        .bg-unsettled { background: #fff3e0; color: #e65100; }
        .bg-prev      { background: #f3e5f5; color: #7b1fa2; }
        .section-title { font-size: 14px; font-weight: 600; margin: 15px 0 8px; border-bottom: 2px solid #4472C4; padding-bottom: 4px; }
        .grand-total td { font-weight: 700; background: #f0f0f0 !important; }
    </style>

    <div class="content-body">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Night Audit Reconciliation Report</h4>
                            <p class="text-muted mb-3">Room occupancy vs charges posted vs settlement status — snapshot for the selected date.</p>

                            {{-- Filters --}}
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label>Date</label>
                                    <input type="date" id="fordate" class="form-control" value="{{ $fordate }}">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button class="btn btn-primary" onclick="fetchData()">Fetch</button>
                                </div>
                            </div>

                            {{-- Summary Cards --}}
                            <div class="row mb-4" id="summaryCards" style="display:none">
                                <div class="col-md-3">
                                    <div class="metric-card bg-occupancy">
                                        <h3 id="mTotalRooms">0</h3>
                                        <p>Occupied Rooms</p>
                                        <small id="mPrevOcc">Prev: 0</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card bg-revenue">
                                        <h3 id="mTotalRevenue">₹0</h3>
                                        <p>Total Revenue Posted</p>
                                        <small id="mPrevRev">Prev: ₹0</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card bg-unsettled">
                                        <h3 id="mUnsettled">0</h3>
                                        <p>Unsettled Bills</p>
                                        <small id="mUnsettledAmt">₹0 outstanding</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card bg-prev">
                                        <h3 id="mActiveGuests">0</h3>
                                        <p>Active Guests</p>
                                        <small id="mCheckedOut">0 checked out today</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Revenue by VType --}}
                            <div class="section-title">Revenue by Voucher Type</div>
                            <div class="table-responsive">
                                <table id="revenueTable" class="table table-bordered table-sm" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Voucher Type</th>
                                            <th class="text-right">Bills</th>
                                            <th class="text-right">Debit (₹)</th>
                                            <th class="text-right">Credit (₹)</th>
                                            <th class="text-right">Net Amount (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr class="grand-total">
                                            <td><strong>TOTAL</strong></td>
                                            <td class="text-right" id="rBills">0</td>
                                            <td class="text-right" id="rDr">0.00</td>
                                            <td class="text-right" id="rCr">0.00</td>
                                            <td class="text-right" id="rNet">0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            {{-- Unsettled Bills --}}
                            <div class="section-title">Unsettled Bills (Outstanding Balance)</div>
                            <div class="table-responsive">
                                <table id="unsettledTable" class="table table-bordered table-sm" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Room No</th>
                                            <th>Guest</th>
                                            <th class="text-right">Balance (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr class="grand-total">
                                            <td colspan="2"><strong>TOTAL OUTSTANDING</strong></td>
                                            <td class="text-right" id="uTotal">0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            {{-- Night Audit Log --}}
                            <div class="section-title">Night Audit Log Entries</div>
                            <div id="naLog" class="mb-4"><em class="text-muted">Click Fetch to load data.</em></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    var rt, ut;

    function fmt(n) { return parseFloat(n || 0).toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2}); }

    function fetchData() {
        $.ajax({
            url: '{{ route("nightauditreconfetch") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', fordate: $('#fordate').val() },
            success: function(r) {
                $('#summaryCards').show();

                // Metrics
                $('#mTotalRooms').text(r.occupancy.total);
                $('#mActiveGuests').text(r.occupancy.active);
                $('#mCheckedOut').text(r.occupancy.co + ' checked out today');
                $('#mTotalRevenue').text('₹' + fmt(r.revenue.total));
                $('#mUnsettled').text(r.unsettled.length);
                var uamt = r.unsettled.reduce(function(s,u){ return s + u.balance; }, 0);
                $('#mUnsettledAmt').text('₹' + fmt(uamt) + ' outstanding');
                $('#mPrevOcc').text('Prev: ' + r.prev.occupied + ' rooms');
                $('#mPrevRev').text('Prev: ₹' + fmt(r.prev.revenue));

                // Revenue by type
                rt.clear();
                var tBills=0, tDr=0, tCr=0, tNet=0;
                (r.revenue.bytype || []).forEach(function(row) {
                    tBills += row.billcount; tDr += row.totaldr; tCr += row.totalcr; tNet += row.netamount;
                    rt.row.add([row.vtype, row.billcount, fmt(row.totaldr), fmt(row.totalcr), fmt(row.netamount)]).draw(false);
                });
                rt.draw();
                $('#rBills').text(tBills); $('#rDr').text(fmt(tDr)); $('#rCr').text(fmt(tCr)); $('#rNet').text(fmt(tNet));

                // Unsettled
                ut.clear();
                var utot=0;
                (r.unsettled || []).forEach(function(u) {
                    utot += u.balance;
                    ut.row.add([u.roomno, u.guestname, fmt(u.balance)]).draw(false);
                });
                ut.draw();
                $('#uTotal').text(fmt(utot));

                // NA Log
                var html = '';
                if ((r.nalog || []).length === 0) {
                    html = '<em class="text-muted">No night audit log entries for this date.</em>';
                } else {
                    html = '<ul class="list-group">';
                    r.nalog.forEach(function(l) {
                        html += '<li class="list-group-item"><strong>' + (l.narration||'') + '</strong> — by ' + (l.u_name||'') + ' at ' + (l.u_entdt||'') + '</li>';
                    });
                    html += '</ul>';
                }
                $('#naLog').html(html);
            },
            error: function(xhr) { alert('Error: ' + (xhr.responseText || 'Unknown')); }
        });
    }

    $(function() {
        rt = $('#revenueTable').DataTable({ dom: 'Bfrtip', buttons: [], paging: false, searching: false });
        ut = $('#unsettledTable').DataTable({ dom: 'Bfrtip', buttons: [], paging: false, searching: false });
    });
    </script>
@endsection
