@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid">
   <div class="card">
      <div class="card-header"><h4 class="card-title"><i class="fas fa-clipboard-list"></i> Pending KOT Report</h4></div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ $fromdate }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ $fromdate }}"></div>
            <div class="col-md-2"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <div class="row mb-3" id="summaryCards" style="display:none;">
            <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-clipboard"></i></span><div class="info-box-content"><span class="info-box-text">Pending KOTs</span><span class="info-box-number" id="totalKOTs">0</span></div></div></div>
            <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-rupee-sign"></i></span><div class="info-box-content"><span class="info-box-text">Total Amount</span><span class="info-box-number" id="totalAmount">₹0</span></div></div></div>
            <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-danger"><i class="fas fa-store"></i></span><div class="info-box-content"><span class="info-box-text">Outlets</span><span class="info-box-number" id="totalOutlets">0</span></div></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped" id="kotTable"><thead class="thead-dark"><tr><th>#</th><th>Date</th><th>Time</th><th>KOT No</th><th>Outlet</th><th>Room</th><th>Item</th><th>Qty</th><th class="text-right">Rate</th><th class="text-right">Amount</th><th>Waiter</th><th>Pax</th></tr></thead><tbody id="tableBody"></tbody></table>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   $('#fetchBtn').click(function() {
      $.post('{{ route("pendingkotreportfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val()}, function(res) {
         var h='';$.each(res.data,function(i,r){h+='<tr><td>'+(i+1)+'</td><td>'+fmtDate(r.VDate)+'</td><td>'+r.VTime+'</td><td>'+r.KOTNo+'</td><td><b>'+(r.OutletName||'')+'</b></td><td>'+(r.RoomNo||'')+'</td><td>'+(r.Item||'')+'</td><td class="text-center">'+r.Qty+'</td><td class="text-right">'+fmt(r.Rate)+'</td><td class="text-right">'+fmt(r.Amount)+'</td><td>'+(r.Waiter||'')+'</td><td>'+r.Pax+'</td></tr>';});
         $('#tableBody').html(h);
         $('#totalKOTs').text(res.total);$('#totalAmount').text('₹'+fmt(res.totalAmount));$('#totalOutlets').text(res.grouped.length);$('#summaryCards').show();
         if($.fn.DataTable.isDataTable('#kotTable'))$('#kotTable').DataTable().destroy();$('#kotTable').DataTable({pageLength:25,order:[]});
      });
   });
   function fmt(v){return Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});}
   function fmtDate(d){return d?new Date(d).toLocaleDateString('en-GB'):'';}
   $('#fetchBtn').click();
});
</script>
@endsection
