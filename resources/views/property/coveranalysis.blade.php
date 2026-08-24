@extends('property.layouts.main')
@section('main-container')
<div class="content-body">
   <div class="container-fluid">
      <div class="card">
         <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0"><i class="fas fa-users mr-2"></i> Cover Analysis</h4>
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
               <div class="col-md-3">
                  <div class="card bg-info text-white p-3">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                           <h6 class="text-white-50">Total Bills</h6>
                           <h3 class="mb-0 text-white" id="totalBills">0</h3>
                        </div>
                        <i class="fas fa-receipt fa-2x"></i>
                     </div>
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="card bg-success text-white p-3">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                           <h6 class="text-white-50">Total Covers</h6>
                           <h3 class="mb-0 text-white" id="totalCovers">0</h3>
                        </div>
                        <i class="fas fa-users fa-2x"></i>
                     </div>
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="card bg-warning text-white p-3">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                           <h6 class="text-white-50">Total Revenue</h6>
                           <h3 class="mb-0 text-white" id="totalNetAmt">₹0</h3>
                        </div>
                        <i class="fas fa-rupee-sign fa-2x"></i>
                     </div>
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="card bg-danger text-white p-3">
                     <div class="d-flex justify-content-between align-items-center">
                        <div>
                           <h6 class="text-white-50">Avg per Cover</h6>
                           <h3 class="mb-0 text-white" id="avgPerCover">₹0</h3>
                        </div>
                        <i class="fas fa-calculator fa-2x"></i>
                     </div>
                  </div>
               </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs" id="coverTabs" role="tablist">
               <li class="nav-item">
                  <a class="nav-link active" id="daily-tab" data-toggle="tab" href="#dailyTab" role="tab">Daily Summary</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" id="category-tab" data-toggle="tab" href="#categoryTab" role="tab">Category Breakdown</a>
               </li>
            </ul>

            <div class="tab-content mt-3">
               <div class="tab-pane fade show active" id="dailyTab" role="tabpanel">
                  <div class="table-responsive">
                     <table class="table table-sm table-bordered table-striped" id="dailyTable" style="width:100%;">
                        <thead class="thead-dark">
                           <tr>
                              <th>Date</th>
                              <th>Bills</th>
                              <th>Covers</th>
                              <th class="text-right">Revenue</th>
                              <th class="text-right">Avg / Cover</th>
                           </tr>
                        </thead>
                        <tbody id="dailyBody"></tbody>
                     </table>
                  </div>
               </div>
               <div class="tab-pane fade" id="categoryTab" role="tabpanel">
                  <div class="table-responsive">
                     <table class="table table-sm table-bordered table-striped" id="catTable" style="width:100%;">
                        <thead class="thead-dark">
                           <tr>
                              <th>Outlet</th>
                              <th>Category</th>
                              <th>Bills</th>
                              <th class="text-right">Amount</th>
                           </tr>
                        </thead>
                        <tbody id="catBody"></tbody>
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

   function fetchData() {
      var params = {
         fromdate: $('#fromdate').val(),
         todate: $('#todate').val(),
      };
      $.post('{{ route("coveranalysisfetch") }}', params, function(res) {
         var dh = '';
         if (res && res.daily && res.daily.length) {
            $.each(res.daily, function(i, r) {
               dh += '<tr>';
               dh += '<td>' + fmtDate(r.date) + '</td>';
               dh += '<td>' + r.bills + '</td>';
               dh += '<td>' + r.covers + '</td>';
               dh += '<td class="text-right">' + fmt(r.netAmt) + '</td>';
               dh += '<td class="text-right">' + fmt(r.avgPerCover) + '</td>';
               dh += '</tr>';
            });
         } else {
            dh = '<tr><td colspan="5" class="text-center text-muted">No cover data found</td></tr>';
         }
         $('#dailyBody').html(dh);

         var ch = '';
         if (res && res.categoryWise && res.categoryWise.length) {
            $.each(res.categoryWise, function(i, r) {
               ch += '<tr>';
               ch += '<td>' + (r.DeptName || '') + '</td>';
               ch += '<td>' + (r.CatType || '') + '</td>';
               ch += '<td>' + (r.BillCount || 0) + '</td>';
               ch += '<td class="text-right">' + fmt(r.NetAmt) + '</td>';
               ch += '</tr>';
            });
         } else {
            ch = '<tr><td colspan="4" class="text-center text-muted">No category data found</td></tr>';
         }
         $('#catBody').html(ch);

         if (res) {
            $('#totalBills').text(res.totalBills || 0);
            $('#totalCovers').text(res.totalCovers || 0);
            $('#totalNetAmt').text('₹' + fmt(res.totalNetAmt));
            $('#avgPerCover').text('₹' + fmt(res.totalCovers > 0 ? (res.totalNetAmt / res.totalCovers) : 0));
            $('#summaryCards').show();
         }
      }).fail(function(xhr) {
         console.error('Error fetching cover analysis:', xhr);
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
