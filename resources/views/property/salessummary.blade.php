@extends('property.layouts.property')
@section('content')
<div class="content-wrapper">
   <div class="content-header">
      <div class="container-fluid">
         <div class="row mb-2">
            <div class="col-sm-6">
               <h1 class="m-0 text-capitalize">{{ str_replace('_', ' ', $view) }}</h1>
            </div>
         </div>
      </div>
   </div>
   <section class="content">
      <div class="container-fluid">
         <div class="row">
            <div class="col-12">
               <div class="card">
                  <div class="card-header">
                     <div class="row">
                        <div class="col-md-3">
                           <label>From Date</label>
                           <input type="date" id="fromdate" class="form-control form-control-sm" value="{{ $fd }}">
                        </div>
                        <div class="col-md-3">
                           <label>To Date</label>
                           <input type="date" id="todate" class="form-control form-control-sm" value="{{ $td }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                           <button id="fetchBtn" class="btn btn-sm btn-primary">Fetch</button>
                           <button id="exportCsv" class="btn btn-sm btn-success ml-2">CSV</button>
                           <button id="printBtn" class="btn btn-sm btn-secondary ml-2">Print</button>
                        </div>
                     </div>
                  </div>
                  <div class="card-body">
                     <div id="summaryArea"></div>
                     <div class="table-responsive">
                        <table id="reportTable" class="table table-bordered table-sm table-striped" style="display:none;">
                           <thead id="tableHead"></thead>
                           <tbody id="tableBody"></tbody>
                           <tfoot id="tableFoot"></tfoot>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>
</div>
@endsection
@section('scripts')
<script>
$(function(){
   function fetchData(){
      var fd=$('#fromdate').val();
      var td=$('#todate').val();
      var url=window.location.href.replace(/\/[^\/]*$/,'')+'fetch';
      $.post(url,{fromdate:fd,todate:td,_token:$('meta[name="csrf-token"]').attr('content')},function(res){
         if(res.error){alert(res.error);return;}
         renderTable(res);
      });
   }
   function renderTable(res){
      var data=res.data||[];
      if(!data.length){$('#reportTable').hide();$('#summaryArea').html('<div class="alert alert-info">No records found</div>');return;}
      var cols=Object.keys(data[0]);
      var html='';cols.forEach(function(c){html+='<th>'+c.toUpperCase().replace(/_/g,' ')+'</th>';});
      $('#tableHead').html('<tr>'+html+'</tr>');
      var body='';data.forEach(function(row){
         body+='<tr>';cols.forEach(function(c){
            var v=row[c];body+='<td>'+(v!==null?v.toLocaleString?Number(v).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}):v:'')+'</td>';
         });body+='</tr>';
      });$('#tableBody').html(body);$('#tableHead tr').clone().appendTo('#tableFoot');
      // Totals
      var foot='';cols.forEach(function(c){
         var sum=0;data.forEach(function(r){var n=parseFloat(r[c]);if(!isNaN(n))sum+=n;});
         foot+='<th>'+(sum?sum.toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}):'')+'</th>';
      });$('#tableFoot').html('<tr>'+foot+'</tr>');
      $('#reportTable').show();
   }
   $('#fetchBtn').click(fetchData);
   $('#exportCsv').click(function(){
      var csv=[];$('#reportTable tr').each(function(){var row=[];$(this).find('th,td').each(function(){row.push('"'+$(this).text().replace(/"/g,'""')+'"');});csv.push(row.join(','));});
      var blob=new Blob([csv.join('\n')],{type:'text/csv'});
      var a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download='report.csv';a.click();
   });
   $('#printBtn').click(function(){window.print();});
   fetchData();
});
</script>
@endsection
