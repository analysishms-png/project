@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid" style="margin-top:90px;">
   <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
         <h4 class="card-title mb-0"><i class="fas fa-book"></i> Booking Detail Report</h4>
         <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print"></i> Print</button>
      </div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ date('Y-m-d') }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ date('Y-m-d') }}"></div>
            <div class="col-md-3">
               <label class="mb-1">Status</label>
               <div class="btn-group btn-group-toggle btn-group-sm" data-toggle="buttons" id="statusGroup">
                  <label class="btn btn-outline-primary active"><input type="radio" name="resstatus" value="" checked> All</label>
                  <label class="btn btn-outline-primary"><input type="radio" name="resstatus" value="Confirm"> Confirm</label>
                  <label class="btn btn-outline-primary"><input type="radio" name="resstatus" value="Tentative"> Tentative</label>
                  <label class="btn btn-outline-primary"><input type="radio" name="resstatus" value="Waitlist"> Waitlist</label>
               </div>
            </div>
            <div class="col-md-2">
               <label class="mb-1">Cancelled</label>
               <div class="btn-group btn-group-toggle btn-group-sm" data-toggle="buttons" id="cancelGroup">
                  <label class="btn btn-outline-secondary active"><input type="radio" name="inccancelled" value="N" checked> Hide</label>
                  <label class="btn btn-outline-secondary"><input type="radio" name="inccancelled" value="Y"> Show</label>
               </div>
            </div>
            <div class="col-md-1"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped" id="rTable">
            <thead class="thead-dark"><tr><th class="">Book No</th>
               <th class="">Guest</th>
               <th class="">Book Date</th>
               <th class=" text-right">Rooms</th>
               <th class="">Source</th>
               <th class="">Status</th>
               <th class="">Cancelled</th>
               <th class="">Mobile</th>
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
   function radioVal(name){return $("input[name='"+name+"']:checked").val()||'';}
   function fetch() {
      $.post('{{ route("bookingdetailfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val()
         ,resstatus:radioVal('resstatus')
         ,inccancelled:radioVal('inccancelled')}
      , function(res) {
         if(res.error){alert(res.error);return;}
         var h='';var tot=0;$.each(res.data,function(i,r){
            h+='<tr>'+'<td class="">'+(r.BookNo||'')+'</td>'+'<td class="">'+'<b>'+(r.GuestName||'')+'</b>'+'</td>'+'<td class="">'+fmtDate(r.vdate)+'</td>'+'<td class="text-right">'+(r.NoofRooms??0)+'</td>'+'<td class="">'+(r.BussSource||'')+'</td>'+'<td class="">'+(r.ResStatus||'')+'</td>'+'<td class="">'+(r.Cancel||'')+'</td>'+'<td class="">'+(r.MobNo||'')+'</td>'+'</tr>';});
         $('#tableBody').html(h);
         if($.fn.DataTable.isDataTable('#rTable'))$('#rTable').DataTable().destroy();
         $('#rTable').DataTable({pageLength:25,order:[]});
      });
   }
   $('#fetchBtn').click(fetch);
   $('input[name=resstatus],input[name=inccancelled]').change(function(){fetch();});
   fetch();
});
</script>
@endsection