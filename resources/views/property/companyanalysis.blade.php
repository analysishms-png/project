@extends('property.layouts.property')
@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h4><i class="fas fa-building text-primary"></i> {{ $view }}</h4>
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
                <div class="col-md-4"><strong>Total Charges:</strong> <span class="badge badge-primary" id="totCharges">₹0</span></div>
                <div class="col-md-4"><strong>Total Payments:</strong> <span class="badge badge-success" id="totPayments">₹0</span></div>
                <div class="col-md-4"><strong>Outstanding:</strong> <span class="badge badge-danger" id="totOutstanding">₹0</span></div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <thead class="thead-dark"><tr><th>Code</th><th>Company</th><th>Room Nights</th><th>Total Charges</th><th>Total Payments</th><th>Outstanding</th></tr></thead>
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
    $.post('{{ route("companyanalysisfetch") }}',{fromdate:$('#fromdate').val(),todate:$('#todate').val(),_token:'{{ csrf_token() }}'},function(res){
        var html='';
        $.each(res.data,function(i,r){
            var bal=Number(r.outstanding||0);
            html+='<tr><td>'+r.sub_code+'</td><td>'+r.companyname+'</td><td>'+r.roomnights+'</td><td>₹'+fmtN(r.totalcharges)+'</td><td>₹'+fmtN(r.totalpayments)+'</td><td class="'+(bal>0?'text-danger':'text-success')+'"><strong>₹'+fmtN(bal)+'</strong></td></tr>';
        });
        $('#tblBody').html(html);
        $('#totCharges').text('₹'+fmtN(res.totCharges));
        $('#totPayments').text('₹'+fmtN(res.totPayments));
        $('#totOutstanding').text('₹'+fmtN(res.totOutstanding));
    });
}
function fmtN(v){ return Number(v||0).toLocaleString('en-IN',{minimumFractionDigits:2}); }
function exportCSV(){
    var csv='Code,Company,RoomNights,Charges,Payments,Outstanding\n';
    $('#tblBody tr').each(function(){var r=[];$(this).find('td').each(function(){r.push('"'+$(this).text().replace(/"/g,'""')+'"');});csv+=r.join(',')+'\n';});
    var blob=new Blob([csv],{type:'text/csv'});var a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download='company_analysis.csv';a.click();
}
</script>
@endsection