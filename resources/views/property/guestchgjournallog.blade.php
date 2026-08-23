@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid" style="margin-top:90px;">
   <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
         <h4 class="card-title mb-0"><i class="fas fa-history"></i> Guest Charge Journal Log</h4>
         <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print"></i> Print</button>
      </div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ date('Y-m-d') }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ date('Y-m-d') }}"></div>
            
            <div class="col-md-2"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped" id="rTable">
            <thead class="thead-dark"><tr><th class="">Entry Time</th>
               <th class="">Biz Date</th>
               <th class="">Doc Id</th>
               <th class="">Type</th>
               <th class="">Pay Code</th>
               <th class=" text-right">Debit</th>
               <th class=" text-right">Credit</th>
               <th class="">Room</th>
               <th class="">Entered By</th>
               </tr></thead>
            <tbody id="tableBody"></tbody>
            
         </table>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   function fmt(v){return Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});}
   function fmtDate(d){if(!d)return '';var x=new Date(d);return isNaN(x)?d:x.toLocaleDateString('en-GB');}
   $('#fetchBtn').click(function() {
      $.post('{{ route("guestchgjournallogfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val()
         ,resstatus:($('#resstatus')?$('#resstatus').val():'')
         ,inccancelled:($('#inccancelled').is(':checked')?'Y':'N')
         ,foliono:($('#foliono')?$('#foliono').val():'')}
      , function(res) {
         if(res.error){alert(res.error);return;}
var h='';var tot=0;$.each(res.data,function(i,r){
            
            h+='<tr>'+'<td class="">'+(r.u_entdt?new Date(r.u_entdt).toLocaleString('en-GB'):'')+'</td>'+'<td class="">'+fmtDate(r.vdate)+'</td>'+'<td class="">'+'<b>'+(r.docid||'')+'</b>'+'</td>'+'<td class="">'+(r.vtype||'')+'</td>'+'<td class="">'+(r.paycode||'')+'</td>'+'<td class="text-right">'+fmt(r.amtdr)+'</td>'+'<td class="text-right">'+fmt(r.amtcr)+'</td>'+'<td class="">'+(r.roomno||'')+'</td>'+'<td class="">'+(r.u_name||'')+'</td>'+'</tr>';});
         $('#tableBody').html(h);
         
         if($.fn.DataTable.isDataTable('#rTable'))$('#rTable').DataTable().destroy();
         $('#rTable').DataTable({pageLength:25,order:[]});
      });
   });
   $('#fetchBtn').click();
});
</script>
@endsection