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
        .bg-guest { background: #e3f2fd; color: #1565c0; }
        .bg-adult { background: #e8f5e9; color: #2e7d32; }
        .bg-child { background: #fff3e0; color: #e65100; }
        .bg-bal { background: #f3e5f5; color: #7b1fa2; }
    </style>

    <div class="content-body">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Checked-In Guest Detail</h4>
                            <p class="text-muted mb-3">All currently checked-in guests with room, company, ID, and balance information.</p>

                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <label>Date</label>
                                    <input type="date" id="fordate" class="form-control" value="{{ $fordate }}">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button class="btn btn-primary mr-2" onclick="fetchData()">Fetch</button>
                                    <button class="btn btn-success" onclick="window.print()">🖨️ Print</button>
                                </div>
                            </div>

                            <div class="row mb-4" id="summaryCards" style="display:none">
                                <div class="col-md-3"><div class="metric-card bg-guest"><h3 id="mGuests">0</h3><p>Guests</p></div></div>
                                <div class="col-md-3"><div class="metric-card bg-adult"><h3 id="mAdults">0</h3><p>Adults</p></div></div>
                                <div class="col-md-3"><div class="metric-card bg-child"><h3 id="mChild">0</h3><p>Children</p></div></div>
                                <div class="col-md-3"><div class="metric-card bg-bal"><h3 id="mBal">₹0</h3><p>Total Balance</p></div></div>
                            </div>

                            <div class="table-responsive">
                                <table id="guestTable" class="table table-bordered table-sm" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Room</th>
                                            <th>Guest</th>
                                            <th>Nationality</th>
                                            <th>ID Type</th>
                                            <th>ID No</th>
                                            <th>Mobile</th>
                                            <th>Company</th>
                                            <th>Travel Agent</th>
                                            <th>Room Type</th>
                                            <th class="text-right">Rate</th>
                                            <th>Check-In</th>
                                            <th>Departure</th>
                                            <th class="text-right">Nights</th>
                                            <th>Check-Out</th>
                                            <th class="text-right">A/C</th>
                                            <th class="text-right">Ch</th>
                                            <th>Leader</th>
                                            <th class="text-right">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr class="font-weight-bold">
                                            <td colspan="9">TOTAL</td>
                                            <td class="text-right" id="fRate">0</td>
                                            <td colspan="3"></td>
                                            <td class="text-right" id="fNights">0</td>
                                            <td></td>
                                            <td class="text-right" id="fAdults">0</td>
                                            <td class="text-right" id="fChild">0</td>
                                            <td></td>
                                            <td class="text-right" id="fBal">0.00</td>
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
            url:'{{ route("checkedinguestdetailfetch") }}', type:'POST',
            data:{_token:'{{ csrf_token() }}', fordate:$('#fordate').val()},
            success:function(r){
                $('#summaryCards').show();
                $('#mGuests').text(r.summary.totalGuests);
                $('#mAdults').text(r.summary.totalAdults);
                $('#mChild').text(r.summary.totalChildren);
                $('#mBal').text('₹'+fmt(r.summary.totalBalance));

                dt.clear();
                var tR=0,tN=0,tA=0,tC=0,tB=0;
                (r.data||[]).forEach(function(g){
                    tR+=g.Rate;tN+=g.Nights;tA+=g.Adults;tC+=g.Children;tB+=g.Balance;
                    dt.row.add([
                        g.RoomNo, g.GuestName, g.Nationality, g.IDType, g.IDNo, g.Mobile,
                        g.Company, g.TravelAgent, g.RoomType, fmt(g.Rate),
                        g.CheckIn ? g.CheckIn.substring(0,10) : '',
                        g.Departure ? g.Departure.substring(0,10) : '',
                        g.Nights,
                        g.CheckOut ? g.CheckOut.substring(0,10) : '-',
                        g.Adults, g.Children, g.Leader, fmt(g.Balance)
                    ]).draw(false);
                });
                dt.draw();
                $('#fRate').text(fmt(tR)); $('#fNights').text(tN);
                $('#fAdults').text(tA); $('#fChild').text(tC); $('#fBal').text(fmt(tB));
            },
            error:function(xhr){ alert('Error: '+(xhr.responseText||'Unknown')); }
        });
    }

    $(function(){
        dt=$('#guestTable').DataTable({
            dom:'Bfrtip',
            buttons:[{extend:'excel',text:'📥 Excel',filename:'checked-in-guests'},{extend:'print',text:'🖨️ Print'}],
            pageLength:50, order:[[0,'asc']]
        });
    });
    </script>
@endsection
