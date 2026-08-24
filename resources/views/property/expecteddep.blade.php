@extends('property.layouts.property')
@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h4><i class="fas fa-door-open text-warning"></i> {{ $view }}</h4>
    </div>
</div>
<section class="content">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <div class="row">
                <div class="col-md-3">
                    <label>Departure Date</label>
                    <input type="date" id="todate" class="form-control form-control-sm" value="{{ $td }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary btn-sm mr-2" onclick="fetchData()"><i class="fas fa-search"></i> Search</button>
                    <button class="btn btn-success btn-sm mr-2" onclick="exportCSV()"><i class="fas fa-file-csv"></i> CSV</button>
                    <button class="btn btn-info btn-sm" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4"><strong>Total Expected:</strong> <span class="badge badge-warning" id="depCount">0</span></div>
                <div class="col-md-4"><strong>Total Balance:</strong> <span class="badge badge-danger" id="totalBal">₹0</span></div>
            </div>
            <div class="table-responsive">
                <table id="dataTable" class="table table-sm table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Room</th><th>Category</th><th>Rate</th><th>Guest</th><th>Mobile</th><th>Check-In</th><th>Departure</th><th>Days</th><th>Charges</th><th>Payments</th><th>Balance</th>
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
$(function(){ fetchData(); });
function fetchData(){
    $.post('{{ route("expecteddepfetch") }}',{todate:$('#todate').val(),_token:'{{ csrf_token() }}'},function(res){
        var html='';
        $.each(res.data,function(i,r){
            var bal=Number(r.balance||0);
            var balCls=bal>0?'text-danger':bal<0?'text-success':'';
            html+='<tr><td>'+r.roomno+'</td><td>'+r.roomcategory+'</td><td>₹'+fmtN(r.roomrate)+'</td><td>'+r.guestname+'</td><td>'+r.mobile+'</td><td>'+fmt(r.chkindate)+'</td><td>'+fmt(r.depdate)+'</td><td>'+r.nodays+'</td><td>₹'+fmtN(r.totalcharges)+'</td><td>₹'+fmtN(r.totalpayments)+'</td><td class="'+balCls+'"><strong>₹'+fmtN(bal)+'</strong></td></tr>';
        });
        $('#tblBody').html(html);
        $('#depCount').text(res.data.length);
        $('#totalBal').text('₹'+fmtN(res.totalBalance));
    });
}
function fmt(v){ return v||''; }
function fmtN(v){ return Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2}); }
function exportCSV(){
    var csv='Room,Category,Rate,Guest,Mobile,CheckIn,Departure,Days,Charges,Payments,Balance\n';
    $('#tblBody tr').each(function(){var r=[];$(this).find('td').each(function(){r.push('"'+$(this).text().replace(/"/g,'""')+'"');});csv+=r.join(',')+'\n';});
    var blob=new Blob([csv],{type:'text/csv'});var a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download='expected_departure.csv';a.click();
}
</script>
@endsection