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

    <style>
        .metric-card { text-align: center; padding: 12px; border-radius: 8px; margin-bottom: 10px; }
        .metric-card h3 { margin: 0; font-size: 24px; }
        .metric-card p { margin: 2px 0 0; font-size: 11px; color: #666; }
        .bg-exp { background: #e3f2fd; color: #1565c0; }
        .bg-act { background: #e8f5e9; color: #2e7d32; }
        .bg-var { background: #fff3e0; color: #e65100; }
        .bg-flag { background: #ffebee; color: #c62828; }
        .text-danger { color: #c62828 !important; font-weight: 700; }
        .text-success { color: #2e7d32 !important; font-weight: 700; }
    </style>

    <div class="content-body">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Room Rent Audit Report</h4>
                            <p class="text-muted mb-3">Financial audit — compares posted room charges (RC/REV) against expected rent (rate × nights). Flags variances for review.</p>

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

                            <div class="row mb-4" id="summaryCards" style="display:none">
                                <div class="col-md-3"><div class="metric-card bg-exp"><h3 id="sExp">₹0</h3><p>Expected Total</p></div></div>
                                <div class="col-md-3"><div class="metric-card bg-act"><h3 id="sAct">₹0</h3><p>Actual Posted</p></div></div>
                                <div class="col-md-3"><div class="metric-card bg-var"><h3 id="sVar">₹0</h3><p>Total Variance</p></div></div>
                                <div class="col-md-3"><div class="metric-card bg-flag"><h3 id="sFlag">0</h3><p>Rooms Flagged</p></div></div>
                            </div>

                            <div class="table-responsive">
                                <table id="auditTable" class="table table-bordered table-sm" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Room</th>
                                            <th>Guest</th>
                                            <th>Room Type</th>
                                            <th class="text-right">Rate</th>
                                            <th class="text-right">Nights</th>
                                            <th class="text-right">Expected (₹)</th>
                                            <th class="text-right">Actual RC (₹)</th>
                                            <th class="text-right">Variance (₹)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr class="font-weight-bold">
                                            <td colspan="5">TOTAL</td>
                                            <td class="text-right" id="fExp">0.00</td>
                                            <td class="text-right" id="fAct">0.00</td>
                                            <td class="text-right" id="fVar">0.00</td>
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
    function fetchData(){
        $.ajax({
            url:'{{ route("roomrentauditfetch") }}',type:'POST',
            data:{_token:'{{ csrf_token() }}',fromdate:$('#fromdate').val(),todate:$('#todate').val()},
            success:function(r){
                $('#summaryCards').show();
                $('#sExp').text('₹'+fmt(r.summary.totalExpected));
                $('#sAct').text('₹'+fmt(r.summary.totalActual));
                var v=r.summary.variance; $('#sVar').text((v>=0?'+':'')+fmt(v));
                if(v<0){$('#sVar').css('color','#c62828');}else if(v>0){$('#sVar').css('color','#2e7d32');}else{$('#sVar').css('color','#333');}
                $('#sFlag').text(r.summary.flagged);

                dt.clear();
                (r.data||[]).forEach(function(row){
                    var cls=row.Variance>0.01?'text-danger':row.Variance<-0.01?'text-danger':'text-success';
                    dt.row.add([
                        row.RoomNo,row.GuestName||'-',row.RoomType||'-',
                        fmt(row.Rate),row.Nights,
                        fmt(row.Expected),fmt(row.ActualRC),
                        '<span class="'+cls+'">'+(row.Variance>=0?'+':'')+fmt(row.Variance)+'</span>',
                        row.Flag
                    ]).draw(false);
                });
                dt.draw();
                $('#fExp').text(fmt(r.summary.totalExpected));
                $('#fAct').text(fmt(r.summary.totalActual));
                var tv=r.summary.variance; $('#fVar').text((tv>=0?'+':'')+fmt(tv));
            },
            error:function(xhr){alert('Error: '+(xhr.responseText||'Unknown'));}
        });
    }

    $(function(){
        dt=$('#auditTable').DataTable({
            dom:'Bfrtip',
            buttons:[{extend:'excel',text:'📥 Excel',filename:'room-rent-audit'},{extend:'print',text:'🖨️ Print'}],
            pageLength:50, order:[[7,'desc']]
        });
    });
    </script>
@endsection
