@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid" style="margin-top:90px;">
   <div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0"><i class="fas fa-file-alt"></i> stocksummstore</h5><span class="badge badge-info">HMS.text</span></div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-3"><label>From Date</label><input type="date" id="fromdate" class="form-control form-control-sm" value="{{ $fd }}"></div>
            <div class="col-md-3"><label>To Date</label><input type="date" id="todate" class="form-control form-control-sm" value="{{ $td }}"></div>
            <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div>
         </div>
         <div class="table-responsive"><table class="table table-sm table-bordered table-striped" style="font-size:12px;"><thead class="thead-dark"><tr id="headerRow"></tr></thead><tbody id="dataBody"></tbody><tfoot id="footerRow"></tfoot></table></div>
      </div>
   </div>
</div>
<script>
$(function(){$('#fetchBtn').click(function(){$.post('{{ route("stocksummstorefetch") }}',{fromdate:$('#fromdate').val(),todate:$('#todate').val(),_token:'{{ csrf_token() }}'},function(res){var html='';$.each(res.data||[],function(i,r){var keys=Object.keys(r);html+='<tr>';for(var k of keys)html+='<td>'+r[k]+'</td>';html+='</tr>';});$('#dataBody').html(html);});});
});
</script>
@endsection
