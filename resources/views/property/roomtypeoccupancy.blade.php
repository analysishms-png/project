@extends('property.layouts.property')
@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h4><i class="fas fa-hotel text-primary"></i> {{ $view }}</h4>
    </div>
</div>
<section class="content">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <div class="row">
                <div class="col-md-3"><label>From Date</label><input type="date" id="fromdate" class="form-control form-control-sm" value="{{ $fd }}"></div>
                <div class="col-md-3"><label>To Date</label><input type="date" id="todate" class="form-control form-control-sm" value="{{ $td }}"></div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary btn-sm mr-2" onclick="fetchData()"><i class="fas fa-search"></i> Search</button>
                    <button class="btn btn-success btn-sm mr-2" onclick="exportCSV()"><i class="fas fa-file-csv"></i> CSV</button>
                    <button class="btn btn-info btn-sm" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3"><strong>Total Rooms:</strong> <span class="badge badge-secondary" id="grandTotal">0</span></div>
                <div class="col-md-3"><strong>Occupied:</strong> <span class="badge badge-danger" id="grandOcc">0</span></div>
                <div class="col-md-3"><strong>Vacant:</strong> <span class="badge badge-success" id="grandVac">0</span></div>
                <div class="col-md-3"><strong>Overall Occupancy:</strong> <span class="badge badge-info" id="overallOcc">0%</span></div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <thead class="thead-dark"><tr><th>Room Type</th><th>Total Rooms</th><th>Occupied</th><th>Vacant</th><th>Occupancy %</th></tr></thead>
                    <tbody id="tblBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
@section('scripts')
<script>
$(function(){ fetchData(); });
function fetchData(){
    $.post('{{ route("roomtypeoccupancyfetch") }}',{fromdate:$('#fromdate').val(),todate:$('#todate').val(),_token:'{{ csrf_token() }}'},function(res){
        var html='';
        $.each(res.data,function(i,r){
            html+='<tr><td><strong>'+r.roomtype+'</strong></td><td>'+r.totalrooms+'</td><td>'+r.occupied+'</td><td>'+r.vacant+'</td><td><span class="badge badge-'+(r.occupancy>=70?'success':r.occupancy>=40?'warning':'danger')+'">'+r.occupancy+'%</span></td></tr>';
        });
        $('#tblBody').html(html);
        $('#grandTotal').text(res.grandTotalRooms);
        $('#grandOcc').text(res.grandOccupied);
        $('#grandVac').text(res.grandVacant);
        $('#overallOcc').text(res.overallOccPercent+'%');
    });
}
function exportCSV(){
    var csv='RoomType,TotalRooms,Occupied,Vacant,OccupancyPct\n';
    $('#tblBody tr').each(function(){var r=[];$(this).find('td').each(function(){r.push('"'+$(this).text().replace(/"/g,'""')+'"');});csv+=r.join(',')+'\n';});
    var blob=new Blob([csv],{type:'text/csv'});var a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download='room_type_occupancy.csv';a.click();
}
</script>
@endsection