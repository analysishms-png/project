@extends('property.layouts.main')
@section('main-container')
<link href="{{ asset('admin/css/dashboard-modern.css') }}" rel="stylesheet">
<div class="content-body">
    <div class="container-fluid" style="margin-top:90px;">
        <div class="dash-title-bar">
            <div class="title-left">
                <h3>Reservation Status — Arrival</h3>
                <p>Expected arrivals for the selected period</p>
            </div>
            <div class="title-right">
                <button onclick="window.print()" class="dash-btn-icon"><i class="fa fa-print"></i></button>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label>From Date</label>
                        <input type="date" id="fromdate" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label>To Date</label>
                        <input type="date" id="todate" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button id="fetchBtn" class="btn btn-primary"><i class="fa fa-search"></i> Fetch</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-sm" id="resultTable">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>Sr</th>
                            <th>Arrival</th>
                            <th>Departure</th>
                            <th>Room</th>
                            <th>Category</th>
                            <th>Guest Name</th>
                            <th>Company</th>
                            <th>Adults</th>
                            <th>Child</th>
                            <th>Rate</th>
                            <th>Plan</th>
                            <th>Mobile</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                    <tfoot id="tableFoot" style="display:none;">
                        <tr class="font-weight-bold">
                            <td colspan="7">Total</td>
                            <td id="totalAdults">0</td>
                            <td id="totalChild">0</td>
                            <td colspan="3"></td>
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
        $.post('{{ route("reservstatusarrivalfetch") }}', {
            fromdate: $('#fromdate').val(),
            todate: $('#todate').val()
        }, function(res) {
            var h = '', totAdults = 0, totChild = 0;
            $.each(res, function(i, r) {
                h += '<tr><td>' + (i+1) + '</td><td>' + (r.ArrivalDate||'') + '</td><td>' + (r.DepartureDate||'') + '</td><td>' + (r.RoomNo||'') + '</td><td>' + (r.RoomCategory||'') + '</td><td>' + (r.FirstName||'') + ' ' + (r.LastName||'') + '</td><td>' + (r.CompanyName||'') + '</td><td>' + (r.Adults||0) + '</td><td>' + (r.Child||0) + '</td><td>₹' + fmt(r.Rate) + '</td><td>' + (r.Plan||'') + '</td><td>' + (r.Mobile||'') + '</td></tr>';
                totAdults += Number(r.Adults || 0);
                totChild += Number(r.Child || 0);
            });
            $('#tableBody').html(h);
            $('#totalAdults').text(totAdults);
            $('#totalChild').text(totChild);
            $('#tableFoot').show();
        });
    });
    $('#fetchBtn').click();
});
</script>
@endsection
