@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid" style="margin-top:90px;">
   <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
         <h4 class="card-title mb-0"><i class="fas fa-hand-holding-usd"></i> Ledger - Creditors / Parties</h4>
         <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print"></i> Print</button>
      </div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ date('Y-m-01') }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ date('Y-m-d') }}"></div>
            <div class="col-md-3">
               <label class="mb-1">Party Type</label>
               <div class="btn-group btn-group-toggle btn-group-sm" data-toggle="buttons" id="ptypeGroup">
                  <label class="btn btn-outline-primary active"><input type="radio" name="partytype" value="Supplier" checked> Suppliers</label>
                  <label class="btn btn-outline-info"><input type="radio" name="partytype" value="Customer"> Customers</label>
               </div>
            </div>
            <div class="col-md-2"><label>Party</label>
               <select id="partycode" class="form-control form-control-sm"><option value="">All Parties</option>@foreach($accounts as $a)<option value="{{ $a->sub_code }}">{{ $a->name }} ({{ $a->nature }})</option>@endforeach</select>
            </div>
            <div class="col-md-1"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped" id="rTable">
            <thead class="thead-dark"><tr>
               <th>Date</th><th>Vch No</th><th>Type</th><th>Party</th><th>Narration</th><th>Cheque No</th>
               <th class="text-right">Debit</th><th class="text-right">Credit</th><th class="text-right">Net (Dr-Cr)</th>
            </tr></thead>
            <tbody id="tableBody"></tbody>
            <tfoot id="tFoot" style="display:none"><tr class="font-weight-bold table-secondary">
               <td colspan="8" class="text-right">NET TOTAL</td><td id="footTotal" class="text-right">0</td>
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
   function fetch() {
      $.post('{{ route("ledgercredfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val()
         ,partytype:radioVal('partytype'),partycode:$('#partycode').val()}
      , function(res) {
         if(res.error){alert(res.error);return;}
         var h='';
         $.each(res.data,function(i,r){
            h+='<tr><td>'+fmtDate(r.vdate)+'</td><td>'+(r.vno||'')+'</td><td>'+(r.vtype||'')+'</td>'
              +'<td><b>'+(r.partyname||r.partycode||'')+'</b></td><td>'+(r.narration||'')+'</td>'
              +'<td>'+(r.chqno||'')+'</td>'
              +'<td class="text-right">'+fmt(r.amtdr)+'</td>'
              +'<td class="text-right">'+fmt(r.amtcr)+'</td>'
              +'<td class="text-right"><b>'+fmt(r.net)+'</b></td></tr>';
         });
         $('#tableBody').html(h);
         $('#footTotal').text(fmt(res.total));
         $('#tFoot').show();
         if($.fn.DataTable.isDataTable('#rTable'))$('#rTable').DataTable().destroy();
         $('#rTable').DataTable({pageLength:25,order:[[0,'asc']]});
      });
   }
   $('#fetchBtn').click(fetch);
   $('input[name=partytype]').change(function(){
      // party dropdown ko selected type ke parties tak filter karo (JS side)
      var t = radioVal('partytype');
      $('#partycode option').each(function(){
         var $o = $(this);
         if($o.val()==='') return;
         $o.toggle(t==='' || $o.text().indexOf('(' + (t==='Supplier'?'Supplier':'Customer') + ')') !== -1);
      });
      $('#partycode').val('');
      fetch();
   });
   $('#partycode').change(fetch);
   fetch();
});
</script>
@endsection
