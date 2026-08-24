@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid" style="margin-top:90px;">
   <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
         <h5 class="mb-0"><i class="fas fa-cash-register"></i> Sales Day Book</h5>
         <span class="badge badge-primary">HMS.text — SalesDayBook</span>
      </div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-3">
               <label>From Date</label>
               <input type="date" id="fromdate" class="form-control form-control-sm" value="{{ $fromdate }}">
            </div>
            <div class="col-md-3">
               <label>To Date</label>
               <input type="date" id="todate" class="form-control form-control-sm" value="{{ $todate }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
               <button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button>
               <button class="btn btn-success btn-sm ml-2" id="exportBtn"><i class="fas fa-file-excel"></i> Export</button>
            </div>
         </div>

         <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped" id="dataTable" style="font-size:12px;">
               <thead class="thead-dark">
                  <tr>
                     <th>Date</th>
                     <th>Bill No</th>
                     <th>Outlet</th>
                     <th>Room</th>
                     <th>Guest</th>
                     <th>Payment</th>
                     <th class="text-right">Gross</th>
                     <th class="text-right">Tax</th>
                     <th class="text-right">Disc</th>
                     <th class="text-right">Net Amount</th>
                     <th>User</th>
                  </tr>
               </thead>
               <tbody id="dataBody"></tbody>
               <tfoot>
                  <tr class="font-weight-bold">
                     <td colspan="6">TOTAL</td>
                     <td class="text-right" id="totalGross">0.00</td>
                     <td class="text-right" id="totalTax">0.00</td>
                     <td class="text-right" id="totalDisc">0.00</td>
                     <td class="text-right" id="totalNet">0.00</td>
                     <td></td>
                  </tr>
               </tfoot>
            </table>
         </div>
      </div>
   </div>
</div>

<script>
$(function() {
   $('#fetchBtn').click(function() {
      $.post('{{ route("salesdaybookfetch") }}', {
         fromdate: $('#fromdate').val(),
         todate: $('#todate').val(),
         _token: '{{ csrf_token() }}'
      }, function(res) {
         var html = '';
         $.each(res.data, function(i, r) {
            html += '<tr>';
            html += '<td>' + (r.saledate || '') + '</td>';
            html += '<td>' + (r.billno || '') + '</td>';
            html += '<td>' + (r.outlet || '') + '</td>';
            html += '<td>' + (r.roomno || '') + '</td>';
            html += '<td>' + (r.guestname || '') + '</td>';
            html += '<td>' + (r.pMode || '') + '</td>';
            html += '<td class="text-right">₹' + fmt(r.grossamt) + '</td>';
            html += '<td class="text-right">₹' + fmt(r.taxamt) + '</td>';
            html += '<td class="text-right">₹' + fmt(r.discamt) + '</td>';
            html += '<td class="text-right">₹' + fmt(r.netamt) + '</td>';
            html += '<td>' + (r.user || '') + '</td>';
            html += '</tr>';
         });
         $('#dataBody').html(html);
         $('#totalGross').text('₹' + fmt(res.data.reduce((s,r) => s + Number(r.grossamt||0), 0)));
         $('#totalTax').text('₹' + fmt(res.data.reduce((s,r) => s + Number(r.taxamt||0), 0)));
         $('#totalDisc').text('₹' + fmt(res.data.reduce((s,r) => s + Number(r.discamt||0), 0)));
         $('#totalNet').text('₹' + fmt(res.total));
      });
   });

   $('#exportBtn').click(function() {
      var csv = '';
      $('#dataTable thead tr th').each(function() { csv += '"' + $(this).text() + '",' }); csv += '\n';
      $('#dataTable tbody tr').each(function() {
         $(this).find('td').each(function() { csv += '"' + $(this).text().trim() + '",' }); csv += '\n';
      });
      var blob = new Blob([csv], {type: 'text/csv'});
      var a = document.createElement('a'); a.href = URL.createObjectURL(blob);
      a.download = 'sales_day_book_' + $('#fromdate').val() + '_' + $('#todate').val() + '.csv'; a.click();
   });

   $('#fetchBtn').click();
});
</script>
@endsection
