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
                            <h4 class="card-title">Room-Wise Room Revenue</h4>
                            <p class="text-muted mb-3">Revenue breakdown by room — room charges, POS charges, tax, discount, net amount.</p>

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
                                <table id="revTable" class="table table-bordered table-sm" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Room</th>
                                            <th>Guest</th>
                                            <th>Room Type</th>
                                            <th class="text-right">Room Charge (₹)</th>
                                            <th class="text-right">POS Charge (₹)</th>
                                            <th class="text-right">Tax (₹)</th>
                                            <th class="text-right">Discount (₹)</th>
                                            <th class="text-right">Net Amount (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr class="font-weight-bold">
                                            <td colspan="3">TOTAL</td>
                                            <td class="text-right" id="fRC">0.00</td>
                                            <td class="text-right" id="fPC">0.00</td>
                                            <td class="text-right" id="fTax">0.00</td>
                                            <td class="text-right" id="fDisc">0.00</td>
                                            <td class="text-right" id="fNet">0.00</td>
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
    function fetchData(){
        $.ajax({
            url:'{{ route("roomwiseroomrevenuefetch") }}', type:'POST',
            data:{_token:'{{ csrf_token() }}', fromdate:$('#fromdate').val(), todate:$('#todate').val()},
            success:function(r){
                dt.clear();
                (r.data||[]).forEach(function(row){
                    dt.row.add([
                        row.RoomNo, row.GuestName||'-', row.RoomType||'-',
                        fmt(row.RoomCharge), fmt(row.POSCharge), fmt(row.Tax),
                        fmt(row.Discount), fmt(row.NetAmount)
                    ]).draw(false);
                });
                dt.draw();
                var s=r.summary||{};
                $('#fRC').text(fmt(s.totalRoom)); $('#fPC').text(fmt(s.totalPos));
                $('#fTax').text(fmt(s.totalTax)); $('#fDisc').text(fmt(s.totalDisc));
                $('#fNet').text(fmt(s.totalNet));
            },
            error:function(xhr){ alert('Error: '+(xhr.responseText||'Unknown')); }
        });
    }

    $(function(){
        dt=$('#revTable').DataTable({
            dom:'Bfrtip',
            buttons:[{extend:'excel',text:'📥 Excel',filename:'room-wise-revenue'},{extend:'print',text:'🖨️ Print'}],
            pageLength:50, order:[[7,'desc']]
        });
    });
    </script>
@endsection
