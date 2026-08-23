@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid" style="margin-top:90px;">
   <div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0"><i class="fas fa-cash-register"></i> Cashier Summary</h5><span class="badge badge-info">HMS.text — CashierSummary</span></div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-3"><label>From Date</label><input type="date" id="fromdate" class="form-control form-control-sm" value="{{ $fromdate }}"></div>
            <div class="col-md-3"><label>To Date</label><input type="date" id="todate" class="form-control form-control-sm" value="{{ $todate }}"></div>
            <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div>
         </div>
         <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped" style="font-size:12px;"><thead class="thead-dark"><tr><th>Outlet</th><th>Cashier</th><th>Payment Mode</th><th class="text-right">Bills</th><th class="text-right">Net Amount</th></tr></thead><tbody id="dataBody"></tbody><tfoot><tr class="font-weight-bold"><td colspan="3">TOTAL</td><td class="text-right" id="totalBills">0</td><td class="text-right" id="totalNet">0.00</td></tr></tfoot></table>
         </div>
      </div>
   </div>
</div>
<script>
$(function(){$('#fetchBtn').click(function(){$.post('{{route("cashiersummaryfetch")}}',{fromdate:$('#fromdate').val(),todate:$('#todate').val(),_token:'{{csrf_token()}}'},function(res){var html='';$.each(res.data,function(i,r){html+='<tr><td>'+r.outlet+'</td><td>'+r.cashier+'</td><td>'+r.pMode+'</td><td class="text-right">'+r.bills+'</td><td class="text-right">₹'+fmt(r.netamt)+'</td></tr>';});$('#dataBody').html(html);$('#totalBills').text(res.data.reduce((s,r)=>s+Number(r.bills||0),0));$('#totalNet').text('₹'+fmt(res.data.reduce((s,r)=>s+Number(r.netamt||0),0)));});});
function fmt(v){return Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});}
$('#fetchBtn').click();});
</script>
@endsection
