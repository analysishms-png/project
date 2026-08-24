@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid">
   <div class="card">
      <div class="card-header"><h4 class="card-title"><i class="fas fa-users"></i> Registered Guest Detail</h4></div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-3"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ $fromdate }}"></div>
            <div class="col-md-3"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ $fromdate }}"></div>
            <div class="col-md-3"><label>Search</label><input type="text" class="form-control form-control-sm" id="search" placeholder="Name, Mobile, Email, City, PAN"></div>
            <div class="col-md-3"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <div class="row mb-3" id="summaryCards" style="display:none;">
            <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-users"></i></span><div class="info-box-content"><span class="info-box-text">Total Guests</span><span class="info-box-number" id="totalGuests">0</span></div></div></div>
            <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-rupee-sign"></i></span><div class="info-box-content"><span class="info-box-text">Total Spend</span><span class="info-box-number" id="totalSpend">₹0</span></div></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped" id="gdTable">
            <thead class="thead-dark">
               <tr><th>#</th><th>Name</th><th>Mobile</th><th>Email</th><th>City</th><th>Nationality</th><th>Type</th><th>Gender</th><th>PAN</th><th>Status</th><th>Visits</th><th>Last Visit</th><th class="text-right">Total Spend</th></tr>
            </thead>
            <tbody id="tableBody"></tbody>
         </table>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   $('#fetchBtn').click(function() {
      $.post('{{ route("registeredguestdetailfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val(),search:$('#search').val()}, function(res) {
         var h='';$.each(res.data,function(i,r){
            var statusBadge = r.guest_status === 'VIP' ? '<span class="badge badge-warning">VIP</span>' : (r.guest_status || '—');
            h+='<tr><td>'+(i+1)+'</td><td><b>'+(r.name||'')+'</b></td><td>'+(r.mobile_no||'')+'</td><td>'+(r.email_id||'')+'</td><td>'+(r.city||'')+'</td><td>'+(r.nationality||'')+'</td><td>'+(r.GuestType||'')+'</td><td>'+(r.gender||'')+'</td><td>'+(r.panno||'')+'</td><td>'+statusBadge+'</td><td class="text-center">'+r.Visits+'</td><td>'+fmtDate(r.LastVisit)+'</td><td class="text-right">'+fmt(r.TotalSpend)+'</td></tr>';
         });
         $('#tableBody').html(h);
         $('#totalGuests').text(res.total);$('#totalSpend').text('₹'+fmt(res.totalSpend));$('#summaryCards').show();
         if($.fn.DataTable.isDataTable('#gdTable'))$('#gdTable').DataTable().destroy();$('#gdTable').DataTable({pageLength:25,order:[],scrollX:true});
      });
   });
   $('#fetchBtn').click();
});
</script>
@endsection
