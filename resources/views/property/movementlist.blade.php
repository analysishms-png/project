@extends('property.layouts.main')
@section('main-container')
<div class="content-body">
   <div class="container-fluid">
      <div class="row">
         <div class="col-12">
            <div class="card">
               <div class="card-header d-flex justify-content-between align-items-center">
                  <h4 class="card-title mb-0"><i class="fas fa-exchange-alt mr-2"></i> Movement List</h4>
                  <div class="card-tools">
                     <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                  </div>
               </div>
               <div class="card-body">
                  <div class="row mb-3">
                     <div class="col-md-2">
                        <label class="font-weight-bold">From Date</label>
                        <input type="date" class="form-control form-control-sm" id="fromdate" value="{{ $fromdate }}">
                     </div>
                     <div class="col-md-2">
                        <label class="font-weight-bold">To Date</label>
                        <input type="date" class="form-control form-control-sm" id="todate" value="{{ $fromdate }}">
                     </div>
                     <div class="col-md-2">
                        <label class="font-weight-bold">Pending</label>
                        <select class="form-control form-control-sm" id="pendingyn">
                           <option value="all">All</option>
                           <option value="pending">Pending (Not Checked-In)</option>
                        </select>
                     </div>
                     <div class="col-md-2">
                        <label class="font-weight-bold">Res. Status</label>
                        <select class="form-control form-control-sm" id="reststatus">
                           <option value="all">All</option>
                           <option value="confirm">Confirm</option>
                           <option value="tentative">Tentative</option>
                           <option value="waiting">Waiting</option>
                        </select>
                     </div>
                     <div class="col-md-2">
                        <label class="font-weight-bold">Sort By</label>
                        <select class="form-control form-control-sm" id="sortby">
                           <option value="arrdate">Arrival Date</option>
                           <option value="guest">Guest Name</option>
                           <option value="company">Company</option>
                           <option value="travelagent">Travel Agent</option>
                           <option value="resstatus">Res. Status</option>
                        </select>
                     </div>
                     <div class="col-md-2">
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
                                 <h6 class="text-white-50">Total Bookings</h6>
                                 <h3 class="mb-0 text-white" id="totalBookings">0</h3>
                              </div>
                              <i class="fas fa-calendar-check fa-2x"></i>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="card bg-success text-white p-3">
                           <div class="d-flex justify-content-between align-items-center">
                              <div>
                                 <h6 class="text-white-50">Total Pax</h6>
                                 <h3 class="mb-0 text-white" id="totalPax">0</h3>
                              </div>
                              <i class="fas fa-users fa-2x"></i>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="card bg-warning text-white p-3">
                           <div class="d-flex justify-content-between align-items-center">
                              <div>
                                 <h6 class="text-white-50">Total Rooms</h6>
                                 <h3 class="mb-0 text-white" id="totalRooms">0</h3>
                              </div>
                              <i class="fas fa-bed fa-2x"></i>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="card bg-danger text-white p-3">
                           <div class="d-flex justify-content-between align-items-center">
                              <div>
                                 <h6 class="text-white-50">Total Advance</h6>
                                 <h3 class="mb-0 text-white" id="totalAdvance">₹0</h3>
                              </div>
                              <i class="fas fa-rupee-sign fa-2x"></i>
                           </div>
                        </div>
                     </div>
                  </div>

                  <!-- Data Table -->
                  <div class="table-responsive">
                     <table id="movementTable" class="table table-sm table-bordered table-striped" style="font-size:12px; width:100%;">
                        <thead class="thead-dark">
                           <tr>
                              <th>#</th>
                              <th>Res No</th>
                              <th>Guest Name</th>
                              <th>Mobile</th>
                              <th>Company / TA</th>
                              <th>Room Type</th>
                              <th>Rooms</th>
                              <th>Arr Date</th>
                              <th>Arr Time</th>
                              <th>Dep Date</th>
                              <th>Pax</th>
                              <th>Plan</th>
                              <th>Booked By</th>
                              <th>Status</th>
                              <th>Advance</th>
                              <th>Remarks</th>
                           </tr>
                        </thead>
                        <tbody id="tableBody">
                        </tbody>
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

   $('#fetchBtn').on('click', function() {
      fetchData();
   });

   $('#printBtn').on('click', function() {
      var params = getParams();
      var url = '{{ route("printmovementlist") }}?' + $.param(params);
      window.open(url, '_blank');
   });

   function getParams() {
      return {
         fromdate: $('#fromdate').val(),
         todate: $('#todate').val(),
         pendingyn: $('#pendingyn').val(),
         reststatus: $('#reststatus').val(),
         sortby: $('#sortby').val(),
      };
   }

   function fetchData() {
      var params = getParams();
      $.post('{{ route("movementlistfetch") }}', params, function(res) {
         var html = '';
         if (res && res.data) {
            $.each(res.data, function(i, row) {
               html += '<tr>';
               html += '<td>' + (i + 1) + '</td>';
               html += '<td>' + (row.ResNo || '') + '</td>';
               html += '<td><b>' + (row.GuestName || '') + '</b></td>';
               html += '<td>' + (row.MobileNo || '') + '</td>';
               html += '<td>' + (row.Company || '') + '</td>';
               html += '<td>' + (row.RoomType || '') + '</td>';
               html += '<td class="text-center">' + (row.RoomDet || '') + '</td>';
               html += '<td>' + formatDate(row.ArrDate) + '</td>';
               html += '<td>' + (row.ArrTime || '') + '</td>';
               html += '<td>' + formatDate(row.DepDate) + '</td>';
               html += '<td class="text-center">' + (row.Pax || 0) + '/' + (row.Child || 0) + '</td>';
               html += '<td>' + (row.PlanName || '') + '</td>';
               html += '<td>' + (row.BookedBy || '') + '</td>';
               html += '<td>' + getStatusBadge(row.ResStatus) + '</td>';
               html += '<td class="text-right">' + formatCurrency(row.advance) + '</td>';
               html += '<td>' + (row.Remarks || '') + '</td>';
               html += '</tr>';
            });
         }
         $('#tableBody').html(html);

         // Update summary
         $('#totalBookings').text(res.total || 0);
         $('#totalPax').text(res.totalPax || 0);
         $('#totalRooms').text(res.totalRooms || 0);
         $('#totalAdvance').text('₹' + Number(res.totalAdvance || 0).toLocaleString('en-IN', {minimumFractionDigits: 2}));
         $('#summaryCards').show();

         // Reinit DataTable
         if ($.fn.DataTable.isDataTable('#movementTable')) {
            $('#movementTable').DataTable().destroy();
         }
         $('#movementTable').DataTable({ pageLength: 25, order: [] });
      }).fail(function(xhr) {
         console.error('Error fetching movement list:', xhr);
      });
   }

   function formatDate(d) {
      if (!d) return '';
      var dt = new Date(d);
      return isNaN(dt.getTime()) ? d : dt.toLocaleDateString('en-GB');
   }

   function formatCurrency(v) {
      return Number(v || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
   }

   function getStatusBadge(s) {
      if (!s || s === '') return '<span class="badge badge-success">Confirm</span>';
      if (s === 'Confirm') return '<span class="badge badge-success">Confirm</span>';
      if (s === 'Tentative') return '<span class="badge badge-warning">Tentative</span>';
      if (s === 'Waiting') return '<span class="badge badge-info">Waiting</span>';
      if (s === 'Cancel') return '<span class="badge badge-danger">Cancelled</span>';
      return '<span class="badge badge-secondary">' + s + '</span>';
   }

   fetchData();
});
</script>
@endsection
