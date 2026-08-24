@extends('property.layouts.main')
@section('main-container')
<div class="content-body">
   <div class="container-fluid">
      <div class="card">
         <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0"><i class="fas fa-exchange-alt mr-2"></i> Room Change History</h4>
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
                           <h6 class="text-white-50">Total Room Changes</h6>
                           <h3 class="mb-0 text-white" id="totalChanges">0</h3>
                        </div>
                        <i class="fas fa-exchange-alt fa-2x"></i>
                     </div>
                  </div>
               </div>
            </div>

            <!-- Data Table -->
            <div class="table-responsive">
               <table class="table table-sm table-bordered table-striped" id="changeTable" style="font-size:12px; width:100%;">
                  <thead class="thead-dark">
                     <tr>
                        <th>#</th>
                        <th>Change Date</th>
                        <th>Time</th>
                        <th>Guest Name</th>
                        <th>Old Room</th>
                        <th>New Room</th>
                        <th>Room Type</th>
                        <th class="text-right">Room Rate</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Reason</th>
                        <th>Changed By</th>
                     </tr>
                  </thead>
                  <tbody id="tableBody"></tbody>
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
      $.post('{{ route("roomchangehistoryfetch") }}', params, function(res) {
         var html = '';
         if (res && res.data) {
            $.each(res.data, function(i, r) {
               html += '<tr>';
               html += '<td>' + (i + 1) + '</td>';
               html += '<td>' + fmtDate(r.ChngDate) + '</td>';
               html += '<td>' + (r.ChngTime || '') + '</td>';
               html += '<td><b>' + (r.GuestName || '') + '</b></td>';
               html += '<td>' + (r.OldRoom || '') + '</td>';
               html += '<td>' + (r.NewRoom || '') + '</td>';
               html += '<td>' + (r.RoomType || '') + '</td>';
               html += '<td class="text-right">' + fmt(r.RoomRate) + '</td>';
               html += '<td>' + fmtDate(r.ChkInDate) + '</td>';
               html += '<td>' + fmtDate(r.ChkOutDate) + '</td>';
               html += '<td>' + (r.Reason || '') + '</td>';
               html += '<td>' + (r.ChangedBy || '') + '</td>';
               html += '</tr>';
            });
         }
         $('#tableBody').html(html);

         if (res) {
            $('#totalChanges').text(res.total || 0);
            $('#summaryCards').show();
         }

         if ($.fn.DataTable.isDataTable('#changeTable')) {
            $('#changeTable').DataTable().destroy();
         }
         $('#changeTable').DataTable({ pageLength: 25, order: [] });
      }).fail(function(xhr) {
         console.error('Error fetching room change history:', xhr);
      });
   }

   function fmtDate(d) {
      if (!d) return '';
      var dt = new Date(d);
      return isNaN(dt.getTime()) ? d : dt.toLocaleDateString('en-GB');
   }

   fetchData();
});
</script>
@endsection
