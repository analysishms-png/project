@extends('property.layouts.main')
@section('main-container')
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <style>
        .metric-card { text-align: center; padding: 12px; border-radius: 8px; margin-bottom: 10px; }
        .metric-card h3 { margin: 0; font-size: 24px; }
        .metric-card p { margin: 2px 0 0; font-size: 11px; color: #666; }
        .bg-arr { background: #e8f5e9; color: #2e7d32; }
        .bg-inh { background: #e3f2fd; color: #1565c0; }
        .bg-dep { background: #fff3e0; color: #e65100; }
        .bg-can { background: #ffebee; color: #c62828; }
        .bg-nos { background: #fce4ec; color: #880e4f; }
        .section-title { font-size: 14px; font-weight: 600; margin: 15px 0 8px; border-bottom: 2px solid #4472C4; padding-bottom: 4px; }
    </style>

    <div class="content-body">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Reservation Status Dashboard</h4>
                            <p class="text-muted mb-3">Today's reservation overview — arrivals, in-house, departures, cancellations, no-shows.</p>

                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <label>Date</label>
                                    <input type="date" id="fordate" class="form-control" value="{{ $fordate }}">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button class="btn btn-primary" onclick="fetchData()">Fetch</button>
                                </div>
                            </div>

                            <div class="row mb-4" id="summaryCards" style="display:none">
                                <div class="col"><div class="metric-card bg-arr"><h3 id="sArr">0</h3><p>Arrivals</p></div></div>
                                <div class="col"><div class="metric-card bg-inh"><h3 id="sInh">0</h3><p>In-House</p></div></div>
                                <div class="col"><div class="metric-card bg-dep"><h3 id="sDep">0</h3><p>Departures</p></div></div>
                                <div class="col"><div class="metric-card bg-can"><h3 id="sCan">0</h3><p>Cancellations</p></div></div>
                                <div class="col"><div class="metric-card bg-nos"><h3 id="sNos">0</h3><p>No-Shows</p></div></div>
                            </div>

                            {{-- Arrivals --}}
                            <div class="section-title">Expected Arrivals</div>
                            <table id="tArr" class="table table-bordered table-sm" style="width:100%"><thead><tr><th>Room</th><th>Guest</th><th>Company</th><th>Adults</th><th>Child</th><th>Arrival</th><th>Departure</th><th>Status</th></tr></thead><tbody></tbody></table>

                            {{-- In-House --}}
                            <div class="section-title">In-House Guests</div>
                            <table id="tInh" class="table table-bordered table-sm" style="width:100%"><thead><tr><th>Room</th><th>Guest</th><th>Company</th><th>Room Type</th><th>Rate</th><th>Check-In</th><th>Departure</th></tr></thead><tbody></tbody></table>

                            {{-- Departures --}}
                            <div class="section-title">Expected Departures</div>
                            <table id="tDep" class="table table-bordered table-sm" style="width:100%"><thead><tr><th>Room</th><th>Guest</th><th>Rate</th><th>Departure</th><th>Checked Out</th></tr></thead><tbody></tbody></table>

                            {{-- Cancellations --}}
                            <div class="section-title">Cancellations Today</div>
                            <table id="tCan" class="table table-bordered table-sm" style="width:100%"><thead><tr><th>Room</th><th>Guest</th><th>Company</th><th>Arrival</th><th>Departure</th><th>Cancelled By</th></tr></thead><tbody></tbody></table>

                            {{-- No-Shows --}}
                            <div class="section-title">No-Shows Today</div>
                            <table id="tNos" class="table table-bordered table-sm" style="width:100%"><thead><tr><th>Room</th><th>Guest</th><th>Company</th><th>Arrival</th><th>Departure</th></tr></thead><tbody></tbody></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    var tA,tI,tD,tC,tN;
    function fetchData(){
        $.ajax({
            url:'{{ route("reservationstatusfetch") }}',type:'POST',
            data:{_token:'{{ csrf_token() }}',fordate:$('#fordate').val()},
            success:function(r){
                $('#summaryCards').show();
                $('#sArr').text(r.summary.arrivals);$('#sInh').text(r.summary.inhouse);
                $('#sDep').text(r.summary.departures);$('#sCan').text(r.summary.cancellations);
                $('#sNos').text(r.summary.noshow);

                tA.clear();(r.arrivals||[]).forEach(function(a){tA.row.add([a.RoomNo,a.GuestName||'-',a.CompanyName||'-',a.NoOfAdults||0,a.NoOfChild||0,a.ArrDate,a.DepDate,a.ResStatus||'']).draw(false);});tA.draw();
                tI.clear();(r.inhouse||[]).forEach(function(g){tI.row.add([g.roomno,g.GuestName||'-',g.Company||'-',g.RoomType||'-',parseFloat(g.roomrate||0).toLocaleString('en-IN'),g.chkindate?g.chkindate.substring(0,10):'',g.depdate?g.depdate.substring(0,10):'']).draw(false);});tI.draw();
                tD.clear();(r.departures||[]).forEach(function(d){tD.row.add([d.roomno,d.GuestName||'-',parseFloat(d.roomrate||0).toLocaleString('en-IN'),d.depdate?d.depdate.substring(0,10):'',d.CheckOut||'-']).draw(false);});tD.draw();
                tC.clear();(r.cancellations||[]).forEach(function(c){tC.row.add([c.RoomNo,c.GuestName||'-',c.CompanyName||'-',c.ArrDate,c.DepDate,c.CancelUName||'-']).draw(false);});tC.draw();
                tN.clear();(r.noshow||[]).forEach(function(n){tN.row.add([n.RoomNo,n.GuestName||'-',n.CompanyName||'-',n.ArrDate,n.DepDate]).draw(false);});tN.draw();
            },
            error:function(xhr){alert('Error: '+(xhr.responseText||'Unknown'));}
        });
    }
    $(function(){
        tA=$('#tArr').DataTable({dom:'Bfrtip',buttons:[],paging:false,searching:false});
        tI=$('#tInh').DataTable({dom:'Bfrtip',buttons:[],paging:false,searching:false});
        tD=$('#tDep').DataTable({dom:'Bfrtip',buttons:[],paging:false,searching:false});
        tC=$('#tCan').DataTable({dom:'Bfrtip',buttons:[],paging:false,searching:false});
        tN=$('#tNos').DataTable({dom:'Bfrtip',buttons:[],paging:false,searching:false});
    });
    </script>
@endsection
