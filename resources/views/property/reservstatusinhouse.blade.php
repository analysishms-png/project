@extends('property.layouts.main')
@section('main-container')
<link href="{{ asset('admin/css/dashboard-modern.css') }}" rel="stylesheet">
<div class="content-body">
    <div class="container-fluid" style="margin-top:90px;">
        <div class="dash-title-bar">
            <div class="title-left">
                <h3>Reservation Status — In-House</h3>
                <p>Currently in-house guests with charges and payments</p>
            </div>
            <div class="title-right">
                <button onclick="window.print()" class="dash-btn-icon"><i class="fa fa-print"></i></button>
            </div>
        </div>
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-sm" id="resultTable">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>Sr</th>
                            <th>Room</th>
                            <th>Category</th>
                            <th>Guest Name</th>
                            <th>Company</th>
                            <th>Arrival</th>
                            <th>Departure</th>
                            <th>Adults</th>
                            <th>Rate</th>
                            <th>Total Charges</th>
                            <th>Total Payments</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                    <tfoot id="tableFoot" style="display:none;">
                        <tr class="font-weight-bold">
                            <td colspan="9">Total</td>
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
    $.post('{{ route("reservstatusinhousefetch") }}', {}, function(res) {
        var h = '', totChg = 0, totPay = 0, totBal = 0;
        $.each(res, function(i, r) {
            var bal = Number(r.TotalCharges||0) - Number(r.TotalPayments||0);
            h += '<tr><td>' + (i+1) + '</td><td>' + (r.RoomNo||'') + '</td><td>' + (r.RoomCategory||'') + '</td><td>' + (r.FirstName||'') + ' ' + (r.LastName||'') + '</td><td>' + (r.CompanyName||'') + '</td><td>' + (r.ArrivalDate||'') + '</td><td>' + (r.DepartureDate||'') + '</td><td>' + (r.Adults||0) + '</td><td>₹' + fmt(r.Rate) + '</td><td class="text-right">₹' + fmt(r.TotalCharges) + '</td><td class="text-right">₹' + fmt(r.TotalPayments) + '</td><td class="text-right" style="color:' + (bal > 0 ? '#ef4444' : '#22c55e') + '">₹' + fmt(bal) + '</td></tr>';
            totChg += Number(r.TotalCharges || 0);
            totPay += Number(r.TotalPayments || 0);
            totBal += bal;
        });
        $('#tableBody').html(h);
        $('#totalCharges').text('₹' + fmt(totChg));
        $('#totalPayments').text('₹' + fmt(totPay));
        $('#totalBalance').text('₹' + fmt(totBal));
        $('#tableFoot').show();
    });
});
</script>
@endsection
