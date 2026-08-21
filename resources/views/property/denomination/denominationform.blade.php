@extends('property.layouts.main')

@section('content')
<div class="content-body">
   <div class="page-header mb-3">
      <div class="row align-items-center">
         <div class="col">
            <h3 class="page-title">New Denomination Entry</h3>
         </div>
      </div>
   </div>

   <div class="row">
      <div class="col-12">
         <div class="card">
            <div class="card-body">
               <form id="denominationForm">
                  @csrf
                  <div class="row mb-3">
                     <div class="col-md-4">
                        <label class="form-label">Serial No</label>
                        <input type="text" class="form-control" value="{{ $nextSno }}" readonly>
                     </div>
                     <div class="col-md-4">
                        <label class="form-label">Date *</label>
                        <input type="date" class="form-control" name="vdate" value="{{ date('Y-m-d') }}" required>
                     </div>
                     <div class="col-md-4">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-control" name="name" required>
                     </div>
                  </div>

                  <div class="table-responsive">
                     <table class="table table-bordered" id="itemsTable">
                        <thead>
                           <tr>
                              <th>#</th>
                              <th>Denomination Type *</th>
                              <th>Value *</th>
                              <th>Unit *</th>
                              <th>Total</th>
                              <th>Action</th>
                           </tr>
                        </thead>
                        <tbody>
                           <tr>
                              <td>1</td>
                              <td>
                                 <select class="form-control denomination-type" required>
                                    <option value="">Select Type</option>
                                    <option value="2000">₹2000 Note</option>
                                    <option value="500">₹500 Note</option>
                                    <option value="200">₹200 Note</option>
                                    <option value="100">₹100 Note</option>
                                    <option value="50">₹50 Note</option>
                                    <option value="20">₹20 Note</option>
                                    <option value="10">₹10 Note</option>
                                    <option value="5">₹5 Coin</option>
                                    <option value="2">₹2 Coin</option>
                                    <option value="1">₹1 Coin</option>
                                 </select>
                              </td>
                              <td><input type="number" class="form-control denomination-value" step="0.01" required></td>
                              <td><input type="number" class="form-control denomination-unit" value="1" required></td>
                              <td class="total-cell">₹0.00</td>
                              <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="fa fa-trash"></i></button></td>
                           </tr>
                        </tbody>
                        <tfoot>
                           <tr>
                              <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
                              <td id="grandTotal">₹0.00</td>
                              <td></td>
                           </tr>
                        </tfoot>
                     </table>
                  </div>

                  <button type="button" class="btn btn-success mb-3" onclick="addRow()">
                     <i class="fa fa-plus"></i> Add Row
                  </button>

                  <div class="text-end">
                     <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Entry
                     </button>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>
</div>
@endsection

@section('scripts')
<script>
function addRow() {
   var tbody = $('#itemsTable tbody');
   var rowCount = tbody.find('tr').length + 1;
   var row = '<tr>' +
      '<td>' + rowCount + '</td>' +
      '<td><select class="form-control denomination-type" required><option value="">Select Type</option><option value="2000">₹2000 Note</option><option value="500">₹500 Note</option><option value="200">₹200 Note</option><option value="100">₹100 Note</option><option value="50">₹50 Note</option><option value="20">₹20 Note</option><option value="10">₹10 Note</option><option value="5">₹5 Coin</option><option value="2">₹2 Coin</option><option value="1">₹1 Coin</option></select></td>' +
      '<td><input type="number" class="form-control denomination-value" step="0.01" required></td>' +
      '<td><input type="number" class="form-control denomination-unit" value="1" required></td>' +
      '<td class="total-cell">₹0.00</td>' +
      '<td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="fa fa-trash"></i></button></td>' +
      '</tr>';
   tbody.append(row);
   bindEvents();
}

function removeRow(btn) {
   if ($('#itemsTable tbody tr').length > 1) {
      $(btn).closest('tr').remove();
      renumberRows();
      calculateGrandTotal();
   }
}

function renumberRows() {
   $('#itemsTable tbody tr').each(function(idx) {
      $(this).find('td:first').text(idx + 1);
   });
}

function calculateGrandTotal() {
   var total = 0;
   $('#itemsTable tbody tr').each(function() {
      var value = parseFloat($(this).find('.denomination-value').val()) || 0;
      var unit = parseInt($(this).find('.denomination-unit').val()) || 0;
      var rowTotal = value * unit;
      $(this).find('.total-cell').text('₹' + rowTotal.toLocaleString('en-IN', {minimumFractionDigits: 2}));
      total += rowTotal;
   });
   $('#grandTotal').text('₹' + total.toLocaleString('en-IN', {minimumFractionDigits: 2}));
}

function bindEvents() {
   $('.denomination-value, .denomination-unit').off('input').on('input', function() {
      calculateGrandTotal();
   });
}

$(document).ready(function() {
   bindEvents();

   $('#denominationForm').on('submit', function(e) {
      e.preventDefault();

      var items = [];
      $('#itemsTable tbody tr').each(function() {
         items.push({
            denominationtype: $(this).find('.denomination-type').val(),
            denominationvalue: parseFloat($(this).find('.denomination-value').val()) || 0,
            denominationunit: parseInt($(this).find('.denomination-unit').val()) || 0,
            denominationtotal: parseFloat($(this).find('.total-cell').text().replace(/[₹,]/g, '')) || 0,
         });
      });

      var formData = {
         _token: $('input[name="_token"]').val(),
         vdate: $('input[name="vdate"]').val(),
         name: $('input[name="name"]').val(),
         items: items,
      };

      $.post('/denomination/store', formData, function(res) {
         if (res.success) {
            alert(res.message);
            window.location.href = '/denomination';
         } else {
            alert(res.message);
         }
      }).fail(function(xhr) {
         alert('Error: ' + xhr.responseText);
      });
   });
});
</script>
@endsection
