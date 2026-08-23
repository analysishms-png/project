@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid" style="margin-top:90px;">
   <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
         <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Cashier Sale (Cash Bills)</h5>
         <span class="badge badge-info">HMS.text — CashierSale</span>
      </div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-3"><label>From Date</label><input type="date" id="fromdate" class="form-control form-control-sm" value="{{ $fromdate }}"></div>
            <div class="col-md-3"><label>To Date</label><input type="date" id="todate" class="form-control form-control-sm" value="{{ $todate }}"></div>
            <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div>
         </div>
         <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped" id="dataTable" style="font-size:12px;">
               <thead class="thead-dark"><tr><th>Date</th><th>Bill No</th><th>Outlet</th><th>Guest</th><th>Room</th><th class="text-right">Net Amount</th><th>Cashier</th></tr></thead>
               <tbody id="dataBody"></tbody>
               <tfoot><tr class="font-weight-bold"><td colspan="5">TOTAL</td><td class="text-right" id="totalNet">0.00</td><td></td></tr></tfoot>
            </table>
         </div>
      </div>
   </div>
</div>
<script>
$(function() {
   $('#fetchBtn').click(function() {
      $.post('{{ route("cashiersalefetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val(),_token:'{{ csrf_token() }}'}, function(res) {
         var html=''; $.each(res.data,function(i,r){html+='<tr><td>'+r.saledate+'</td><td>'+r.billno+'</td><td>'+r.outlet+'</td><td>'+r.guestname+'</td><td>'+r.roomno+'</td><td class="text-right">₹'+fmt(r.netamt)+'</td><td>'+r.cashier+'</td></tr>';});
         $('#dataBody').html(html); $('#totalNet').text('₹'+fmt(res.total));
      });
   });
   function fmt(v){return Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});}
   $('#fetchBtn').click();
});
</script>
@endsection
