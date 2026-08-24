@extends('property.layouts.main')
@section('main-container')
<div class="content-body">
   <div class="container-fluid">
      <div class="card">
         <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0"><i class="fas fa-cash-register mr-2"></i> Cashier Settlement</h4>
            <div class="card-tools">
               <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
         </div>
         <div class="card-body">
            <div class="row mb-3">
               <div class="col-md-3">
                  <label class="font-weight-bold">From Date</label>
                  <input type="date" class="form-control form-control-sm" id="fromdate" value="{{ $fromdate }}">
               </div>
               <div class="col-md-3">
                  <label class="font-weight-bold">To Date</label>
                  <input type="date" class="form-control form-control-sm" id="todate" value="{{ $fromdate }}">
               </div>
               <div class="col-md-3">
                  <label>&nbsp;</label>
                  <div>
                     <button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button>
                  </div>
               </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-3" id="summaryCards" style="display:none;">
               <div class="col-md-4">
                  <div class="card bg-success text-white p-3">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                           <h6 class="text-white-50">Total Settlement</h6>
                           <h3 class="mb-0 text-white" id="totalSettlement">₹0</h3>
                        </div>
                        <i class="fas fa-rupee-sign fa-2x"></i>
                     </div>
                  </div>
               </div>
               <div class="col-md-4">
                  <div class="card bg-info text-white p-3">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                           <h6 class="text-white-50">Total Transactions</h6>
                           <h3 class="mb-0 text-white" id="totalTxn">0</h3>
                        </div>
                        <i class="fas fa-credit-card fa-2x"></i>
                     </div>
                  </div>
               </div>
               <div class="col-md-4">
                  <div class="card bg-warning text-white p-3">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                           <h6 class="text-white-50">Days Covered</h6>
                           <h3 class="mb-0 text-white" id="totalDays">0</h3>
                        </div>
                        <i class="fas fa-calendar fa-2x"></i>
                     </div>
                  </div>
               </div>
            </div>

            <div class="row">
               <div class="col-md-6">
                  <h6 class="font-weight-bold mb-2">Mode-Wise Summary</h6>
                  <div class="table-responsive">
                     <table class="table table-sm table-bordered table-striped" style="font-size:12px; width:100%;">
                        <thead class="thead-dark">
                           <tr>
                              <th>Payment Mode</th>
                              <th class="text-right">Amount</th>
                              <th class="text-center">Count</th>
                           </tr>
                        </thead>
                        <tbody id="modeBody"></tbody>
                        <tfoot id="modeFoot" style="display:none;">
                           <tr class="font-weight-bold">
                              <td>TOTAL</td>
                              <td id="modeTotal" class="text-right">0.00</td>
                              <td id="modeCount" class="text-center">0</td>
                           </tr>
                        </tfoot>
                     </table>
                  </div>
               </div>
               <div class="col-md-6">
                  <h6 class="font-weight-bold mb-2">Daily Summary</h6>
                  <div class="table-responsive">
                     <table class="table table-sm table-bordered table-striped" style="font-size:12px; width:100%;">
                        <thead class="thead-dark">
                           <tr>
                              <th>Date</th>
                              <th class="text-right">Amount</th>
                              <th class="text-center">Count</th>
                           </tr>
                        </thead>
                        <tbody id="dailyBody"></tbody>
                     </table>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
   $.ajaxSetup({
      headers: {
         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
   });

   $('#fetchBtn').on('click', function() { fetchData(); });

   function fetchData() {
      var params = {
         fromdate: $('#fromdate').val(),
         todate: $('#todate').val(),
      };
      $.post('{{ route("cashiersettlementfetch") }}', params, function(res) {
         var mh = '';
         if (res && res.modeWise && res.modeWise.length) {
            $.each(res.modeWise, function(i, r) {
               mh += '<tr>';
               mh += '<td><b>' + (r.mode || 'N/A') + '</b></td>';
               mh += '<td class="text-right">' + fmt(r.amount) + '</td>';
               mh += '<td class="text-center">' + (r.count || 0) + '</td>';
               mh += '</tr>';
            });
         } else {
            mh = '<tr><td colspan="3" class="text-center text-muted">No settlement data found</td></tr>';
         }
         $('#modeBody').html(mh);

         if (res) {
            $('#modeTotal').text(fmt(res.total));
            $('#modeCount').text(res.data ? res.data.length : 0);
            $('#modeFoot').show();
         }

         var dh = '';
         if (res && res.daily && res.daily.length) {
            $.each(res.daily, function(i, r) {
               dh += '<tr>';
               dh += '<td>' + fmtDate(r.date) + '</td>';
               dh += '<td class="text-right">' + fmt(r.total) + '</td>';
               dh += '<td class="text-center">' + (r.count || 0) + '</td>';
               dh += '</tr>';
            });
         } else {
            dh = '<tr><td colspan="3" class="text-center text-muted">No daily records found</td></tr>';
         }
         $('#dailyBody').html(dh);

         if (res) {
            $('#totalSettlement').text('₹' + fmt(res.total));
            $('#totalTxn').text(res.data ? res.data.length : 0);
            $('#totalDays').text(res.daily ? res.daily.length : 0);
            $('#summaryCards').show();
         }
      }).fail(function(xhr) {
         console.error('Error fetching cashier settlement:', xhr);
      });
   }

   fetchData();
});
</script>
@endsection
