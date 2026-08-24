@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid">
   <div class="card">
      <div class="card-header"><h4 class="card-title"><i class="fas fa-balance-scale"></i> Guest Trial Balance</h4></div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ $fromdate }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ $fromdate }}"></div>
            <div class="col-md-2"><label>Filter</label><select class="form-control form-control-sm" id="filter"><option value="all">All</option><option value="inhouse">In House</option><option value="checkedin">Checked In</option><option value="checkedout">Checked Out</option></select></div>
            <div class="col-md-2"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <div class="row mb-3" id="summaryCards" style="display:none;">
            <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-users"></i></span><div class="info-box-content"><span class="info-box-text">Guests with Balance</span><span class="info-box-number" id="totalGuests">0</span></div></div></div>
            <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span><div class="info-box-content"><span class="info-box-text">Total Charges</span><span class="info-box-number" id="totalCharges">₹0</span></div></div></div>
            <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-arrow-down"></i></span><div class="info-box-content"><span class="info-box-text">Total Payments</span><span class="info-box-number" id="totalPayments">₹0</span></div></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped" id="tbTable"><thead class="thead-dark"><tr><th>#</th><th>Guest</th><th>Room</th><th>Type</th><th>Check-In</th><th>Depart</th><th class="text-right">Charges</th><th class="text-right">Payments</th><th class="text-right">Balance</th></tr></thead><tbody id="tableBody"></tbody><tfoot id="tableFoot" style="display:none;"><tr class="font-weight-bold"><td colspan="6" class="text-right">TOTAL</td><td id="footCharges" class="text-right">0</td><td id="footPayments" class="text-right">0</td><td id="footBalance" class="text-right">0</td></tr></tfoot></table>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   $('#fetchBtn').click(function() {
      $.post('{{ route("guesttrialbalancefetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val(),filter:$('#filter').val()}, function(res) {
         var h='';$.each(res.data,function(i,r){h+='<tr><td>'+(i+1)+'</td><td><b>'+(r.GuestName||'')+'</b></td><td>'+(r.RoomNo||'')+'</td><td>'+(r.RoomType||'')+'</td><td>'+fmtDate(r.CheckInDate)+'</td><td>'+fmtDate(r.DepartDate)+'</td><td class="text-right">'+fmt(r.TotalCharges)+'</td><td class="text-right">'+fmt(r.TotalPayments)+'</td><td class="text-right '+(r.Balance>0?'text-danger':'text-success')+'"><b>'+fmt(r.Balance)+'</b></td></tr>';});
         $('#tableBody').html(h);
         $('#totalGuests').text(res.total);$('#totalCharges').text('₹'+fmt(res.totalCharges));$('#totalPayments').text('₹'+fmt(res.totalPayments));$('#summaryCards').show();
         $('#footCharges').text(fmt(res.totalCharges));$('#footPayments').text(fmt(res.totalPayments));$('#footBalance').text(fmt(res.totalBalance));$('#tableFoot').show();
         if($.fn.DataTable.isDataTable('#tbTable'))$('#tbTable').DataTable().destroy();$('#tbTable').DataTable({pageLength:25,order:[]});
      });
   });
   $('#fetchBtn').click();
});
</script>
@endsection
