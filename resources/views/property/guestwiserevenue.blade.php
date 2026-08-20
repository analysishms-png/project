@extends('property.layouts.main')
@section('main-container')
<link href="{{ asset('admin/css/dashboard-modern.css') }}" rel="stylesheet">
<div class="content-body">
    <div class="container-fluid" style="margin-top:90px;">
        <div class="dash-title-bar">
            <div class="title-left"><h3>Guest Wise Revenue</h3><p>Revenue breakdown by guest — room rent + charges</p></div>
            <div class="title-right"><button onclick="window.print()" class="dash-btn-icon"><i class="fa fa-print"></i></button></div>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label>From Date</label>
                        <input type="date" id="fromdate" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label>To Date</label>
                        <input type="date" id="todate" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button id="fetchBtn" class="btn btn-primary"><i class="fa fa-search"></i> Fetch</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>Sr</th>
                            <th>Room</th>
                            <th>Guest</th>
                            <th>Company</th>
                            <th>Arrival</th>
                            <th>Departure</th>
                            <th>Rate</th>
                            <th>Total Charges</th>
                            <th>Total Payments</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                    <tfoot id="tableFoot" style="display:none;">
                        <tr class="font-weight-bold">
                            <td colspan="7">Total</td>
                            <td id="totalCharges" class="text-right">₹0.00</td>
                            <td id="totalPayments" class="text-right">₹0.00</td>
                            <td id="totalBalance" class="text-right">₹0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    $('#fetchBtn').click(function() {
        $.post('{{ route("reservstatusinhousefetch") }}', {
            fromdate: $('#fromdate').val(),
            todate: $('#todate').val()
        }, function(res) {
            var h = '', tc = 0, tp = 0;
            $.each(res, function(i, r) {
                var bal = Number(r.TotalCharges||0) - Number(r.TotalPayments||0);
                h += '<tr><td>'+(i+1)+'</td><td>'+(r.RoomNo||'')+'</td><td>'+(r.FirstName||'')+' '+(r.LastName||'')+'</td><td>'+(r.CompanyName||'')+'</td><td>'+(r.ArrivalDate||'')+'</td><td>'+(r.DepartureDate||'')+'</td><td>₹'+fmt(r.Rate)+'</td><td class="text-right">₹'+fmt(r.TotalCharges)+'</td><td class="text-right">₹'+fmt(r.TotalPayments)+'</td><td class="text-right">₹'+fmt(bal)+'</td></tr>';
                tc += Number(r.TotalCharges||0); tp += Number(r.TotalPayments||0);
            });
            $('#tableBody').html(h);
            $('#totalCharges').text('₹'+fmt(tc)); $('#totalPayments').text('₹'+fmt(tp));
            $('#totalBalance').text('₹'+fmt(tc-tp)); $('#tableFoot').show();
        });
    });
});
function fmt(v) { return Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}); }
</script>
@endsection
