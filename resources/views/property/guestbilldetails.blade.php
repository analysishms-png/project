@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid" style="margin-top:90px;">
   <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
         <h4 class="card-title mb-0"><i class="fas fa-file-invoice-dollar"></i> Guest Bill Details</h4>
         <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print"></i> Print</button>
      </div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ date('Y-m-d') }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ date('Y-m-d') }}"></div>
            
            <div class="col-md-2"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped" id="rTable">
            <thead class="thead-dark"><tr><th class=" text-right">Folio</th>
               <th class="">Guest</th>
               <th class="">Date</th>
               <th class="">Type</th>
               <th class="">Particulars</th>
               <th class=" text-right">Debit</th>
               <th class=" text-right">Credit</th>
               <th class=" text-right">Balance</th>
               <th class="">User</th>
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
      $.post('{{ route("guestbilldetailsfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val()
         ,resstatus:($('#resstatus')?$('#resstatus').val():'')
         ,inccancelled:($('#inccancelled').is(':checked')?'Y':'N')
         ,foliono:($('#foliono')?$('#foliono').val():'')}
      , function(res) {
         if(res.error){alert(res.error);return;}
var h='';var tot=0;$.each(res.data,function(i,r){
            
            h+='<tr>'+'<td class="text-right">'+(r.foliono??0)+'</td>'+'<td class="">'+'<b>'+(r.name||'')+'</b>'+'</td>'+'<td class="">'+fmtDate(r.vdate)+'</td>'+'<td class="">'+(r.vtype||'')+'</td>'+'<td class="">'+(r.particulars||'')+'</td>'+'<td class="text-right">'+fmt(r.dr)+'</td>'+'<td class="text-right">'+fmt(r.cr)+'</td>'+'<td class="text-right">'+fmt(r.balance)+'</td>'+'<td class="">'+(r.user||'')+'</td>'+'</tr>';});
         $('#tableBody').html(h);
         
         if($.fn.DataTable.isDataTable('#rTable'))$('#rTable').DataTable().destroy();
         $('#rTable').DataTable({pageLength:25,order:[]});
      });
   });
   $('#fetchBtn').click();
});
</script>
@endsection