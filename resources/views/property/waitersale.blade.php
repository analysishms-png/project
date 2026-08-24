@extends('property.layouts.main')
@section('main-container')
<div class="content-body">
   <div class="container-fluid">
      <div class="card">
         <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0"><i class="fas fa-user-tie mr-2"></i> Waiter-Wise Sale</h4>
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
                     <button class="btn btn-success btn-sm" id="printBtn"><i class="fas fa-print"></i> Print</button>
                  </div>
               </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-3" id="summaryCards" style="display:none;">
               <div class="col-md-3">
                  <div class="card bg-info text-white p-3">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                           <h6 class="text-white-50">Total Waiters</h6>
                           <h3 class="mb-0 text-white" id="totalWaiters">0</h3>
                        </div>
                        <i class="fas fa-user fa-2x"></i>
                     </div>
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="card bg-success text-white p-3">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                           <h6 class="text-white-50">Total Sale</h6>
                           <h3 class="mb-0 text-white" id="totalSale">₹0</h3>
                        </div>
                        <i class="fas fa-rupee-sign fa-2x"></i>
                     </div>
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="card bg-warning text-white p-3">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                           <h6 class="text-white-50">Total KOTs</h6>
                           <h3 class="mb-0 text-white" id="totalKOTs">0</h3>
                        </div>
                        <i class="fas fa-receipt fa-2x"></i>
                     </div>
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="card bg-danger text-white p-3">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                           <h6 class="text-white-50">Total Tips</h6>
                           <h3 class="mb-0 text-white" id="totalTips">₹0</h3>
                        </div>
                        <i class="fas fa-hand-holding-usd fa-2x"></i>
                     </div>
                  </div>
               </div>
            </div>

            <!-- Data Table -->
            <div class="table-responsive">
               <table class="table table-sm table-bordered table-striped" id="waiterTable" style="font-size:12px; width:100%;">
                  <thead class="thead-dark">
                     <tr>
                        <th>#</th>
                        <th>Outlet</th>
                        <th>Waiter</th>
                        <th>KOTs</th>
                        <th class="text-right">Net Sale</th>
                        <th class="text-right">Tax</th>
                        <th class="text-right">Tips</th>
                     </tr>
                  </thead>
                  <tbody id="tableBody"></tbody>
                  <tfoot id="tableFoot" style="display:none;">
                     <tr class="font-weight-bold">
                        <td colspan="3" class="text-right">TOTAL</td>
                        <td id="footKOTs" class="text-center">0</td>
                        <td id="footSale" class="text-right">0.00</td>
                        <td id="footTax" class="text-right">0.00</td>
                        <td id="footTips" class="text-right">0.00</td>
                     </tr>
                  </tfoot>
               </table>
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
   $('#printBtn').on('click', function() { window.print(); });

   function fetchData() {
      var params = {
         fromdate: $('#fromdate').val(),
         todate: $('#todate').val(),
      };
      $.post('{{ route("waitersalefetch") }}', params, function(res) {
         var html = '';
         if (res && res.data) {
            $.each(res.data, function(i, r) {
               html += '<tr>';
               html += '<td>' + (i + 1) + '</td>';
               html += '<td>' + (r.DeptName || '') + '</td>';
               html += '<td><b>' + (r.WaiterName || r.WaiterCode || '') + '</b></td>';
               html += '<td class="text-center">' + (r.KOTCount || 0) + '</td>';
               html += '<td class="text-right">' + fmt(r.NetSale) + '</td>';
               html += '<td class="text-right">' + fmt(r.TaxAmt) + '</td>';
               html += '<td class="text-right">' + fmt(r.TipAmt) + '</td>';
               html += '</tr>';
            });
         }
         $('#tableBody').html(html);

         if (res) {
            var kots = res.data ? res.data.reduce((s, r) => s + Number(r.KOTCount || 0), 0) : 0;
            $('#totalWaiters').text(res.total || 0);
            $('#totalSale').text('₹' + fmt(res.totalSale));
            $('#totalKOTs').text(kots);
            $('#totalTips').text('₹' + fmt(res.totalTips));
            $('#summaryCards').show();

            $('#footKOTs').text(kots);
            $('#footSale').text(fmt(res.totalSale));
            $('#footTax').text(fmt(res.totalTax));
            $('#footTips').text(fmt(res.totalTips));
            $('#tableFoot').show();
         }

         if ($.fn.DataTable.isDataTable('#waiterTable')) {
            $('#waiterTable').DataTable().destroy();
         }
         $('#waiterTable').DataTable({ pageLength: 25, order: [] });
      }).fail(function(xhr) {
         console.error('Error fetching waiter sale:', xhr);
      });
   }

   fetchData();
});
</script>
@endsection
