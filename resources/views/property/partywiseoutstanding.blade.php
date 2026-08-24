@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid" style="margin-top:90px;">
   <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
         <h4 class="card-title mb-0"><i class="fas fa-balance-scale"></i> Party-wise Outstanding</h4>
         <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print"></i> Print</button>
      </div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>As On</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ date('Y-m-d') }}"></div>
            <div class="col-md-3">
               <label class="mb-1">Balance Type</label>
               <div class="btn-group btn-group-toggle btn-group-sm" data-toggle="buttons" id="balGroup">
                  <label class="btn btn-outline-primary active"><input type="radio" name="baltype" value="" checked> All</label>
                  <label class="btn btn-outline-success"><input type="radio" name="baltype" value="R"> Receivable (Dr)</label>
                  <label class="btn btn-outline-danger"><input type="radio" name="baltype" value="P"> Payable (Cr)</label>
               </div>
            </div>
            <div class="col-md-3">
               <label class="mb-1">Party Nature</label>
               <div class="btn-group btn-group-toggle btn-group-sm" data-toggle="buttons" id="natGroup">
                  <label class="btn btn-outline-info active"><input type="radio" name="nature" value="" checked> All Parties</label>
                  <label class="btn btn-outline-info"><input type="radio" name="nature" value="Customer"> Customers</label>
                  <label class="btn btn-outline-info"><input type="radio" name="nature" value="Supplier"> Suppliers</label>
               </div>
            </div>
            <div class="col-md-1"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <div class="row mb-2">
            <div class="col-md-6"><div class="alert alert-success py-2 mb-0">Total Receivable: <b id="totRecvd">0</b></div></div>
            <div class="col-md-6"><div class="alert alert-danger py-2 mb-0">Total Payable: <b id="totPayble">0</b></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped" id="rTable">
            <thead class="thead-dark"><tr>
               <th>A/C Code</th><th>Party Name</th><th>Nature</th>
               <th class="text-right">Debit</th><th class="text-right">Credit</th><th class="text-right">Outstanding</th>
            </tr></thead>
            <tbody id="tableBody"></tbody>
         </table>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   function fetch() {
      $.post('{{ route("partywiseoutstandingfetch") }}', {todate:$('#todate').val()
         ,baltype:radioVal('baltype'),nature:radioVal('nature')}
      , function(res) {
         if(res.error){alert(res.error);return;}
         var h='';
         $.each(res.data,function(i,r){
            var pos = Number(r.outstanding)>0;
            h+='<tr><td>'+(r.sub_code||'')+'</td><td><b>'+(r.name||'')+'</b></td>'
              +'<td><span class="badge badge-'+(r.nature==='Customer'?'info':'warning')+'">'+(r.nature||'')+'</span></td>'
              +'<td class="text-right">'+fmt(r.dr)+'</td>'
              +'<td class="text-right">'+fmt(r.cr)+'</td>'
              +'<td class="text-right"><b class="'+(pos?'text-success':'text-danger')+'">'+fmt(Math.abs(r.outstanding))+' '+(pos?'Dr':'Cr')+'</b></td></tr>';
         });
         $('#tableBody').html(h);
         $('#totRecvd').text(fmt(res.totrecvd));
         $('#totPayble').text(fmt(res.totpayble));
         if($.fn.DataTable.isDataTable('#rTable'))$('#rTable').DataTable().destroy();
         $('#rTable').DataTable({pageLength:25,order:[[5,'desc']]});
      });
   }
   $('#fetchBtn').click(fetch);
   $('input[name=baltype],input[name=nature]').change(fetch);
   fetch();
});
</script>
@endsection
