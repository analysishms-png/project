@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid" style="margin-top:90px;">
   <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
         <h4 class="card-title mb-0"><i class="fas fa-list-alt"></i> Hall Tax-wise Detail Report</h4>
         <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print"></i> Print</button>
      </div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ date('Y-m-01') }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ date('Y-m-d') }}"></div>
            
            <div class="col-md-1"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped w-100" id="rTable">
            <thead class="thead-dark"><tr><th>Date</th><th>Bill No</th><th>Party</th><th>Description</th><th>Tax Head</th><th>Rate %</th><th class="text-right">Base</th><th class="text-right">Tax Amt</th></tr></thead>
            <tbody id="tableBody"></tbody>
            <tfoot id="tFoot" style="display:none"><tr class="font-weight-bold table-secondary"><td colspan="6" class="text-right">TOTAL</td><td id="fBase" class="text-right">0</td><td id="fTax" class="text-right">0</td></tr></tfoot>
         </table>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   function fmt(v){return Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});}
   function fmtDate(d){if(!d)return '';var x=new Date(d);return isNaN(x)?d:x.toLocaleDateString('en-GB');}
   function sum(key){return rows.reduce(function(a,r){return a+Number(r[key]||0);},0);}
   var rows=[];
   function fetch() {
      $.post('{{ route("taxwisedetailreporthallfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val()
         ,source:radioVal('source')}
      , function(res) {
         if(res.error){alert(res.error);return;}
         rows=res.rows||[];
         var h='';
         $.each(rows,function(i,r){
            h+='<tr><td>'+fmtDate(r.vdate)+'</td><td>'+(r.billno||'')+'</td><td>'+(r.party||'-')+'</td><td>'+(r.descr||'')+'</td><td>'+(r.taxhead||'')+'</td><td class="text-right">'+fmt(r.taxper)+'</td><td class="text-right">'+fmt(r.base)+'</td><td class="text-right"><b>'+fmt(r.taxamt)+'</b></td></tr>';
         });
         $('#tableBody').html(h||'<tr><td colspan=8 class="text-center text-muted">No records found</td></tr>');
         $('#fBase').text(fmt(sum(r.base)));$('#fTax').text(fmt(sum(r.taxamt)));
         $('#tFoot').show();
         if($.fn.DataTable.isDataTable('#rTable'))$('#rTable').DataTable().destroy();
         $('#rTable').DataTable({pageLength:25});
      });
   }
   function radioVal(name){var el=document.querySelector("input[name='"+name+"']:checked");return el?el.value:'';}
   $('#fetchBtn').click(fetch);
   $('input[name=source]').change(fetch);
   fetch();
});
</script>
@endsection