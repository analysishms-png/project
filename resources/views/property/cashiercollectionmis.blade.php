@extends('property.layouts.main')
@section('main-container')
<link href="{{ asset('admin/css/dashboard-modern.css') }}" rel="stylesheet">
<div class="content-body">
    <div class="container-fluid" style="margin-top:90px;">
        <div class="dash-title-bar">
            <div class="title-left"><h3>cashiercollectionmis</h3></div>
            <div class="title-right"><button onclick="window.print()" class="dash-btn-icon"><i class="fa fa-print"></i></button></div>
        </div>
        <div class="card mb-4"><div class="card-body"><div class="row g-3">
            <div class="col-md-3"><label>From Date</label><input type="date" id="fromdate" class="form-control" value="\{{ date('Y-m-d') }}"></div>
            <div class="col-md-3"><label>To Date</label><input type="date" id="todate" class="form-control" value="\{{ date('Y-m-d') }}"></div>
            <div class="col-md-3 d-flex align-items-end"><button id="fetchBtn" class="btn btn-primary"><i class="fa fa-search"></i> Fetch</button></div>
        </div></div></div>
        <div class="card"><div class="card-body table-responsive">
            <table class="table table-bordered table-sm"><thead class="bg-primary text-white"><tr><th>Sr</th><th>Data</th></tr></thead><tbody id="tableBody"></tbody></table>
        </div></div>
    </div>
</div>
<script>
$(document).ready(function(){$('#fetchBtn').click(function(){
    $.post('\{{ route("cashiercollectionmisfetch") }}',{fromdate:$('#fromdate').val(),todate:$('#todate').val()},function(res){
        var h='';$.each(res,function(i,r){h+='<tr><td>'+(i+1)+'</td><td>'+JSON.stringify(r)+'</td></tr>';});
        $('#tableBody').html(h);
    });
});$('#fetchBtn').click();});
</script>
@endsection
