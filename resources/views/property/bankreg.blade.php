@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid" style="margin-top:90px;">
   <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
         <h4 class="card-title mb-0"><i class="fas fa-university"></i> Bank Register</h4>
         <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print"></i> Print</button>
      </div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ date('Y-m-01') }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ date('Y-m-d') }}"></div>
            <div class="col-md-2"><label>Bank A/C</label>
               <select id="bankcode" class="form-control form-control-sm"><option value="">All Banks</option>@foreach($banks as $b)<option value="{{ $b->sub_code }}">{{ $b->name }}</option>@endforeach</select>
            </div>
            <div class="col-md-3">
               <label class="mb-1">Clearance Status</label>
               <div class="btn-group btn-group-toggle btn-group-sm" data-toggle="buttons" id="clrGroup">
                  <label class="btn btn-outline-primary active"><input type="radio" name="clrstatus" value="" checked> All</label>
                  <label class="btn btn-outline-success"><input type="radio" name="clrstatus" value="C"> Cleared</label>
                  <label class="btn btn-outline-warning"><input type="radio" name="clrstatus" value="P"> Pending</label>
               </div>
            </div>
            <div class="col-md-1"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped" id="rTable">
            <thead class="thead-dark"><tr>
               <th>Date</th><th>Vch No</th><th>Type</th><th>Bank A/C</th><th>Particulars</th>
               <th class="text-right">Amount</th><th>Cleared On</th><th>Status</th>
            </tr></thead>
            <tbody id="tableBody"></tbody>
            <tfoot id="tFoot" style="display:none"><tr class="font-weight-bold table-secondary">
               <td colspan="5" class="text-right">TOTAL</td><td id="footTotal" class="text-right">0</td><td colspan="2"></td>
            </tr></tfoot>
         </table>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   function fetch() {
      $.post('{{ route("bankregfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val()
         ,bankcode:$('#bankcode').val(),clrstatus:radioVal('clrstatus')}
      , function(res) {
         if(res.error){alert(res.error);return;}
         var h='';
         $.each(res.data,function(i,r){
            var badge = r.clrstatus=='Cleared' ? '<span class="badge badge-success">Cleared</span>' : '<span class="badge badge-warning">Pending</span>';
            h+='<tr><td>'+fmtDate(r.vdate)+'</td><td>'+(r.vno||'')+'</td><td>'+(r.vtype||'')+'</td>'
              +'<td>'+(r.bankname||r.dispname||'')+'</td><td>'+(r.dispname||'')+'</td>'
              +'<td class="text-right">'+fmt(r.amount)+'</td>'
              +'<td>'+(r.sunappdate?fmtDate(r.sunappdate):'-')+'</td><td>'+badge+'</td></tr>';
         });
         $('#tableBody').html(h);
         $('#footTotal').text(fmt(res.total));
         $('#tFoot').show();
         if($.fn.DataTable.isDataTable('#rTable'))$('#rTable').DataTable().destroy();
         $('#rTable').DataTable({pageLength:25,order:[[0,'asc']]});
      });
   }
   $('#fetchBtn').click(fetch);
   $('input[name=clrstatus]').change(fetch);
   $('#bankcode').change(fetch);
   fetch();
});
</script>
@endsection
