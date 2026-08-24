@extends('property.layouts.main')
@section('main-container')
<div class="content-body">
   <div class="container-fluid">
      <div class="card">
         <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0"><i class="fas fa-money-bill-wave mr-2"></i> Guest Payments</h4>
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
               <div class="col-md-4">
                  <div class="card bg-info text-white p-3">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                           <h6 class="text-white-50">Total Receipts</h6>
                           <h3 class="mb-0 text-white" id="totalCount">0</h3>
                        </div>
                        <i class="fas fa-receipt fa-2x"></i>
                     </div>
                  </div>
               </div>
               <div class="col-md-4">
                  <div class="card bg-success text-white p-3">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                           <h6 class="text-white-50">Total Received</h6>
                           <h3 class="mb-0 text-white" id="totalAmount">₹0</h3>
                        </div>
                        <i class="fas fa-rupee-sign fa-2x"></i>
                     </div>
                  </div>
               </div>
            </div>

            <div class="row">
               <div class="col-md-4">
                  <h6 class="font-weight-bold mb-2">Mode-Wise Summary</h6>
                  <div class="table-responsive">
                     <table class="table table-sm table-bordered table-striped" style="font-size:12px; width:100%;">
                        <thead class="thead-dark">
                           <tr>
                              <th>Mode</th>
                              <th class="text-right">Amount</th>
                              <th class="text-center">Count</th>
                           </tr>
                        </thead>
                        <tbody id="modeBody"></tbody>
                     </table>
                  </div>
               </div>
               <div class="col-md-8">
                  <h6 class="font-weight-bold mb-2">Payment Details</h6>
                  <div class="table-responsive">
                     <table class="table table-sm table-bordered table-striped" id="detailTable" style="font-size:12px; width:100%;">
                        <thead class="thead-dark">
                           <tr>
                              <th>Date</th>
                              <th>VNo</th>
                              <th>Guest Name</th>
                              <th>Room</th>
                              <th>Folio</th>
                              <th>Mode</th>
                              <th class="text-right">Amount</th>
                              <th>Remarks</th>
                           </tr>
                        </thead>
                        <tbody id="detailBody"></tbody>
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
   $('#printBtn').on('click', function() { window.print(); });

   function fetchData() {
      var params = {
         fromdate: $('#fromdate').val(),
         todate: $('#todate').val(),
      };
      $.post('{{ route("guestpaymentsfetch") }}', params, function(res) {
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
            mh = '<tr><td colspan="3" class="text-center text-muted">No mode summary</td></tr>';
         }
         $('#modeBody').html(mh);

         var dh = '';
         if (res && res.data && res.data.length) {
            $.each(res.data, function(i, r) {
               dh += '<tr>';
               dh += '<td>' + fmtDate(r.VDate) + '</td>';
               dh += '<td>' + (r.VNo || '') + '</td>';
               dh += '<td><b>' + (r.GuestName || '') + '</b></td>';
               dh += '<td>' + (r.RoomNo || '') + '</td>';
               dh += '<td>' + (r.FolioNo || '') + '</td>';
               dh += '<td>' + (r.PayType || '') + '</td>';
               dh += '<td class="text-right">' + fmt(r.Amount) + '</td>';
               dh += '<td>' + (r.Remarks || '') + '</td>';
               dh += '</tr>';
            });
         }
         $('#detailBody').html(dh);

         if (res) {
            $('#totalAmount').text('₹' + fmt(res.total));
            $('#totalCount').text(res.count || 0);
            $('#summaryCards').show();
         }

         if ($.fn.DataTable.isDataTable('#detailTable')) {
            $('#detailTable').DataTable().destroy();
         }
         $('#detailTable').DataTable({ pageLength: 25, order: [] });
      }).fail(function(xhr) {
         console.error('Error fetching guest payments:', xhr);
      });
   }

   fetchData();
});
</script>
@endsection
