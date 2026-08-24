@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid" style="margin-top:90px;">
   <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
         <h4 class="card-title mb-0"><i class="fas fa-ban"></i> Deleted / Unsettled Bills</h4>
         <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print"></i> Print</button>
      </div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ date('Y-m-d') }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ date('Y-m-d') }}"></div>
            
            <div class="col-md-2"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <h5>Unsettled Folio Bills</h5>
         <table class="table table-sm table-bordered table-striped" id="tblA">
            <thead class="thead-dark"><tr><th>Date</th><th>Folio</th><th>Doc Id</th><th text-right>Unsettled Balance</th></tr></thead><tbody id="bodyA"></tbody>
         </table>
         <hr>
         <h5>Deleted / History Entries (paychargeh)</h5>
         <table class="table table-sm table-bordered table-striped" id="tblB">
            <thead class="thead-dark"><tr><th>Deleted On</th><th>Biz Date</th><th>Doc Id</th><th>Type</th><th>Code</th><th text-right>Amount</th><th>By User</th></tr></thead><tbody id="bodyB"></tbody>
         </table>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   $('#fetchBtn').click(function() {
      $.post('{{ route("delbillunsetbillfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val()
         ,resstatus:($('#resstatus')?$('#resstatus').val():'')
         ,inccancelled:($('#inccancelled').is(':checked')?'Y':'N')
         ,foliono:($('#foliono')?$('#foliono').val():'')}
      , function(res) {
         if(res.error){alert(res.error);return;}
var hA='';$.each(res.unsettled,function(i,r){hA+='<tr>'+'<td>'+fmtDate(r.vd)+'</td>'+'<td>'+'<b>'+(r.foliono||'')+'</b>'+'</td>'+'<td>'+(r.docid||'')+'</td>'+'<td class="text-right">'+fmt(r.bal)+'</td>'+'</tr>';});$('#bodyA').html(hA);
         var hB='';$.each(res.deleted,function(i,r){hB+='<tr>'+'<td>'+(r.u_entdt?new Date(r.u_entdt).toLocaleString('en-GB'):'')+'</td>'+'<td>'+fmtDate(r.vdate)+'</td>'+'<td>'+'<b>'+(r.docid||'')+'</b>'+'</td>'+'<td>'+(r.vtype||'')+'</td>'+'<td>'+(r.paycode||'')+'</td>'+'<td class="text-right">'+fmt(r.amtdr)+'</td>'+'<td>'+(r.u_name||'')+'</td>'+'</tr>';});$('#bodyB').html(hB);
      });
   });
   $('#fetchBtn').click();
});
</script>
@endsection