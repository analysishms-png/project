@extends('property.layouts.main')
@section('main-container')
<div class="content-body">
   <div class="container-fluid">
      <div class="row">
         <div class="col-12">
            <div class="card">
               <div class="card-header d-flex justify-content-between align-items-center">
                  <h4 class="card-title mb-0"><i class="fas fa-utensils mr-2"></i> Food Cost Report</h4>
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

                  <!-- Food Cost Statement -->
                  <div id="resultArea" style="display:none;">
                     <div class="row">
                        <div class="col-md-8">
                           <div class="card card-outline card-primary">
                              <div class="card-header">
                                 <h5 class="card-title mb-0">Food Cost Statement</h5>
                              </div>
                              <div class="card-body p-0">
                                 <table class="table table-sm table-bordered mb-0">
                                    <tbody>
                                       <tr>
                                          <td><b>Opening Stock (Stores) (+)</b></td>
                                          <td class="text-right" id="openingStock">₹0.00</td>
                                       </tr>
                                       <tr>
                                          <td><b>Purchases (Stores) (+)</b></td>
                                          <td class="text-right" id="purchases">₹0.00</td>
                                       </tr>
                                       <tr class="table-info">
                                          <td><b>Gross Stock Available</b></td>
                                          <td class="text-right" id="grossStock">₹0.00</td>
                                       </tr>
                                       <tr>
                                          <td>Less: Store Closing Stock (-)</td>
                                          <td class="text-right" id="closingStock">₹0.00</td>
                                       </tr>
                                       <tr class="table-warning">
                                          <td><b>Net Stock Consumed</b></td>
                                          <td class="text-right" id="netStock">₹0.00</td>
                                       </tr>
                                       <tr>
                                          <td>Less: Staff Kitchen Issue (-)</td>
                                          <td class="text-right" id="staffKitchenIssue">₹0.00</td>
                                       </tr>
                                       <tr class="table-danger">
                                          <td><b>Net F&B Consumption</b></td>
                                          <td class="text-right font-weight-bold" id="netConsumption">₹0.00</td>
                                       </tr>
                                    </tbody>
                                 </table>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-4">
                           <div class="card card-outline card-success mb-3">
                              <div class="card-header">
                                 <h5 class="card-title mb-0">Food Sales</h5>
                              </div>
                              <div class="card-body p-0">
                                 <table class="table table-sm table-bordered mb-0">
                                    <tbody>
                                       <tr>
                                          <td>POS Outlets</td>
                                          <td class="text-right" id="foodSalesPOS">₹0.00</td>
                                       </tr>
                                       <tr>
                                          <td>Banquet</td>
                                          <td class="text-right" id="foodSalesBanquet">₹0.00</td>
                                       </tr>
                                       <tr class="table-success">
                                          <td><b>Total Food Sales</b></td>
                                          <td class="text-right font-weight-bold" id="totalFoodSales">₹0.00</td>
                                       </tr>
                                    </tbody>
                                 </table>
                              </div>
                           </div>

                           <div class="card card-outline card-danger">
                              <div class="card-header">
                                 <h5 class="card-title mb-0">Food Cost %</h5>
                              </div>
                              <div class="card-body text-center">
                                 <h2 id="foodCostPct" class="text-danger font-weight-bold">0%</h2>
                                 <small class="text-muted">(Net Consumption / Total Food Sales) × 100</small>
                              </div>
                           </div>
                        </div>
                     </div>

                     <!-- Additional Details -->
                     <div class="row mt-3">
                        <div class="col-md-6">
                           <div class="card card-outline card-secondary">
                              <div class="card-header">
                                 <h5 class="card-title mb-0">Additional Deductions</h5>
                              </div>
                              <div class="card-body p-0">
                                 <table class="table table-sm table-bordered mb-0">
                                    <tbody>
                                       <tr>
                                          <td>Kitchen Consumption (direct)</td>
                                          <td class="text-right" id="kitchenConsumption">₹0.00</td>
                                       </tr>
                                       <tr>
                                          <td>NC KOT Deduction</td>
                                          <td class="text-right" id="ncKotDeduction">₹0.00</td>
                                       </tr>
                                    </tbody>
                                 </table>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="card card-outline card-info">
                              <div class="card-header">
                                 <h5 class="card-title mb-0">POS Outlet Breakdown</h5>
                              </div>
                              <div class="card-body p-0">
                                 <table class="table table-sm table-bordered mb-0" id="posBreakdownTable">
                                    <thead class="thead-light">
                                       <tr><th>Outlet</th><th class="text-right">Sales</th></tr>
                                    </thead>
                                    <tbody id="posBreakdownBody">
                                    </tbody>
                                 </table>
                              </div>
                           </div>
                        </div>
                     </div>
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
      $.post('{{ route("foodcostfetch") }}', params, function(res) {
         if (res) {
            $('#openingStock').text('₹' + fmt(res.openingStock));
            $('#purchases').text('₹' + fmt(res.purchases));
            $('#grossStock').text('₹' + fmt((res.openingStock || 0) + (res.purchases || 0)));
            $('#closingStock').text('₹' + fmt(res.closingStock));
            $('#netStock').text('₹' + fmt(res.netStock));
            $('#staffKitchenIssue').text('₹' + fmt(res.staffKitchenIssue));
            $('#netConsumption').text('₹' + fmt(res.netConsumption));
            $('#foodSalesPOS').text('₹' + fmt(res.foodSalesPOS));
            $('#foodSalesBanquet').text('₹' + fmt(res.foodSalesBanquet));
            $('#totalFoodSales').text('₹' + fmt(res.totalFoodSales));
            $('#foodCostPct').text((res.foodCostPct || 0) + '%');
            $('#kitchenConsumption').text('₹' + fmt(res.kitchenConsumption));
            $('#ncKotDeduction').text('₹' + fmt(res.ncKotDeduction));

            var html = '';
            if (res.posBreakdown && res.posBreakdown.length) {
               $.each(res.posBreakdown, function(i, row) {
                  html += '<tr><td>' + (row.RestName || row.RestCode || 'Outlet') + '</td><td class="text-right">₹' + fmt(row.Amt) + '</td></tr>';
               });
            } else {
               html = '<tr><td colspan="2" class="text-center text-muted">No POS outlet sales found for selected period</td></tr>';
            }
            $('#posBreakdownBody').html(html);
            $('#resultArea').show();
         }
      }).fail(function(xhr) {
         console.error('Error fetching food cost:', xhr);
      });
   }

   fetchData();
});
</script>
@endsection
