@extends('property.layouts.main')
@section('main-container')
<link href="{{ asset('admin/css/dashboard-modern.css') }}" rel="stylesheet">
<div class="content-body">
    <div class="container-fluid" style="margin-top:90px;">
        <div class="dash-title-bar">
            <div class="title-left"><h3>Guest Wise Analysis</h3><p>Guest value analysis — charges vs payments</p></div>
            <div class="title-right"><button onclick="window.print()" class="dash-btn-icon"><i class="fa fa-print"></i></button></div>
        </div>
        <div class="card mb-4"><div class="card-body"><div class="row g-3">
            <div class="col-md-3"><label>From Date</label><input type="date" id="fromdate" class="form-control" value="{{ date('Y-m-d') }}"></div>
            <div class="col-md-3"><label>To Date</label><input type="date" id="todate" class="form-control" value="{{ date('Y-m-d') }}"></div>
            <div class="col-md-3 d-flex align-items-end"><button id="fetchBtn" class="btn btn-primary"><i class="fa fa-search"></i> Fetch</button></div>
        </div></div></div>
        <div class="card"><div class="card-body table-responsive">
            <table class="table table-bordered table-sm"><thead class="bg-primary text-white"><tr><th>Sr</th><th>Guest</th><th>Mobile</th><th>Company</th><th>Bookings</th><th>Total Charges</th><th>Total Payments</th><th>Balance</th></tr></thead>
            <tbody id="tableBody"></tbody>
            <tfoot id="tableFoot" style="display:none;"><tr class="font-weight-bold"><td colspan="4">Total</td><td id="tBook">0</td><td id="tChg" class="text-right">₹0.00</td><td id="tPay" class="text-right">₹0.00</td><td id="tBal" class="text-right">₹0.00</td></tr></tfoot></table>
        </div></div>
    </div>
</div>
<script>
$(document).ready(function(){$('#fetchBtn').click(function(){$.post('{{ route("guestwiseanalysisfetch") }}',{fromdate:$('#fromdate').val(),todate:$('#todate').val()},function(res){var h='',tB=0,tC=0,tP=0,tBa=0;$.each(res,function(i,r){var bal=Number(r.TotalCharges||0)-Number(r.TotalPayments||0);h+='<tr><td>'+(i+1)+'</td><td>'+(r.FirstName||'')+' '+(r.LastName||'')+'</td><td>'+(r.Mobile||'')+'</td><td>'+(r.CompanyName||'')+'</td><td>'+r.TotalBookings+'</td><td class="text-right">₹'+fmt(r.TotalCharges)+'</td><td class="text-right">₹'+fmt(r.TotalPayments)+'</td><td class="text-right" style="color:'+(bal>0?'#ef4444':'#22c55e')+'">₹'+fmt(bal)+'</td></tr>';tB+=r.TotalBookings;tC+=Number(r.TotalCharges||0);tP+=Number(r.TotalPayments||0);tBa+=bal;});$('#tableBody').html(h);$('#tBook').text(tB);$('#tChg').text('₹'+fmt(tC));$('#tPay').text('₹'+fmt(tP));$('#tBal').text('₹'+fmt(tBa));$('#tableFoot').show();});});$('#fetchBtn').click();
});
</script>
@endsection
