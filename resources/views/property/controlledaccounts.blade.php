@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid" style="margin-top:90px;">
   <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
         <h4 class="card-title mb-0"><i class="fas fa-tasks"></i> Controlled Accounts</h4>
         <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print"></i> Print</button>
      </div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ date('Y-m-01') }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ date('Y-m-d') }}"></div>
            <div class="col-md-6">
               <label class="mb-1">Account Group</label>
               <div class="btn-group btn-group-toggle btn-group-sm flex-wrap" data-toggle="buttons" id="natureGroup">
                  <label class="btn btn-outline-primary active"><input type="radio" name="nature" value="" checked> All</label>
                  <label class="btn btn-outline-primary"><input type="radio" name="nature" value="Cash"> Cash</label>
                  <label class="btn btn-outline-primary"><input type="radio" name="nature" value="Bank"> Bank</label>
                  <label class="btn btn-outline-primary"><input type="radio" name="nature" value="TDS"> TDS</label>
                  <label class="btn btn-outline-primary"><input type="radio" name="nature" value="Sale"> Sale</label>
                  <label class="btn btn-outline-primary"><input type="radio" name="nature" value="Purchase"> Purchase</label>
                  <label class="btn btn-outline-primary"><input type="radio" name="nature" value="Expenditure"> Expense</label>
               </div>
            </div>
            <div class="col-md-1"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped" id="rTable">
            <thead class="thead-dark"><tr>
               <th>A/C Code</th><th>Account Name</th><th>Nature</th>
               <th class="text-right">Debit</th><th class="text-right">Credit</th><th class="text-right">Balance</th>
            </tr></thead>
            <tbody id="tableBody"></tbody>
            <tfoot id="tFoot" style="display:none"><tr class="font-weight-bold table-secondary">
               <td colspan="3" class="text-right">TOTAL</td>
               <td id="footDr" class="text-right">0</td>
               <td id="footCr" class="text-right">0</td><td></td>
            </tr></tfoot>
         </table>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   function fmt(v){return Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2});}
   function radioVal(name){return $("input[name='"+name+"']:checked").val()||'';}
   function fetch() {
      $.post('{{ route("controlledaccountsfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val()
         ,nature:radioVal('nature')}
      , function(res) {
         if(res.error){alert(res.error);return;}
         var h='';
         $.each(res.data,function(i,r){
            var balCls = Number(r.balance)>=0 ? 'text-dark' : 'text-danger';
            h+='<tr><td>'+(r.sub_code||'')+'</td><td><b>'+(r.name||'')+'</b></td>'
              +'<td><span class="badge badge-secondary">'+(r.nature||'')+'</span></td>'
              +'<td class="text-right">'+fmt(r.dr)+'</td>'
              +'<td class="text-right">'+fmt(r.cr)+'</td>'
              +'<td class="text-right '+balCls+'"><b>'+fmt(r.balance)+'</b></td></tr>';
         });
         $('#tableBody').html(h);
         $('#footDr').text(fmt(res.totdr));
         $('#footCr').text(fmt(res.totcr));
         $('#tFoot').show();
         if($.fn.DataTable.isDataTable('#rTable'))$('#rTable').DataTable().destroy();
         $('#rTable').DataTable({pageLength:25,order:[[2,'asc'],[1,'asc']]});
      });
   }
   $('#fetchBtn').click(fetch);
   $('input[name=nature]').change(fetch);
   fetch();
});
</script>
@endsection
