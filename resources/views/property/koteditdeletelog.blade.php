@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid">
   <div class="card">
      <div class="card-header"><h4 class="card-title"><i class="fas fa-history"></i> KOT Edit/Delete Log</h4></div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ $fromdate }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ $fromdate }}"></div>
            <div class="col-md-2">
               <label>Outlet</label>
               <select class="form-control form-control-sm" id="outlet">
                  <option value="all">All Outlets</option>
                  @foreach($outlets as $o)
                  <option value="{{ $o->dcode }}">{{ $o->name }}</option>
                  @endforeach
               </select>
            </div>
            <div class="col-md-2">
               <label>Mode</label>
               <select class="form-control form-control-sm" id="mode">
                  <option value="all">All</option>
                  <option value="edited">Edited</option>
                  <option value="deleted">Deleted</option>
                  <option value="voided">Voided</option>
                  <option value="nc">NC KOT</option>
               </select>
            </div>
            <div class="col-md-2"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <div class="row mb-3" id="summaryCards" style="display:none;">
            <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-receipt"></i></span><div class="info-box-content"><span class="info-box-text">Total KOTs</span><span class="info-box-number" id="totalKOTs">0</span></div></div></div>
            <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-rupee-sign"></i></span><div class="info-box-content"><span class="info-box-text">Total Amount</span><span class="info-box-number" id="totalAmount">₹0</span></div></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped" id="klTable">
            <thead class="thead-dark">
               <tr><th>#</th><th>Date</th><th>KOT No</th><th>Outlet</th><th>Room</th><th>Item</th><th>Qty</th><th class="text-right">Rate</th><th class="text-right">Amount</th><th>Void</th><th>NC</th><th>User</th><th>Reason</th><th>Status</th></tr>
            </thead>
            <tbody id="tableBody"></tbody>
         </table>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   $('#fetchBtn').click(function() {
      $.post('{{ route("koteditdeletelogfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val(),outlet:$('#outlet').val(),mode:$('#mode').val()}, function(res) {
         var h='';$.each(res.data,function(i,r){
            var statusBadges = '';
            if(r.delflag === 'Y') statusBadges += '<span class="badge badge-danger mr-1">Deleted</span>';
            if(r.voidyn === 'Y') statusBadges += '<span class="badge badge-warning mr-1">Voided</span>';
            if(r.nckot === 'Y') statusBadges += '<span class="badge badge-info mr-1">NC</span>';
            if(r.u_ae === 'e') statusBadges += '<span class="badge badge-primary mr-1">Edited</span>';
            if(!statusBadges) statusBadges = '<span class="badge badge-success">Original</span>';
            h+='<tr><td>'+(i+1)+'</td><td>'+fmtDate(r.vdate)+'</td><td><b>'+(r.KOTNo||'')+'</b></td><td>'+(r.OutletName||'')+'</td><td>'+(r.roomno||'')+'</td><td>'+(r.item||'')+'</td><td class="text-center">'+r.qty+'</td><td class="text-right">'+fmt(r.rate)+'</td><td class="text-right">'+fmt(r.amount)+'</td><td>'+(r.voidyn||'')+'</td><td>'+(r.nckot||'')+'</td><td>'+(r.u_name||'')+'</td><td>'+(r.reasons||r.ncreason||r.remarks||'')+'</td><td>'+statusBadges+'</td></tr>';
         });
         $('#tableBody').html(h);
         $('#totalKOTs').text(res.total);$('#totalAmount').text('₹'+fmt(res.totalAmount));$('#summaryCards').show();
         if($.fn.DataTable.isDataTable('#klTable'))$('#klTable').DataTable().destroy();$('#klTable').DataTable({pageLength:25,order:[],scrollX:true});
      });
   });
   function fmt(v){return Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});}
   function fmtDate(d){return d?new Date(d).toLocaleDateString('en-GB'):'';}
   $('#fetchBtn').click();
});
</script>
@endsection
