@extends('property.layouts.property')
@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h4><i class="fas fa-bed text-info"></i> {{ $view }}</h4>
    </div>
</div>
<section class="content">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <div class="row">
                <div class="col-md-2"><strong>Total:</strong> <span class="badge badge-secondary" id="totalRooms">0</span></div>
                <div class="col-md-2"><strong>Occupied:</strong> <span class="badge badge-danger" id="occRooms">0</span></div>
                <div class="col-md-2"><strong>Vacant:</strong> <span class="badge badge-success" id="vacRooms">0</span></div>
                <div class="col-md-2"><strong>Dirty:</strong> <span class="badge badge-warning" id="dirtyRooms">0</span></div>
                <div class="col-md-2"><strong>Blocked:</strong> <span class="badge badge-dark" id="blkRooms">0</span></div>
                <div class="col-md-2"><strong>OOO:</strong> <span class="badge badge-secondary" id="oooRooms">0</span></div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary btn-sm" onclick="fetchData()"><i class="fas fa-sync"></i> Refresh</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div id="roomGrid" class="row"></div>
            <div class="table-responsive mt-3">
                <table class="table table-sm table-bordered table-striped">
                    <thead class="thead-dark"><tr><th>Room</th><th>Category</th><th>Status</th><th>Guest</th><th>Rate</th><th>Check-In</th><th>Departure</th></tr></thead>
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
    $.post('{{ route("roomoccdispfetch") }}',{_token:'{{ csrf_token() }}'},function(res){
        var html='';
        $.each(res.data,function(i,r){
            var color=r.status==='Occupied'?'#e74c3c':r.status==='Vacant'?'#27ae60':r.status==='Dirty'?'#f39c12':r.status==='Blocked'?'#34495e':'#95a5a6';
            html+='<div class="col-md-1 col-sm-2 col-3 mb-2"><div class="text-center p-2 rounded" style="background:'+color+';color:#fff;cursor:pointer;font-size:12px;"><strong>'+r.roomno+'</strong><br><small>'+r.status+'</small></div></div>';
            var tcolor=r.status==='Occupied'?'badge-danger':r.status==='Vacant'?'badge-success':r.status==='Dirty'?'badge-warning':r.status==='Blocked'?'badge-dark':'badge-secondary';
            $('#tblBody').append('<tr><td>'+r.roomno+'</td><td>'+r.roomcategory+'</td><td><span class="badge '+tcolor+'">'+r.status+'</span></td><td>'+r.guestname+'</td><td>₹'+fmtN(r.roomrate)+'</td><td>'+fmt(r.chkindate)+'</td><td>'+fmt(r.depdate)+'</td></tr>');
        });
        $('#roomGrid').html(html);
        $('#totalRooms').text(res.summary.total);
        $('#occRooms').text(res.summary.occupied);
        $('#vacRooms').text(res.summary.vacant);
        $('#dirtyRooms').text(res.summary.dirty);
        $('#blkRooms').text(res.summary.blocked);
        $('#oooRooms').text(res.summary.ooo);
    });
}
function fmt(v){ return v||''; }
function fmtN(v){ return Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2}); }
</script>
@endsection