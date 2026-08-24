@extends('property.layouts.main')
@section('main-container')
<link href="{{ asset('admin/css/dashboard-modern.css') }}" rel="stylesheet">
<div class="content-body">
    <div class="container-fluid" style="margin-top:90px;">
        <div class="dash-title-bar">
            <div class="title-left">
                <h3>Party Outstanding Report</h3>
                <p>Banquet party wise outstanding amounts</p>
            </div>
            <div class="title-right">
                <button onclick="window.print()" class="dash-btn-icon"><i class="fa fa-print"></i></button>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label>From Date</label>
                        <input type="date" id="fromdate" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label>To Date</label>
                        <input type="date" id="todate" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button id="fetchBtn" class="btn btn-primary"><i class="fa fa-search"></i> Fetch</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-sm" id="resultTable">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>Sr</th>
                            <th>Party Name</th>
                            <th>Function</th>
                            <th>Bill No</th>
                            <th>Bill Date</th>
                            <th>Amount</th>
                            <th>Advance</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                    <tfoot id="tableFoot" style="display:none;">
                        <tr class="font-weight-bold">
                            <td colspan="5">Total</td>
                            <td id="totalAmount" class="text-right">₹0.00</td>
                            <td id="totalAdvance" class="text-right">₹0.00</td>
                            <td id="totalBalance" class="text-right">₹0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    $('#fetchBtn').click(function() {
        $.post('{{ route("partyoutstandingfetch") }}', {
            fromdate: $('#fromdate').val(),
            todate: $('#todate').val()
        }, function(res) {
            var h = '', totAmt = 0, totAdv = 0, totBal = 0;
            $.each(res, function(i, r) {
                h += '<tr><td>' + (i+1) + '</td><td>' + (r.PartyName||'') + '</td><td>' + (r.FuncName||'') + '</td><td>' + (r.BillNo||'') + '</td><td>' + (r.BillDate||'') + '</td><td class="text-right">₹' + fmt(r.Amount) + '</td><td class="text-right">₹' + fmt(r.Advance) + '</td><td class="text-right">₹' + fmt(r.Balance) + '</td></tr>';
                totAmt += Number(r.Amount || 0);
                totAdv += Number(r.Advance || 0);
                totBal += Number(r.Balance || 0);
            });
            $('#tableBody').html(h);
            $('#totalAmount').text('₹' + fmt(totAmt));
            $('#totalAdvance').text('₹' + fmt(totAdv));
            $('#totalBalance').text('₹' + fmt(totBal));
            $('#tableFoot').show();
        });
    });
    $('#fetchBtn').click();
});
</script>
@endsection
