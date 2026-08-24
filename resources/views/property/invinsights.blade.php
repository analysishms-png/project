@extends('property.layouts.main')

@section('main-container')
    <div class="content-body">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('invdashboard') }}" class="btn btn-secondary btn-sm">&#8592; Back</a>
                    <h4 class="mb-0">Inventory Insights</h4>
                    <span id="insUpdated" class="text-muted small"></span>
                </div>
                <button type="button" id="insRefresh" class="btn btn-primary btn-sm">
                    <i class="fa fa-refresh"></i> Refresh
                </button>
            </div>

            <div class="row g-3">

                {{-- Pending Indents --}}
                <div class="col-xl-6 col-lg-6 col-md-12" id="pendingIndents">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Pending Indents <span id="piCount" class="badge bg-warning text-dark ms-1">0</span></h5>
                            <span class="text-muted small">not yet picked by a PO</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height:340px;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th><th>Indent No</th><th>Date</th><th>Department</th><th class="text-end">Items</th>
                                        </tr>
                                    </thead>
                                    <tbody id="piBody">
                                        <tr><td colspan="5" class="text-center text-muted py-3">Loading...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pending Purchase Orders --}}
                <div class="col-xl-6 col-lg-6 col-md-12" id="pendingPOs">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Pending Purchase Orders <span id="ppCount" class="badge bg-warning text-dark ms-1">0</span></h5>
                            <span class="text-muted small">no material received yet</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height:340px;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th><th>PO No</th><th>Date</th><th>Supplier</th><th>Expected</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ppBody">
                                        <tr><td colspan="5" class="text-center text-muted py-3">Loading...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Supplier Wise Purchase --}}
                <div class="col-xl-6 col-lg-6 col-md-12" id="supplierWise">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Supplier Wise Purchase <span class="text-muted small">(last 12 months)</span></h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height:340px;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th><th>Supplier</th><th class="text-end">Bills</th><th class="text-end">Net Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="swBody">
                                        <tr><td colspan="4" class="text-center text-muted py-3">Loading...</td></tr>
                                    </tbody>
                                    <tfoot class="table-light fw-semibold" id="swFoot"></tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Purchase Trend --}}
                <div class="col-xl-6 col-lg-6 col-md-12" id="trend">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Purchase Trend <span class="text-muted small">(last 6 months)</span></h5>
                        </div>
                        <div class="card-body" id="trBody">
                            <p class="text-muted text-center py-3">Loading...</p>
                        </div>
                    </div>
                </div>

                {{-- Minus Stock --}}
                <div class="col-xl-12" id="minusStock">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Minus Stock <span id="msCount" class="badge bg-danger ms-1">0</span></h5>
                            <span class="text-muted small">store items with negative balance</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height:340px;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th><th>Item</th><th>Godown</th><th class="text-end">Balance Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody id="msBody">
                                        <tr><td colspan="4" class="text-center text-muted py-3">Loading...</td></tr>
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
        function insEsc(v) {
            return $('<div>').text(v == null ? '' : String(v)).html();
        }

        function loadInsights() {
            $.getJSON('{{ url("invinsights/data") }}', function (d) {
                var i;

                $('#piCount').text(d.pendingIndents.length);
                if (d.pendingIndents.length) {
                    $('#piBody').empty();
                    for (i = 0; i < d.pendingIndents.length; i++) {
                        var pi = d.pendingIndents[i];
                        $('#piBody').append('<tr>' +
                            '<td>' + (i + 1) + '</td>' +
                            '<td>' + insEsc(pi.docid) + '</td>' +
                            '<td>' + insEsc(pi.vdate) + '</td>' +
                            '<td>' + insEsc(pi.department_name || pi.department || '-') + '</td>' +
                            '<td class="text-end">' + pi.itemcount + '</td></tr>');
                    }
                } else {
                    $('#piBody').html('<tr><td colspan="5" class="text-center text-muted py-3">No pending indents</td></tr>');
                }

                $('#ppCount').text(d.pendingPOs.length);
                if (d.pendingPOs.length) {
                    $('#ppBody').empty();
                    for (i = 0; i < d.pendingPOs.length; i++) {
                        var pp = d.pendingPOs[i];
                        $('#ppBody').append('<tr>' +
                            '<td>' + (i + 1) + '</td>' +
                            '<td>' + insEsc(pp.vno) + '</td>' +
                            '<td>' + insEsc(pp.vdate) + '</td>' +
                            '<td>' + insEsc(pp.supplier || '-') + '</td>' +
                            '<td>' + insEsc(pp.exp_delivery || '-') + '</td></tr>');
                    }
                } else {
                    $('#ppBody').html('<tr><td colspan="5" class="text-center text-muted py-3">No pending purchase orders</td></tr>');
                }

                var swTotal = 0;
                $('#swBody').empty();
                if (d.supplierWise.length) {
                    for (i = 0; i < d.supplierWise.length; i++) {
                        var sw = d.supplierWise[i];
                        swTotal += parseFloat(sw.netamt || 0);
                        $('#swBody').append('<tr>' +
                            '<td>' + (i + 1) + '</td>' +
                            '<td>' + insEsc(sw.supplier || sw.Party || '-') + '</td>' +
                            '<td class="text-end">' + sw.bills + '</td>' +
                            '<td class="text-end">' + fmt(sw.netamt) + '</td></tr>');
                    }
                } else {
                    $('#swBody').html('<tr><td colspan="4" class="text-center text-muted py-3">No purchases in the last year</td></tr>');
                }
                $('#swFoot').html('<tr><td colspan="2">Total</td><td class="text-end">' +
                    d.supplierWise.reduce(function (a, r) { return a + parseInt(r.bills || 0); }, 0) +
                    '</td><td class="text-end">' + fmt(swTotal) + '</td></tr>');

                if (d.trend.length) {
                    var max = Math.max.apply(null, d.trend.map(function (r) { return parseFloat(r.netamt || 0); }));
                    var html = '';
                    for (i = 0; i < d.trend.length; i++) {
                        var t = d.trend[i];
                        var pct = max > 0 ? Math.round(parseFloat(t.netamt) / max * 100) : 0;
                        html += '<div class="mb-2">' +
                            '<div class="d-flex justify-content-between small"><span>' + insEsc(t.ym) +
                            '</span><span>' + fmt(t.netamt) + '</span></div>' +
                            '<div class="progress" style="height:10px;">' +
                            '<div class="progress-bar" role="progressbar" style="width:' + pct + '%;"></div></div></div>';
                    }
                    $('#trBody').html(html);
                } else {
                    $('#trBody').html('<p class="text-muted text-center py-3">No purchases in the last 6 months</p>');
                }

                $('#msCount').text(d.minusStock.length);
                if (d.minusStock.length) {
                    $('#msBody').empty();
                    for (i = 0; i < d.minusStock.length; i++) {
                        var ms = d.minusStock[i];
                        $('#msBody').append('<tr>' +
                            '<td>' + (i + 1) + '</td>' +
                            '<td>' + insEsc(ms.item_name || ms.Item || '-') + '</td>' +
                            '<td>' + insEsc(ms.godown_name || ms.GodownCode || '-') + '</td>' +
                            '<td class="text-end text-danger">' + fmt(ms.balance) + '</td></tr>');
                    }
                } else {
                    $('#msBody').html('<tr><td colspan="4" class="text-center text-muted py-3">No minus stock - all balances healthy</td></tr>');
                }

                $('#insUpdated').text('updated ' + new Date().toLocaleTimeString());
            }).fail(function () {
                $('#insUpdated').text('load failed - press Refresh');
            });
        }

        $('#insRefresh').on('click', loadInsights);
        $(document).ready(loadInsights);
    </script>
@endsection
