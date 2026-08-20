@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid">
   <div class="card">
      <div class="card-header"><h4 class="card-title"><i class="fas fa-receipt"></i> Extra Charges During Stay</h4></div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ $fromdate }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ $fromdate }}"></div>
            <div class="col-md-2"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <div class="row mb-3" id="summaryCards" style="display:none;">
            <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-users"></i></span><div class="info-box-content"><span class="info-box-text">Guests with Extra Charges</span><span class="info-box-number" id="totalGuests">0</span></div></div></div>
            <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-receipt"></i></span><div class="info-box-content"><span class="info-box-text">Total Transactions</span><span class="info-box-number" id="totalTxn">0</span></div></div></div>
            <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-danger"><i class="fas fa-rupee-sign"></i></span><div class="info-box-content"><span class="info-box-text">Total Extra Charges</span><span class="info-box-number" id="totalExtra">₹0</span></div></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped" id="ecTable">
            <thead class="thead-dark">
               <tr><th>#</th><th>Folio</th><th>Guest</th><th>Room</th><th>Type</th><th>Check-In</th><th class="text-center">Txns</th><th class="text-right">PPOS</th><th class="text-right">IPOS</th><th class="text-right">Total Extra</th></tr>
            </thead>
            <tbody id="tableBody"></tbody>
         </table>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   $('#fetchBtn').click(function() {
      $.post('{{ route("extrachargesduringstayfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val()}, function(res) {
         var h='';$.each(res.data,function(i,r){
            h+='<tr><td>'+(i+1)+'</td><td><b>'+(r.FolioNo||'')+'</b></td><td><b>'+(r.GuestName||'')+'</b></td><td>'+(r.RoomNo||'')+'</td><td>'+(r.RoomType||'')+'</td><td>'+fmtDate(r.CheckInDate)+'</td><td class="text-center">'+r.TxnCount+'</td><td class="text-right">'+fmt(r.Breakdown.PPOS||0)+'</td><td class="text-right">'+fmt(r.Breakdown.IPOS||0)+'</td><td class="text-right"><b>'+fmt(r.TotalExtra)+'</b></td></tr>';
         });
         $('#tableBody').html(h);
         $('#totalGuests').text(res.total);$('#totalTxn').text(res.totalTxn);$('#totalExtra').text('₹'+fmt(res.totalExtra));$('#summaryCards').show();
         if($.fn.DataTable.isDataTable('#ecTable'))$('#ecTable').DataTable().destroy();$('#ecTable').DataTable({pageLength:25,order:[]});
      });
   });
   function fmt(v){return Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});}
   function fmtDate(d){return d?new Date(d).toLocaleDateString('en-GB'):'';}
   $('#fetchBtn').click();
});
</script>
@endsection
