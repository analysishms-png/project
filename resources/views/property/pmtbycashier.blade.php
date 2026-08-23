@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid" style="margin-top:90px;">
   <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
         <h4 class="card-title mb-0"><i class="fas fa-cash-register"></i> Payments by Cashier</h4>
         <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print"></i> Print</button>
      </div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ date('Y-m-01') }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ date('Y-m-d') }}"></div>
            <div class="col-md-4">
               <label class="mb-1">Group By</label>
               <div class="btn-group btn-group-toggle btn-group-sm" data-toggle="buttons" id="grpGroup">
                  <label class="btn btn-outline-primary active"><input type="radio" name="groupby" value="cashier" checked> Cashier</label>
                  <label class="btn btn-outline-primary"><input type="radio" name="groupby" value="mode"> Payment Mode</label>
                  <label class="btn btn-outline-primary"><input type="radio" name="groupby" value="date"> Date</label>
               </div>
            </div>
            <div class="col-md-1"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped" id="rTable">
            <thead class="thead-dark"><tr id="headRow">
               <th>Group</th><th>Mode</th><th class="text-right">Docs</th><th class="text-right">Amount</th>
            </tr></thead>
            <tbody id="tableBody"></tbody>
            <tfoot id="tFoot" style="display:none"><tr class="font-weight-bold table-secondary">
               <td colspan="3" class="text-right">TOTAL</td><td id="footTotal" class="text-right">0</td>
            </tr></tfoot>
         </table>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   function fmt(v){return Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});}
   function fmtDate(d){if(!d)return '';var x=new Date(d);return isNaN(x)?d:x.toLocaleDateString('en-GB');}
   function radioVal(name){return $("input[name='"+name+"']:checked").val()||'';}
   function headLabel(gb){
      if(gb==='cashier') return 'Cashier';
      if(gb==='mode') return 'Payment Mode';
      return 'Date';
   }
   function fetch() {
      var gb = radioVal('groupby');
      $.post('{{ route("pmtbycashierfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val()
         ,groupby:gb}
      , function(res) {
         if(res.error){alert(res.error);return;}
         $('#headRow th:first').text(headLabel(res.groupby||gb));
         var h='';
         $.each(res.data,function(i,r){
            var label = r.grpval;
            if((res.groupby||gb)==='date') label = fmtDate(r.grpval);
            h+='<tr><td><b>'+(label||'-')+'</b></td>'
              +'<td>'+(r.paytype||'')+(r.modeset?' ('+r.modeset+')':'')+'</td>'
              +'<td class="text-right">'+(r.docs??0)+'</td>'
              +'<td class="text-right">'+fmt(r.totamt)+'</td></tr>';
         });
         $('#tableBody').html(h);
         $('#footTotal').text(fmt(res.total));
         $('#tFoot').show();
         if($.fn.DataTable.isDataTable('#rTable'))$('#rTable').DataTable().destroy();
         $('#rTable').DataTable({pageLength:25,order:[[3,'desc']]});
      });
   }
   $('#fetchBtn').click(fetch);
   $('input[name=groupby]').change(fetch);
   fetch();
});
</script>
@endsection
