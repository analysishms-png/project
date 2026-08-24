@extends('property.layouts.property')
@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h4><i class="fas fa-plane-arrival text-primary"></i> {{ $view }}</h4>
    </div>
</div>
<section class="content">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <div class="row">
                <div class="col-md-3">
                    <label>From Date</label>
                    <input type="date" id="fromdate" class="form-control form-control-sm" value="{{ $fd }}">
                </div>
                <div class="col-md-3">
                    <label>To Date</label>
                    <input type="date" id="todate" class="form-control form-control-sm" value="{{ $td }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary btn-sm mr-2" onclick="fetchData()"><i class="fas fa-search"></i> Search</button>
                    <button class="btn btn-success btn-sm mr-2" onclick="exportCSV()"><i class="fas fa-file-csv"></i> CSV</button>
                    <button class="btn btn-info btn-sm" onclick="printReport()"><i class="fas fa-print"></i> Print</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3"><strong>Arrivals:</strong> <span id="arrivalCount" class="badge badge-primary">0</span></div>
                <div class="col-md-3"><strong>Departures:</strong> <span id="departureCount" class="badge badge-warning">0</span></div>
            </div>
            <div class="table-responsive">
                <table id="dataTable" class="table table-sm table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Type</th><th>Date</th><th>Room</th><th>Category</th><th>Rate</th><th>Guest</th><th>Ref No</th><th>Days</th><th>Adults</th><th>Child</th><th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="tblBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
@section('scripts')
<script>
$(function(){
    fetchData();
    function fetchData(){
        $.post('{{ route("arrivaldepfetch") }}',{fromdate:$('#fromdate').val(),todate:$('#todate').val(),_token:'{{ csrf_token() }}'},function(res){
            var html='';
            $.each(res.data,function(i,r){
                var badge=r.type==='Arrival'?'badge-primary':'badge-warning';
                html+='<tr><td><span class="badge '+badge+'">'+r.type+'</span></td><td>'+fmt(r.vdate)+'</td><td>'+r.roomno+'</td><td>'+r.roomcategory+'</td><td>₹'+fmtN(r.roomrate)+'</td><td>'+r.guestname+'</td><td>'+r.refno+'</td><td>'+r.nodays+'</td><td>'+r.adults+'</td><td>'+r.childs+'</td><td>'+r.remarks+'</td></tr>';
            });
            $('#tblBody').html(html);
            $('#arrivalCount').text(res.arrivals);
            $('#departureCount').text(res.departures);
        });
    }
    function fmt(v){ return v||''; }
    function fmtN(v){ return Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2}); }
    function exportCSV(){
        var csv='Type,Date,Room,Category,Rate,Guest,RefNo,Days,Adults,Child,Remarks\n';
        $('#tblBody tr').each(function(){var r=[];$(this).find('td').each(function(){r.push('"'+$(this).text().replace(/"/g,'""')+'"');});csv+=r.join(',')+'\n';});
        var blob=new Blob([csv],{type:'text/csv'});
        var a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download='arrival_departure_list.csv';a.click();
    }
    function printReport(){ window.print(); }
});
</script>
@endsection