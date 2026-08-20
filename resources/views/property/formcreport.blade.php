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
                            <h4 class="card-title">Form C — Foreign Guest Registration</h4>
                            <p class="text-muted mb-3">Compliance report for foreign nationals (passport holders). Required under the Foreigners Act, 1946.</p>

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

                            <div class="mb-2">
                                <span class="badge bg-info text-white p-2" id="guestCount">0 foreign guests</span>
                            </div>

                            <div class="table-responsive">
                                <table id="formCTable" class="table table-bordered table-sm" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Room</th>
                                            <th>Guest Name</th>
                                            <th>Sex</th>
                                            <th>Nationality</th>
                                            <th>Country</th>
                                            <th>ID Type</th>
                                            <th>Passport No</th>
                                            <th>Visa No</th>
                                            <th>Visa Date</th>
                                            <th>Mobile</th>
                                            <th>Company</th>
                                            <th>Check-In</th>
                                            <th>Departure</th>
                                            <th>Check-Out</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
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
            url:'{{ route("formcreportfetch") }}', type:'POST',
            data:{_token:'{{ csrf_token() }}', fromdate:$('#fromdate').val(), todate:$('#todate').val()},
            success:function(r){
                $('#guestCount').text(r.summary.total+' foreign guests');
                dt.clear();
                (r.data||[]).forEach(function(g){
                    dt.row.add([
                        g.RoomNo, g.GuestName, g.Sex, g.Nationality, g.Country,
                        g.IDType, g.PassportNo, g.VisaNo, g.VisaDate, g.Mobile,
                        g.Company, g.CheckIn?g.CheckIn.substring(0,10):'',
                        g.Departure?g.Departure.substring(0,10):'',
                        g.CheckOut?g.CheckOut.substring(0,10):'-'
                    ]).draw(false);
                });
                dt.draw();
            },
            error:function(xhr){ alert('Error: '+(xhr.responseText||'Unknown')); }
        });
    }

    $(function(){
        dt=$('#formCTable').DataTable({
            dom:'Bfrtip',
            buttons:[{extend:'excel',text:'📥 Excel',filename:'form-c-foreign-guests'},{extend:'print',text:'🖨️ Print'}],
            pageLength:50, order:[[11,'asc']]
        });
    });
    </script>
@endsection
