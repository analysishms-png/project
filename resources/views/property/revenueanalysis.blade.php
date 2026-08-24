@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid">
   <div class="card">
      <div class="card-header"><h4 class="card-title"><i class="fas fa-chart-bar"></i> Revenue Analysis</h4></div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ $fromdate }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ $fromdate }}"></div>
            <div class="col-md-2"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <div class="row mb-3" id="summaryCards" style="display:none;">
            <div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-bed"></i></span><div class="info-box-content"><span class="info-box-text">FO Revenue</span><span class="info-box-number" id="totalFO">₹0</span></div></div></div>
            <div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-utensils"></i></span><div class="info-box-content"><span class="info-box-text">POS Revenue</span><span class="info-box-number" id="totalPOS">₹0</span></div></div></div>
            <div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-calculator"></i></span><div class="info-box-content"><span class="info-box-text">Accounting</span><span class="info-box-number" id="totalAcc">₹0</span></div></div></div>
            <div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-danger"><i class="fas fa-rupee-sign"></i></span><div class="info-box-content"><span class="info-box-text">Grand Total</span><span class="info-box-number" id="grandTotal">₹0</span></div></div></div>
         </div>

         {{-- FO Revenue by VType --}}
         <div class="card mb-3" id="foSection" style="display:none;">
            <div class="card-header p-2"><h6 class="mb-0"><i class="fas fa-bed"></i> Front Office Revenue by Type</h6></div>
            <div class="card-body p-0">
               <table class="table table-sm table-bordered table-striped mb-0">
                  <thead class="thead-dark"><tr><th>VType</th><th>Revenue Name</th><th class="text-center">Transactions</th><th class="text-right">Amount</th></tr></thead>
                  <tbody id="foBody"></tbody>
                  <tfoot id="foFoot" style="display:none;"><tr class="font-weight-bold"><td colspan="2">TOTAL</td><td id="foTxn" class="text-center">0</td><td id="foAmt" class="text-right">0</td></tr></tfoot>
               </table>
            </div>
         </div>

         {{-- POS Revenue by Outlet --}}
         <div class="card mb-3" id="posSection" style="display:none;">
            <div class="card-header p-2"><h6 class="mb-0"><i class="fas fa-utensils"></i> POS Revenue by Outlet</h6></div>
            <div class="card-body p-0">
               <table class="table table-sm table-bordered table-striped mb-0">
                  <thead class="thead-dark"><tr><th>Outlet</th><th class="text-center">Bills</th><th class="text-right">Amount</th></tr></thead>
                  <tbody id="posBody"></tbody>
                  <tfoot id="posFoot" style="display:none;"><tr class="font-weight-bold"><td>TOTAL</td><td id="posBills" class="text-center">0</td><td id="posAmt" class="text-right">0</td></tr></tfoot>
               </table>
            </div>
         </div>

         {{-- Accounting Revenue by VType --}}
         <div class="card mb-3" id="accSection" style="display:none;">
            <div class="card-header p-2"><h6 class="mb-0"><i class="fas fa-calculator"></i> Accounting Revenue by VType</h6></div>
            <div class="card-body p-0">
               <table class="table table-sm table-bordered table-striped mb-0">
                  <thead class="thead-dark"><tr><th>VType</th><th class="text-center">Transactions</th><th class="text-right">Amount</th></tr></thead>
                  <tbody id="accBody"></tbody>
                  <tfoot id="accFoot" style="display:none;"><tr class="font-weight-bold"><td>TOTAL</td><td id="accTxn" class="text-center">0</td><td id="accAmt" class="text-right">0</td></tr></tfoot>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   $('#fetchBtn').click(function() {
      $.post('{{ route("revenueanalysisfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val()}, function(res) {
         // Summary cards
         $('#totalFO').text('₹'+fmt(res.totalFO));
         $('#totalPOS').text('₹'+fmt(res.totalPOS));
         $('#totalAcc').text('₹'+fmt(res.totalAcc));
         $('#grandTotal').text('₹'+fmt(res.grandTotal));
         $('#summaryCards').show();

         // FO Revenue
         if(res.foRevenue.length > 0) {
            var h='';var txn=0;
            $.each(res.foRevenue,function(i,r){h+='<tr><td><b>'+r.vtype+'</b></td><td>'+r.RevName+'</td><td class="text-center">'+r.TxnCount+'</td><td class="text-right">'+fmt(r.Amount)+'</td></tr>';txn+=r.TxnCount;});
            $('#foBody').html(h);$('#foTxn').text(txn);$('#foAmt').text(fmt(res.totalFO));$('#foFoot').show();$('#foSection').show();
         }

         // POS Revenue
         if(res.posRevenue.length > 0) {
            var h='';var bills=0;
            $.each(res.posRevenue,function(i,r){h+='<tr><td><b>'+r.OutletName+'</b></td><td class="text-center">'+r.BillCount+'</td><td class="text-right">'+fmt(r.Amount)+'</td></tr>';bills+=r.BillCount;});
            $('#posBody').html(h);$('#posBills').text(bills);$('#posAmt').text(fmt(res.totalPOS));$('#posFoot').show();$('#posSection').show();
         }

         // Accounting Revenue
         if(res.accRevenue.length > 0) {
            var h='';var txn=0;
            $.each(res.accRevenue,function(i,r){h+='<tr><td><b>'+r.vtype+'</b></td><td class="text-center">'+r.TxnCount+'</td><td class="text-right">'+fmt(r.Amount)+'</td></tr>';txn+=r.TxnCount;});
            $('#accBody').html(h);$('#accTxn').text(txn);$('#accAmt').text(fmt(res.totalAcc));$('#accFoot').show();$('#accSection').show();
         }
      });
   });
   $('#fetchBtn').click();
});
</script>
@endsection
