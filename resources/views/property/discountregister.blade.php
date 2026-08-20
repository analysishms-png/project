@extends('property.layouts.main')
@section('main-container')
<div class="content-body">
   <div class="container-fluid">
      <div class="row">
         <div class="col-12">
            <div class="card">
               <div class="card-header d-flex justify-content-between align-items-center">
                  <h4 class="card-title mb-0"><i class="fas fa-percent mr-2"></i> Discount Register</h4>
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
                        <label class="font-weight-bold">Outlet</label>
                        <select class="form-control form-control-sm" id="restcode">
                           <option value="all">All Outlets</option>
                           @if(isset($outlets))
                              @foreach($outlets as $outlet)
                                 <option value="{{ $outlet->dcode }}">{{ $outlet->name }}</option>
                              @endforeach
                           @endif
                        </select>
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
                                 <h6 class="text-white-50">Discounted Items</h6>
                                 <h3 class="mb-0 text-white" id="totalItems">0</h3>
                              </div>
                              <i class="fas fa-list fa-2x"></i>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="card bg-primary text-white p-3">
                           <div class="d-flex justify-content-between align-items-center">
                              <div>
                                 <h6 class="text-white-50">Total Bill Amount</h6>
                                 <h3 class="mb-0 text-white" id="totalAmount">₹0</h3>
                              </div>
                              <i class="fas fa-rupee-sign fa-2x"></i>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="card bg-danger text-white p-3">
                           <div class="d-flex justify-content-between align-items-center">
                              <div>
                                 <h6 class="text-white-50">Total Discount</h6>
                                 <h3 class="mb-0 text-white" id="totalDiscount">₹0</h3>
                              </div>
                              <i class="fas fa-percentage fa-2x"></i>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="card bg-success text-white p-3">
                           <div class="d-flex justify-content-between align-items-center">
                              <div>
                                 <h6 class="text-white-50">Net Amount</h6>
                                 <h3 class="mb-0 text-white" id="netAmount">₹0</h3>
                              </div>
                              <i class="fas fa-calculator fa-2x"></i>
                           </div>
                        </div>
                     </div>
                  </div>

                  <!-- Data Table -->
                  <div class="table-responsive">
                     <table id="discountTable" class="table table-sm table-bordered table-striped" style="font-size:12px; width:100%;">
                        <thead class="thead-dark">
                           <tr>
                              <th>#</th>
                              <th>Date</th>
                              <th>Bill No</th>
                              <th>Outlet</th>
                              <th>Item</th>
                              <th>Qty</th>
                              <th>Rate</th>
                              <th>Amount</th>
                              <th>Disc %</th>
                              <th>Disc Amt</th>
                           </tr>
                        </thead>
                        <tbody id="tableBody">
                        </tbody>
                        <tfoot id="tableFoot" style="display:none;">
                           <tr class="font-weight-bold">
                              <td colspan="5" class="text-right">TOTAL</td>
                              <td id="footQty" class="text-center">0</td>
                              <td></td>
                              <td id="footAmount" class="text-right">0.00</td>
                              <td></td>
                              <td id="footDisc" class="text-right text-danger">0.00</td>
                           </tr>
                        </tfoot>
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
         restcode: $('#restcode').val(),
      };
      $.post('{{ route("discountregisterfetch") }}', params, function(res) {
         var html = '';
         if (res && res.data) {
            $.each(res.data, function(i, row) {
               html += '<tr>';
               html += '<td>' + (i + 1) + '</td>';
               html += '<td>' + formatDate(row.VDate) + '</td>';
               html += '<td>' + (row.VType || '') + '/' + (row.VNo || '') + '</td>';
               html += '<td>' + (row.DeptName || '') + '</td>';
               html += '<td>' + (row.ItemName || '') + '</td>';
               html += '<td class="text-center">' + (row.Quantity || 0) + '</td>';
               html += '<td class="text-right">' + formatCurrency(row.Rate) + '</td>';
               html += '<td class="text-right">' + formatCurrency(row.Amount) + '</td>';
               html += '<td class="text-center">' + (row.DiscPer || 0) + '%</td>';
               html += '<td class="text-right text-danger">' + formatCurrency(row.DiscAmt) + '</td>';
               html += '</tr>';
            });
         }
         $('#tableBody').html(html);

         // Update summary
         $('#totalItems').text(res.total || 0);
         $('#totalAmount').text('₹' + Number(res.totalAmount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2}));
         $('#totalDiscount').text('₹' + Number(res.totalDiscount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2}));
         $('#netAmount').text('₹' + Number((res.totalAmount || 0) - (res.totalDiscount || 0)).toLocaleString('en-IN', {minimumFractionDigits: 2}));
         $('#summaryCards').show();

         // Update foot
         if (res && res.data) {
            $('#footQty').text(res.data.reduce((s, r) => s + Number(r.Quantity || 0), 0));
            $('#footAmount').text(formatCurrency(res.totalAmount));
            $('#footDisc').text(formatCurrency(res.totalDiscount));
            $('#tableFoot').show();
         }

         // Reinit DataTable
         if ($.fn.DataTable.isDataTable('#discountTable')) {
            $('#discountTable').DataTable().destroy();
         }
         $('#discountTable').DataTable({ pageLength: 25, order: [] });
      }).fail(function(xhr) {
         console.error('Error fetching discount register:', xhr);
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

   fetchData();
});
</script>
@endsection
