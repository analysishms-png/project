@extends('property.layouts.main')
@section('main-container')
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <style>
        .metric-card { text-align: center; padding: 15px; border-radius: 8px; margin-bottom: 10px; }
        .metric-card h3 { margin: 0; font-size: 28px; }
        .metric-card p { margin: 2px 0 0; font-size: 12px; color: #666; }
        .bg-occ { background: #e3f2fd; color: #1565c0; }
        .bg-arr { background: #e8f5e9; color: #2e7d32; }
        .bg-dep { background: #fff3e0; color: #e65100; }
        .bg-rev { background: #f3e5f5; color: #7b1fa2; }
        .section-title { font-size: 14px; font-weight: 600; margin: 15px 0 8px; border-bottom: 2px solid #4472C4; padding-bottom: 4px; }
        .occ-bar { height: 8px; border-radius: 4px; background: #e0e0e0; }
        .occ-fill { height: 100%; border-radius: 4px; background: #4472C4; }
    </style>

    <div class="content-body">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">AMR Morning Report</h4>
                            <p class="text-muted mb-3">Daily operational snapshot for front office — occupancy, arrivals, departures, revenue.</p>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label>Date</label>
                                    <input type="date" id="fordate" class="form-control" value="{{ $fordate }}">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button class="btn btn-primary" onclick="fetchData()">Fetch</button>
                                </div>
                            </div>

                            {{-- Summary Cards --}}
                            <div class="row mb-4" id="summaryCards" style="display:none">
                                <div class="col-md-3">
                                    <div class="metric-card bg-occ">
                                        <h3 id="mOccPct">0%</h3>
                                        <p>Occupancy</p>
                                        <small id="mOccDetail">0 / 0 rooms</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card bg-arr">
                                        <h3 id="mArrivals">0</h3>
                                        <p>Expected Arrivals</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card bg-dep">
                                        <h3 id="mDepartures">0</h3>
                                        <p>Expected Departures</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card bg-rev">
                                        <h3 id="mRevenue">₹0</h3>
                                        <p>Today Revenue</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Room Type Occupancy --}}
                            <div class="section-title">Room Type Occupancy</div>
                            <div class="table-responsive">
                                <table id="occTable" class="table table-bordered table-sm">
                                    <thead><tr><th>Room Type</th><th class="text-right">Total</th><th class="text-right">Occupied</th><th class="text-right">Vacant</th><th class="text-right">Occ %</th><th style="width:200px">Occupancy</th></tr></thead>
                                    <tbody></tbody>
                                    <tfoot><tr class="font-weight-bold"><td>TOTAL</td><td class="text-right" id="oTotal">0</td><td class="text-right" id="oOccupied">0</td><td class="text-right" id="oVacant">0</td><td class="text-right" id="oPct">0%</td><td></td></tr></tfoot>
                                </table>
                            </div>

                            {{-- Expected Arrivals --}}
                            <div class="section-title">Expected Arrivals ({{ $fordate }})</div>
                            <div class="table-responsive">
                                <table id="arrTable" class="table table-bordered table-sm">
                                    <thead><tr><th>Room</th><th>Guest</th><th>Company</th><th>Adults</th><th>Child</th><th>Arrival</th><th>Departure</th><th>Status</th></tr></thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            {{-- Expected Departures --}}
                            <div class="section-title">Expected Departures ({{ $fordate }})</div>
                            <div class="table-responsive">
                                <table id="depTable" class="table table-bordered table-sm">
                                    <thead><tr><th>Room</th><th>Guest</th><th>Rate</th><th>Complimentary</th></tr></thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            {{-- Revenue by VType --}}
                            <div class="section-title">Revenue by Voucher Type</div>
                            <div class="table-responsive">
                                <table id="revTable" class="table table-bordered table-sm">
                                    <thead><tr><th>Voucher Type</th><th class="text-right">Bills</th><th class="text-right">Net Amount (₹)</th></tr></thead>
                                    <tbody></tbody>
                                    <tfoot><tr class="font-weight-bold"><td>TOTAL</td><td class="text-right" id="rBills">0</td><td class="text-right" id="rNet">0.00</td></tr></tfoot>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    var ot, at, dt, rt;
    function fmt(n){ return parseFloat(n||0).toLocaleString('en-IN',{minimumFractionDigits:2,maximumFractionDigits:2}); }

    function fetchData(){
        $.ajax({
            url:'{{ route("amrmorningreportfetch") }}', type:'POST',
            data:{_token:'{{ csrf_token() }}', fordate:$('#fordate').val()},
            success:function(r){
                $('#summaryCards').show();
                $('#mOccPct').text(r.occupancy.occ_pct+'%');
                $('#mOccDetail').text(r.occupancy.occupied+' / '+r.occupancy.total+' rooms');
                $('#mArrivals').text(r.arrivals.length);
                $('#mDepartures').text(r.departures.length);
                $('#mRevenue').text('₹'+fmt(r.revenue.total));

                // Occupancy by type
                ot.clear();
                var tT=0,tO=0,tV=0;
                (r.occupancy.bytype||[]).forEach(function(row){
                    tT+=row.total;tO+=row.occupied;tV+=row.vacant;
                    var pct = row.total>0?Math.round(row.occupied/row.total*100):0;
                    ot.row.add([row.category,row.total,row.occupied,row.vacant,pct+'%','<div class="occ-bar"><div class="occ-fill" style="width:'+pct+'%"></div></div>']).draw(false);
                });
                ot.draw();
                $('#oTotal').text(tT);$('#oOccupied').text(tO);$('#oVacant').text(tV);
                $('#oPct').text(tT>0?Math.round(tO/tT*100)+'%':'0%');

                // Arrivals
                at.clear();
                (r.arrivals||[]).forEach(function(a){
                    at.row.add([a.RoomNo,a.GuestName||'-',a.CompanyName||'-',a.NoOfAdults||0,a.NoOfChild||0,a.ArrDate,a.DepDate,a.ResStatus||'']).draw(false);
                });
                at.draw();

                // Departures
                dt.clear();
                (r.departures||[]).forEach(function(d){
                    dt.row.add([d.roomno,d.GuestName||'-',fmt(d.roomrate),d.Complimentary=='Y'?'YES':'']).draw(false);
                });
                dt.draw();

                // Revenue
                rt.clear();
                var tB=0,tN=0;
                (r.revenue.bytype||[]).forEach(function(v){
                    tB+=v.bills;tN+=v.net;
                    rt.row.add([v.vtype,v.bills,fmt(v.net)]).draw(false);
                });
                rt.draw();
                $('#rBills').text(tB);$('#rNet').text(fmt(tN));
            },
            error:function(xhr){alert('Error: '+(xhr.responseText||'Unknown'));}
        });
    }

    $(function(){
        ot=$('#occTable').DataTable({dom:'Bfrtip',buttons:[],paging:false,searching:false});
        at=$('#arrTable').DataTable({dom:'Bfrtip',buttons:[],paging:false,searching:false});
        dt=$('#depTable').DataTable({dom:'Bfrtip',buttons:[],paging:false,searching:false});
        rt=$('#revTable').DataTable({dom:'Bfrtip',buttons:[],paging:false,searching:false});
    });
    </script>
@endsection
