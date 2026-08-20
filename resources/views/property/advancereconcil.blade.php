@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid">
   <div class="card">
      <div class="card-header"><h4 class="card-title"><i class="fas fa-balance-scale"></i> Advance Reconciliation Report</h4></div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ $fromdate }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ $fromdate }}"></div>
            <div class="col-md-2">
               <label>Filter</label>
               <select class="form-control form-control-sm" id="filter">
                  <option value="all">All</option>
                  <option value="mismatch">Mismatches Only</option>
                  <option value="not_posted">Not Posted</option>
                  <option value="cancelled">Cancelled</option>
               </select>
            </div>
            <div class="col-md-2"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <div class="row mb-3" id="summaryCards" style="display:none;">
            <div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-file-invoice-dollar"></i></span><div class="info-box-content"><span class="info-box-text">Total Reservations</span><span class="info-box-number" id="totalRes">0</span></div></div></div>
            <div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-rupee-sign"></i></span><div class="info-box-content"><span class="info-box-text">Booking Advance</span><span class="info-box-number" id="totalBookingAdv">₹0</span></div></div></div>
            <div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-check-circle"></i></span><div class="info-box-content"><span class="info-box-text">Posted to Folio</span><span class="info-box-number" id="totalPosted">₹0</span></div></div></div>
            <div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span><div class="info-box-content"><span class="info-box-text">Mismatch Amount</span><span class="info-box-number" id="totalMismatch">₹0</span></div></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped" id="advTable">
            <thead class="thead-dark">
               <tr><th>#</th><th>Res. No</th><th>Guest</th><th>Room</th><th>Arrival</th><th>Depart</th><th class="text-right">Booking Adv</th><th class="text-right">Posted Cr</th><th class="text-right">Posted Dr</th><th class="text-right">Net Posted</th><th class="text-right">Mismatch</th><th>Folio</th><th>Status</th><th>Cancel</th></tr>
            </thead>
            <tbody id="tableBody"></tbody>
            <tfoot id="tableFoot" style="display:none;">
               <tr class="font-weight-bold"><td colspan="6" class="text-right">TOTAL</td><td id="footBooking" class="text-right">0</td><td id="footCr" class="text-right">0</td><td id="footDr" class="text-right">0</td><td id="footNet" class="text-right">0</td><td id="footMismatch" class="text-right">0</td><td colspan="3"></td></tr>
            </tfoot>
         </table>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   $('#fetchBtn').click(function() {
      $.post('{{ route("advancereconcilfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val(),filter:$('#filter').val()}, function(res) {
         var h='';$.each(res.data,function(i,r){
            var statusBadge = '';
            switch(r.Status) {
               case 'RECONCILED': statusBadge='<span class="badge badge-success">Reconciled</span>'; break;
               case 'NOT_POSTED': statusBadge='<span class="badge badge-danger">Not Posted</span>'; break;
               case 'PARTIAL': statusBadge='<span class="badge badge-warning">Partial</span>'; break;
               case 'OVER_POSTED': statusBadge='<span class="badge badge-danger">Over Posted</span>'; break;
               case 'POSTED_NO_FOLIO': statusBadge='<span class="badge badge-info">No Folio</span>'; break;
               default: statusBadge='<span class="badge badge-secondary">'+r.Status+'</span>';
            }
            var mismatchClass = r.Mismatch > 0 ? 'text-danger font-weight-bold' : 'text-success';
            h+='<tr><td>'+(i+1)+'</td><td><b>'+(r.BookNo||'')+'</b></td><td><b>'+(r.GuestName||'')+'</b></td><td>'+(r.RoomNo||'')+'</td><td>'+fmtDate(r.ArrDate)+'</td><td>'+fmtDate(r.DepDate)+'</td><td class="text-right">'+fmt(r.BookingAdvance)+'</td><td class="text-right">'+fmt(r.PostedCredit)+'</td><td class="text-right">'+fmt(r.PostedDebit)+'</td><td class="text-right">'+fmt(r.NetPosted)+'</td><td class="text-right '+mismatchClass+'">'+fmt(r.Mismatch)+'</td><td>'+(r.HasFolio?'✅':'—')+'</td><td>'+statusBadge+'</td><td>'+(r.Cancel==='Y'?'<span class="badge badge-danger">Cancelled</span>':'')+'</td></tr>';
         });
         $('#tableBody').html(h);
         $('#totalRes').text(res.total);$('#totalBookingAdv').text('₹'+fmt(res.totalBookingAdvance));$('#totalPosted').text('₹'+fmt(res.totalNetPosted));$('#totalMismatch').text('₹'+fmt(res.totalMismatch));
         $('#summaryCards').show();
         $('#footBooking').text(fmt(res.totalBookingAdvance));$('#footNet').text(fmt(res.totalNetPosted));$('#footMismatch').text(fmt(res.totalMismatch));$('#tableFoot').show();
         if($.fn.DataTable.isDataTable('#advTable'))$('#advTable').DataTable().destroy();$('#advTable').DataTable({pageLength:25,order:[]});
      });
   });
   function fmt(v){return Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});}
   function fmtDate(d){return d?new Date(d).toLocaleDateString('en-GB'):'';}
   $('#fetchBtn').click();
});
</script>
@endsection
