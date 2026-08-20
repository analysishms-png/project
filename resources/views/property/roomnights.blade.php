@extends('property.layouts.main')
@section('main-container')
<div class="container-fluid">
   <div class="card">
      <div class="card-header"><h4 class="card-title"><i class="fas fa-bed"></i> Room Nights Analysis</h4></div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-2"><label>From</label><input type="date" class="form-control form-control-sm" id="fromdate" value="{{ $fromdate }}"></div>
            <div class="col-md-2"><label>To</label><input type="date" class="form-control form-control-sm" id="todate" value="{{ $fromdate }}"></div>
            <div class="col-md-2"><label>&nbsp;</label><div><button class="btn btn-primary btn-sm" id="fetchBtn"><i class="fas fa-search"></i> Fetch</button></div></div>
         </div>
         <div class="row mb-3" id="summaryCards" style="display:none;">
            <div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-bed"></i></span><div class="info-box-content"><span class="info-box-text">Total Rooms</span><span class="info-box-number" id="totalRooms">0</span></div></div></div>
            <div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-moon"></i></span><div class="info-box-content"><span class="info-box-text">Total Room Nights</span><span class="info-box-number" id="totalNights">0</span></div></div></div>
            <div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-calendar"></i></span><div class="info-box-content"><span class="info-box-text">Period Days</span><span class="info-box-number" id="periodDays">0</span></div></div></div>
            <div class="col-md-3"><div class="info-box"><span class="info-box-icon bg-danger"><i class="fas fa-percentage"></i></span><div class="info-box-content"><span class="info-box-text">Occupancy %</span><span class="info-box-number" id="occupancyPct">0%</span></div></div></div>
         </div>
         <table class="table table-sm table-bordered table-striped" id="rnTable"><thead class="thead-dark"><tr><th>Room Type</th><th class="text-center">Total Rooms</th><th class="text-center">Occupied Rooms</th><th class="text-center">Room Nights</th><th class="text-center">Occupancy %</th></tr></thead><tbody id="tableBody"></tbody><tfoot id="tableFoot" style="display:none;"><tr class="font-weight-bold"><td>TOTAL</td><td id="footRooms" class="text-center">0</td><td id="footOccupied" class="text-center">0</td><td id="footNights" class="text-center">0</td><td id="footOcc" class="text-center">0%</td></tr></tfoot></table>
      </div>
   </div>
</div>
<script>
$(document).ready(function() {
   $('#fetchBtn').click(function() {
      $.post('{{ route("roomnightsfetch") }}', {fromdate:$('#fromdate').val(),todate:$('#todate').val()}, function(res) {
         var h='';$.each(res.data,function(i,r){
            var occPct = r.norooms > 0 ? ((r.RoomNights / (r.norooms * res.periodDays)) * 100).toFixed(1) : '0.0';
            h+='<tr><td><b>'+(r.RoomTypeName||'')+'</b></td><td class="text-center">'+r.norooms+'</td><td class="text-center">'+r.OccupiedRooms+'</td><td class="text-center">'+r.RoomNights+'</td><td class="text-center">'+occPct+'%</td></tr>';
         });
         $('#tableBody').html(h);
         $('#totalRooms').text(res.totalRooms);$('#totalNights').text(res.totalNights);$('#periodDays').text(res.periodDays);$('#occupancyPct').text(res.occupancyPct+'%');$('#summaryCards').show();
         $('#footRooms').text(res.totalRooms);$('#footNights').text(res.totalNights);$('#footOcc').text(res.occupancyPct+'%');$('#tableFoot').show();
      });
   });
   $('#fetchBtn').click();
});
</script>
@endsection
