@extends('property.layouts.main')
@section('content')
<div class="content-body">
   <div class="page-header mb-3">
      <div class="row align-items-center">
         <div class="col">
            <h3 class="page-title">{{ ucfirst(str_replace(['/','-'],' ',$route)) }}</h3>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-12">
         <div class="card">
            <div class="card-body">
               <form id="filterForm" class="row g-3 mb-4">
                  @if(!in_array($route, ['roznamcha']))
                  <div class="col-md-3">
                     <label class="form-label">From Date</label>
                     <input type="date" class="form-control" id="fromdate" value="{{ date('Y-m-d') }}">
                  </div>
                  @endif
                  <div class="col-md-3">
                     <label class="form-label">To Date</label>
                     <input type="date" class="form-control" id="todate" value="{{ date('Y-m-d') }}">
                  </div>
                  @if(in_array($route, ['ledgerdeb','generalledger2']))
                  <div class="col-md-3">
                     <label class="form-label">Party/Account</label>
                     <select class="form-control" id="partycode">
                        <option value="">All</option>
                        @isset($accounts)
                        @foreach($accounts as $acc)
                        <option value="{{ $acc->sub_code }}">{{ $acc->name }}</option>
                        @endforeach
                        @endisset
                     </select>
                  </div>
                  @endif
                  @if(in_array($route, ['gstr1report']))
                  <div class="col-md-3">
                     <label class="form-label">Month</label>
                     <input type="month" class="form-control" id="month" value="{{ date('Y-m') }}">
                  </div>
                  @endif
                  <div class="col-md-3 d-flex align-items-end">
                     <button type="button" class="btn btn-primary me-2" onclick="fetchData()">Search</button>
                     <button type="button" class="btn btn-success" onclick="exportExcel()">Export</button>
                  </div>
               </form>
               <div class="table-responsive">
                  <table class="table table-bordered table-striped" id="reportTable">
                     <thead id="tableHead"></thead>
                     <tbody id="tableBody"></tbody>
                  </table>
               </div>
               <div id="summaryArea" class="mt-3" style="display:none;"></div>
            </div>
         </div>
      </div>
   </div>
</div>
@endsection
@section('scripts')
<script>
var route = '{{ $route ?? "" }}';
$(document).ready(function(){ fetchData(); });

function fetchData() {
   var params = {};
   if ($('#fromdate').length) params.fromdate = $('#fromdate').val();
   if ($('#todate').length) params.todate = $('#todate').val();
   if ($('#partycode').length) params.partycode = $('#partycode').val();
   if ($('#month').length) params.month = $('#month').val();

   $.post(route + 'fetch', params, function(res) {
      if (res.summary) {
         var html = '<div class="row">';
         $.each(res.summary, function(k,v) {
            if (v && k !== 'invoices') html += '<div class="col-md-3"><strong>'+k+':</strong> ₹'+Number(v).toLocaleString('en-IN')+'</div>';
         });
         html += '</div>';
         $('#summaryArea').html(html).show();
      }
      renderTable(res.data);
   });
}

function renderTable(data) {
   if (!data || data.length === 0) { $('#tableHead').html(''); $('#tableBody').html('<tr><td class="text-center">No records found</td></tr>'); return; }
   var cols = Object.keys(data[0]);
   var thead = '<tr>' + cols.map(function(c){ return '<th>'+c.toUpperCase().replace(/_/g,' ')+'</th>'; }).join('') + '</tr>';
   $('#tableHead').html(thead);
   var tbody = '';
   data.forEach(function(row) {
      tbody += '<tr>' + cols.map(function(c){
         var v = row[c];
         if (typeof v === 'number' && (c.includes('amt') || c.includes('rate') || c.includes('total') || c.includes('amount'))) return '₹'+Number(v).toLocaleString('en-IN',{minimumFractionDigits:2});
         return v || '';
      }).join('</td><td>') + '</tr>';
   });
   $('#tableBody').html(tbody);
}

function exportExcel() {
   var table = document.getElementById('reportTable');
   var csv = [];
   for (var i = 0; i < table.rows.length; i++) {
      var row = [];
      for (var j = 0; j < table.rows[i].cells.length; j++) row.push(table.rows[i].cells[j].innerText);
      csv.push(row.join(','));
   }
   var blob = new Blob([csv.join('\n')], {type:'text/csv'});
   var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = route+'.csv'; a.click();
}
</script>
@endsection
