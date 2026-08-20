@extends('property.layouts.main')
@section('main-container')
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <div class="content-body">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">FO Settlement Report</h4>
                            <p class="text-muted mb-3">Payment settlements by room with mode-wise breakdown (Cash/Room/Company/UPI/Card).</p>

                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <label>From Date</label>
                                    <input type="date" id="fromdate" class="form-control" value="{{ $fordate }}">
                                </div>
                                <div class="col-md-2">
                                    <label>To Date</label>
                                    <input type="date" id="todate" class="form-control" value="{{ $fordate }}">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button class="btn btn-primary mr-2" onclick="fetchData()">Fetch</button>
                                    <button class="btn btn-success" onclick="window.print()">🖨️ Print</button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="settleTable" class="table table-bordered table-sm" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Room</th>
                                            <th>Guest</th>
                                            <th>Settle Date</th>
                                            <th>Bill No</th>
                                            <th>Folio</th>
                                            <th class="text-right">Cash (₹)</th>
                                            <th class="text-right">Room (₹)</th>
                                            <th class="text-right">Company (₹)</th>
                                            <th class="text-right">UPI (₹)</th>
                                            <th class="text-right">Card (₹)</th>
                                            <th class="text-right">Total Paid (₹)</th>
                                            <th>Settled By</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr class="font-weight-bold">
                                            <td colspan="5">TOTAL</td>
                                            <td class="text-right" id="fCash">0.00</td>
                                            <td class="text-right" id="fRoom">0.00</td>
                                            <td class="text-right" id="fComp">0.00</td>
                                            <td class="text-right" id="fUPI">0.00</td>
                                            <td class="text-right" id="fCard">0.00</td>
                                            <td class="text-right" id="fTotal">0.00</td>
                                            <td></td>
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
    var dt;
    function fmt(n){ return parseFloat(n||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}); }

    function fetchData(){
        $.ajax({
            url:'{{ route("fosettlereportfetch") }}', type:'POST',
            data:{_token:'{{ csrf_token() }}', fromdate:$('#fromdate').val(), todate:$('#todate').val()},
            success:function(r){
                dt.clear();
                (r.data||[]).forEach(function(s){
                    dt.row.add([
                        s.RoomNo, s.GuestName||'-',
                        s.SettleDate?s.SettleDate.substring(0,10):'',
                        s.BillNo, s.FolioNo,
                        fmt(s.Cash), fmt(s.Room), fmt(s.Company), fmt(s.UPI), fmt(s.Card),
                        fmt(s.TotalPaid), s.SettledBy||'-'
                    ]).draw(false);
                });
                dt.draw();
                var sm=r.summary||{};
                $('#fCash').text(fmt(sm.totalCash)); $('#fRoom').text(fmt(sm.totalRoom));
                $('#fComp').text(fmt(sm.totalCompany)); $('#fUPI').text(fmt(sm.totalUPI));
                $('#fCard').text(fmt(sm.totalCard)); $('#fTotal').text(fmt(sm.totalPaid));
            },
            error:function(xhr){ alert('Error: '+(xhr.responseText||'Unknown')); }
        });
    }

    $(function(){
        dt=$('#settleTable').DataTable({
            dom:'Bfrtip',
            buttons:[{extend:'excel',text:'📥 Excel',filename:'fo-settlement-report'},{extend:'print',text:'🖨️ Print'}],
            pageLength:50, order:[[2,'asc']]
        });
    });
    </script>
@endsection
