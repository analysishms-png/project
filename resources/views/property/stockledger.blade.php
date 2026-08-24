@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid" style="margin-top:90px;">
   <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
         <h5 class="mb-0"><i class="fas fa-boxes"></i> Stock Ledger</h5>
         <span class="badge badge-primary">HMS.text — StockLedger</span>
      </div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2">
               <label>From Date</label>
               <input type="date" id="fromdate" class="form-control form-control-sm" value="{{ $fromdate }}">
            </div>
            <div class="col-md-2">
               <label>To Date</label>
               <input type="date" id="todate" class="form-control form-control-sm" value="{{ $todate }}">
            </div>
            <div class="col-md-2">
               <label>Item Code</label>
               <select id="itemcode" class="form-control form-control-sm">
                  <option value="">All Items</option>
               </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
               <button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button>
            </div>
         </div>

         {{-- Summary Cards --}}
         <div class="row mb-3" id="summaryCards" style="display:none;">
            <div class="col-md-3">
               <div class="card bg-light">
                  <div class="card-body text-center p-2">
                     <h6 class="text-muted mb-1">Total Items</h6>
                     <h4 id="totalItems">0</h4>
                  </div>
               </div>
            </div>
            <div class="col-md-3">
               <div class="card bg-success text-white">
                  <div class="card-body text-center p-2">
                     <h6 class="mb-1">Total Receipt</h6>
                     <h4 id="totalReceipt">0</h4>
                  </div>
               </div>
            </div>
            <div class="col-md-3">
               <div class="card bg-danger text-white">
                  <div class="card-body text-center p-2">
                     <h6 class="mb-1">Total Issue</h6>
                     <h4 id="totalIssue">0</h4>
                  </div>
               </div>
            </div>
            <div class="col-md-3">
               <div class="card bg-primary text-white">
                  <div class="card-body text-center p-2">
                     <h6 class="mb-1">Net Balance</h6>
                     <h4 id="totalBalance">0</h4>
                  </div>
               </div>
            </div>
         </div>

         {{-- Item Summary --}}
         <h6>Item Summary</h6>
         <div class="table-responsive mb-4">
            <table class="table table-sm table-bordered table-striped" id="summaryTable" style="font-size:12px;">
               <thead class="thead-dark">
                  <tr>
                     <th>Item Code</th>
                     <th>Item Name</th>
                     <th>Unit</th>
                     <th class="text-right">Receipt</th>
                     <th class="text-right">Issue</th>
                     <th class="text-right">Balance</th>
                  </tr>
               </thead>
               <tbody id="summaryBody"></tbody>
            </table>
         </div>

         {{-- Detailed Transactions --}}
         <h6>Detailed Transactions</h6>
         <div class="table-responsive">
            <table class="table table-sm table-bordered" id="detailTable" style="font-size:11px;">
               <thead class="thead-dark">
                  <tr>
                     <th>Date</th>
                     <th>Item</th>
                     <th>Type</th>
                     <th>Voucher</th>
                     <th class="text-right">Qty</th>
                     <th class="text-right">Amount</th>
                  </tr>
               </thead>
               <tbody id="detailBody"></tbody>
            </table>
         </div>
      </div>
   </div>
</div>

<script>
$(function() {
   $('#fetchBtn').click(function() {
      $.post('{{ route("stockledgerfetch") }}', {
         fromdate: $('#fromdate').val(),
         todate: $('#todate').val(),
         itemcode: $('#itemcode').val(),
         _token: '{{ csrf_token() }}'
      }, function(res) {
         // Summary
         var html = '';
         $.each(res.items, function(i, r) {
            html += '<tr>';
            html += '<td>' + r.itemcode + '</td>';
            html += '<td>' + r.itemname + '</td>';
            html += '<td>' + r.unit + '</td>';
            html += '<td class="text-right">' + fmt(r.receipt) + '</td>';
            html += '<td class="text-right">' + fmt(r.issue) + '</td>';
            html += '<td class="text-right font-weight-bold">' + fmt(r.balance) + '</td>';
            html += '</tr>';
         });
         $('#summaryBody').html(html);

         // Cards
         $('#totalItems').text(res.items.length);
         $('#totalReceipt').text(fmt(res.items.reduce((s,r) => s + Number(r.receipt||0), 0)));
         $('#totalIssue').text(fmt(res.items.reduce((s,r) => s + Number(r.issue||0), 0)));
         $('#totalBalance').text(fmt(res.items.reduce((s,r) => s + Number(r.balance||0), 0)));
         $('#summaryCards').show();

         // Detail
         var dhtml = '';
         $.each(res.transactions, function(i, r) {
            dhtml += '<tr>';
            dhtml += '<td>' + r.sundate + '</td>';
            dhtml += '<td>' + r.itemname + '</td>';
            dhtml += '<td><span class="badge ' + (r.suntypes === 'R' ? 'badge-success' : 'badge-danger') + '">' + (r.suntypes === 'R' ? 'RECEIPT' : 'ISSUE') + '</span></td>';
            dhtml += '<td>' + r.vtype + '-' + r.vno + '</td>';
            dhtml += '<td class="text-right">' + fmt(r.qty) + '</td>';
            dhtml += '<td class="text-right">₹' + fmt(r.amount) + '</td>';
            dhtml += '</tr>';
         });
         $('#detailBody').html(dhtml);
      });
   });

   $('#fetchBtn').click();
});
</script>
@endsection
