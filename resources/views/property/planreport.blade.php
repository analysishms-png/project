@extends('property.layouts.main')
@section('main-container')
<link href="{{ asset('admin/css/dashboard-modern.css') }}" rel="stylesheet">
<div class="content-body">
    <div class="container-fluid" style="margin-top:90px;">
        <div class="dash-title-bar">
            <div class="title-left">
                <h3>Plan Report</h3>
                <p>Plan/room category wise booking analysis</p>
            </div>
            <div class="title-right">
                <button onclick="window.print()" class="dash-btn-icon"><i class="fa fa-print"></i></button>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><label>From Date</label><input type="date" id="fromdate" class="form-control" value="{{ date('Y-m-d') }}"></div>
                    <div class="col-md-3"><label>To Date</label><input type="date" id="todate" class="form-control" value="{{ date('Y-m-d') }}"></div>
                    <div class="col-md-3 d-flex align-items-end"><button id="fetchBtn" class="btn btn-primary"><i class="fa fa-search"></i> Fetch</button></div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-sm"><thead class="bg-primary text-white"><tr><th>Sr</th><th>Plan</th><th>Room Category</th><th>Bookings</th><th>Rooms</th><th>Adults</th><th>Child</th><th>Avg Rate</th><th>Total Revenue</th></tr></thead>
                <tbody id="tableBody"></tbody>
                <tfoot id="tableFoot" style="display:none;"><tr class="font-weight-bold"><td colspan="3">Total</td><td id="tBook">0</td><td id="tRoom">0</td><td id="tAdult">0</td><td id="tChild">0</td><td></td><td id="tRev" class="text-right">₹0.00</td></tr></tfoot></table>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    $('#fetchBtn').click(function() {
        $.post('{{ route("planreportfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val()}, function(res) {
            var h='',tB=0,tR=0,tA=0,tC=0,tRev=0;
            $.each(res,function(i,r){h+='<tr><td>'+(i+1)+'</td><td>'+(r.PlanName||'')+'</td><td>'+(r.RoomCategory||'')+'</td><td>'+r.TotalBookings+'</td><td>'+r.TotalRooms+'</td><td>'+r.TotalAdults+'</td><td>'+r.TotalChildren+'</td><td>₹'+fmt(r.AvgRate)+'</td><td class="text-right">₹'+fmt(r.TotalRevenue)+'</td></tr>';tB+=r.TotalBookings;tR+=r.TotalRooms;tA+=r.TotalAdults;tC+=r.TotalChildren;tRev+=Number(r.TotalRevenue||0);});
            $('#tableBody').html(h);$('#tBook').text(tB);$('#tRoom').text(tR);$('#tAdult').text(tA);$('#tChild').text(tC);$('#tRev').text('₹'+fmt(tRev));$('#tableFoot').show();
        });
    });
    $('#fetchBtn').click();
});
function fmt(v){return Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});}
</script>
@endsection
