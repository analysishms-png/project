@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid">
   <div class="card">
      <div class="card-header"><h4 class="card-title"><i class="fas fa-file-invoice-dollar"></i> Guest Charges MIS</h4></div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ $fromdate }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ $fromdate }}"></div>
            <div class="col-md-2"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <div class="row mb-3" id="summaryCards" style="display:none;">
            <div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-users"></i></span><div class="info-box-content"><span class="info-box-text">Folios with Balance</span><span class="info-box-number" id="totalFolios">0</span></div></div></div>
            <div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span><div class="info-box-content"><span class="info-box-text">Total Charges</span><span class="info-box-number" id="totalCharges">₹0</span></div></div></div>
            <div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-arrow-down"></i></span><div class="info-box-content"><span class="info-box-text">Total Payments</span><span class="info-box-number" id="totalPayments">₹0</span></div></div></div>
            <div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-danger"><i class="fas fa-balance-scale"></i></span><div class="info-box-content"><span class="info-box-text">Outstanding</span><span class="info-box-number" id="totalBalance">₹0</span></div></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped" id="gmTable">
            <thead class="thead-dark">
               <tr><th>#</th><th>Folio</th><th>Guest</th><th>Room</th><th>Type</th><th>Check-In</th><th class="text-right">Charges</th><th class="text-right">Payments</th><th class="text-right">Balance</th></tr>
            </thead>
            <tbody id="tableBody"></tbody>
            <tfoot id="tableFoot" style="display:none;">
               <tr class="font-weight-bold"><td colspan="6" class="text-right">TOTAL</td><td id="footCharges" class="text-right">0</td><td id="footPayments" class="text-right">0</td><td id="footBalance" class="text-right">0</td></tr>
            </tfoot>
         </table>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   $('#fetchBtn').click(function() {
      $.post('{{ route("guestchargesmisfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val()}, function(res) {
         var h='';$.each(res.data,function(i,r){
            var balClass = r.Balance > 0 ? 'text-danger' : 'text-success';
            h+='<tr><td>'+(i+1)+'</td><td><b>'+(r.FolioNo||'')+'</b></td><td><b>'+(r.GuestName||'')+'</b></td><td>'+(r.RoomNo||'')+'</td><td>'+(r.RoomType||'')+'</td><td>'+fmtDate(r.CheckInDate)+'</td><td class="text-right">'+fmt(r.TotalCharges)+'</td><td class="text-right">'+fmt(r.TotalPayments)+'</td><td class="text-right '+balClass+'"><b>'+fmt(r.Balance)+'</b></td></tr>';
         });
         $('#tableBody').html(h);
         $('#totalFolios').text(res.total);$('#totalCharges').text('₹'+fmt(res.totalCharges));$('#totalPayments').text('₹'+fmt(res.totalPayments));$('#totalBalance').text('₹'+fmt(res.totalBalance));$('#summaryCards').show();
         $('#footCharges').text(fmt(res.totalCharges));$('#footPayments').text(fmt(res.totalPayments));$('#footBalance').text(fmt(res.totalBalance));$('#tableFoot').show();
         if($.fn.DataTable.isDataTable('#gmTable'))$('#gmTable').DataTable().destroy();$('#gmTable').DataTable({pageLength:25,order:[]});
      });
   });
   $('#fetchBtn').click();
});
</script>
@endsection
