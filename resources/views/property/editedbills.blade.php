@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid">
   <div class="card">
      <div class="card-header"><h4 class="card-title"><i class="fas fa-edit"></i> Edited Bills Report</h4></div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ $fromdate }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ $fromdate }}"></div>
            <div class="col-md-2"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <div class="row mb-3" id="summaryCards" style="display:none;">
            <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-edit"></i></span><div class="info-box-content"><span class="info-box-text">Edited Bills</span><span class="info-box-number" id="totalBills">0</span></div></div></div>
            <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-rupee-sign"></i></span><div class="info-box-content"><span class="info-box-text">Total Bill Amount</span><span class="info-box-number" id="totalBillAmt">₹0</span></div></div></div>
            <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span><div class="info-box-content"><span class="info-box-text">Total Settled</span><span class="info-box-number" id="totalSettAmt">₹0</span></div></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped" id="ebTable">
            <thead class="thead-dark">
               <tr><th>#</th><th>Bill No</th><th>Bill Date</th><th>Folio</th><th>Guest</th><th class="text-right">Bill Amt</th><th class="text-right">Settled</th><th>Status</th><th>Edited By</th><th>Entry Date</th><th>Updated</th></tr>
            </thead>
            <tbody id="tableBody"></tbody>
         </table>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   $('#fetchBtn').click(function() {
      $.post('{{ route("editedbillsfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val()}, function(res) {
         var h='';$.each(res.data,function(i,r){
            var statusBadge = r.status === 'S' ? '<span class="badge badge-success">Settled</span>' : (r.status === 'C' ? '<span class="badge badge-danger">Cancelled</span>' : '<span class="badge badge-secondary">'+(r.status||'Open')+'</span>');
            h+='<tr><td>'+(i+1)+'</td><td><b>'+(r.billno||'')+'</b></td><td>'+fmtDate(r.billdate)+'</td><td>'+(r.foliono||'')+'</td><td><b>'+(r.guestname||'')+'</b></td><td class="text-right">'+fmt(r.billamt)+'</td><td class="text-right">'+fmt(r.settamt)+'</td><td>'+statusBadge+'</td><td>'+(r.u_name||'')+'</td><td>'+fmtDateTime(r.u_entdt)+'</td><td>'+fmtDateTime(r.u_updatedt)+'</td></tr>';
         });
         $('#tableBody').html(h);
         $('#totalBills').text(res.total);$('#totalBillAmt').text('₹'+fmt(res.totalBillAmt));$('#totalSettAmt').text('₹'+fmt(res.totalSettAmt));$('#summaryCards').show();
         if($.fn.DataTable.isDataTable('#ebTable'))$('#ebTable').DataTable().destroy();$('#ebTable').DataTable({pageLength:25,order:[]});
      });
   });
   function fmtDateTime(d){return d?new Date(d).toLocaleString('en-GB'):'';}
   $('#fetchBtn').click();
});
</script>
@endsection
