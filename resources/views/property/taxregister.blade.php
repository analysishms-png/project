@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid" style="margin-top:90px;">
   <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
         <h4 class="card-title mb-0"><i class="fas fa-book"></i> Tax Register (Summary)</h4>
         <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print"></i> Print</button>
      </div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ date('Y-m-01') }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ date('Y-m-d') }}"></div>
            <div class="col-md-4"><label class="mb-1">Source</label><div class="btn-group btn-group-toggle btn-group-sm" data-toggle="buttons" id="grpSrc"><label class="btn btn-outline-primary active"><input type="radio" name="source" value="all" checked> All</label><label class="btn btn-outline-primary"><input type="radio" name="source" value="rooms"> Room</label><label class="btn btn-outline-primary"><input type="radio" name="source" value="pos"> POS</label><label class="btn btn-outline-primary"><input type="radio" name="source" value="banquet"> Banquet</label></div></div>
            <div class="col-md-1"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped w-100" id="rTable">
            <thead class="thead-dark"><tr><th>Source</th><th>Rate %</th><th class="text-right">Entries</th><th class="text-right">Base</th><th class="text-right">CGST</th><th class="text-right">SGST</th><th class="text-right">IGST</th><th class="text-right">Total Tax</th><th class="text-right">Net</th></tr></thead>
            <tbody id="tableBody"></tbody>
            <tfoot id="tFoot" style="display:none"><tr class="font-weight-bold table-secondary"><td colspan="3" class="text-right">TOTAL</td><td id="fBase" class="text-right">0</td><td id="fCgst" class="text-right">0</td><td id="fSgst" class="text-right">0</td><td id="fIgst" class="text-right">0</td><td id="fTax" class="text-right">0</td><td id="fNet" class="text-right">0</td></tr></tfoot>
         </table>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   function sum(key){return rows.reduce(function(a,r){return a+Number(r[key]||0);},0);}
   var rows=[];
   function fetch() {
      $.post('{{ route("taxregisterfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val()
         ,source:radioVal('source')}
      , function(res) {
         if(res.error){alert(res.error);return;}
         rows=res.rows||[];
         var h='';
         $.each(rows,function(i,r){
            h+='<tr><td><span class="badge badge-info">'+r.src+'</span></td><td class="text-right">'+fmt(r.taxper)+'</td><td class="text-right">'+(r.bills??0)+'</td><td class="text-right">'+fmt(r.base)+'</td><td class="text-right">'+fmt(r.cgst)+'</td><td class="text-right">'+fmt(r.sgst)+'</td><td class="text-right">'+fmt(r.igst)+'</td><td class="text-right">'+fmt(r.tax)+'</td><td class="text-right"><b>'+fmt(r.net)+'</b></td></tr>';
         });
         $('#tableBody').html(h||'<tr><td colspan=9 class="text-center text-muted">No records found</td></tr>');
         $('#fBase').text(fmt(sum(r.base)));$('#fCgst').text(fmt(sum(r.cgst)));$('#fSgst').text(fmt(sum(r.sgst)));$('#fIgst').text(fmt(sum(r.igst)));$('#fTax').text(fmt(sum(r.tax)));$('#fNet').text(fmt(sum(r.net)));
         $('#tFoot').show();
         if($.fn.DataTable.isDataTable('#rTable'))$('#rTable').DataTable().destroy();
         $('#rTable').DataTable({pageLength:25});
      });
   }
   $('#fetchBtn').click(fetch);
   $('input[name=source]').change(fetch);
   fetch();
});
</script>
@endsection