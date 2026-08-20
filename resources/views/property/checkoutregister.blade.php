@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid">
   <div class="card">
      <div class="card-header"><h4 class="card-title"><i class="fas fa-sign-out-alt"></i> Check-Out Register</h4></div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ $fromdate }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ $fromdate }}"></div>
            <div class="col-md-2"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <div class="row mb-3" id="summaryCards" style="display:none;">
            <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-sign-out-alt"></i></span><div class="info-box-content"><span class="info-box-text">Check-Outs</span><span class="info-box-number" id="totalCheckouts">0</span></div></div></div>
            <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-moon"></i></span><div class="info-box-content"><span class="info-box-text">Total Nights</span><span class="info-box-number" id="totalNights">0</span></div></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped" id="coTable">
            <thead class="thead-dark">
               <tr><th>#</th><th>Folio</th><th>Guest Name</th><th>Room</th><th>Type</th><th>Check-In</th><th>Check-Out</th><th>Nights</th><th>Room Rate</th><th>Payment</th><th>By</th></tr>
            </thead>
            <tbody id="tableBody"></tbody>
         </table>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   $('#fetchBtn').click(function() {
      $.post('{{ route("checkoutregisterfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val()}, function(res) {
         var h='';$.each(res.data,function(i,r){
            var payHtml='';if(r.payments){$.each(r.payments,function(j,p){payHtml+='<span class="badge badge-success mr-1">'+p.PayType+': ₹'+fmt(p.Amount)+'</span>';});}
            h+='<tr><td>'+(i+1)+'</td><td><b>'+(r.FolioNo||'')+'</b></td><td><b>'+(r.GuestName||'')+'</b></td><td>'+(r.RoomNo||'')+'</td><td>'+(r.RoomType||'')+'</td><td>'+fmtDate(r.ChkInDate)+'</td><td>'+fmtDate(r.ChkOutDate)+'</td><td class="text-center">'+r.Nights+'</td><td class="text-right">'+fmt(r.RoomRate)+'</td><td>'+payHtml+'</td><td>'+(r.CheckOutBy||'')+'</td></tr>';
         });
         $('#tableBody').html(h);
         $('#totalCheckouts').text(res.total);$('#totalNights').text(res.totalNights);$('#summaryCards').show();
         if($.fn.DataTable.isDataTable('#coTable'))$('#coTable').DataTable().destroy();$('#coTable').DataTable({pageLength:25,order:[],scrollX:true});
      });
   });
   function fmt(v){return Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});}
   function fmtDate(d){return d?new Date(d).toLocaleDateString('en-GB'):'';}
   $('#fetchBtn').click();
});
</script>
@endsection
