@extends('property.layouts.main')

@section('content')
<div class="content-body">
   <div class="page-header mb-3">
      <div class="row align-items-center">
         <div class="col">
            <h3 class="page-title">Denomination Detail</h3>
         </div>
         <div class="col text-end">
            <a href="{{ route('denomination.create') }}" class="btn btn-primary">
               <i class="fa fa-plus"></i> New Entry
            </a>
         </div>
      </div>
   </div>

   <div class="row">
      <div class="col-12">
         <div class="card">
            <div class="card-body">
               <div class="table-responsive">
                  <table class="table table-bordered table-striped" id="denominationTable">
                     <thead>
                        <tr>
                           <th>S.No</th>
                           <th>Date</th>
                           <th>Name</th>
                           <th>Items</th>
                           <th>Total</th>
                           <th>Actions</th>
                        </tr>
                     </thead>
                     <tbody>
                        @forelse($data as $row)
                        <tr>
                           <td>{{ $row->sno }}</td>
                           <td>{{ date('d-M-Y', strtotime($row->vdate)) }}</td>
                           <td>{{ $row->name }}</td>
                           <td>
                              <button class="btn btn-sm btn-info" onclick="viewDetail({{ $row->sno }})">
                                 View Items
                              </button>
                           </td>
                           <td>₹{{ number_format($row->denominationtotal, 2) }}</td>
                           <td>
                              <button class="btn btn-sm btn-success" onclick="printDenom({{ $row->sno }})">
                                 <i class="fa fa-print"></i>
                              </button>
                              <button class="btn btn-sm btn-danger" onclick="deleteDenom({{ $row->sno }})">
                                 <i class="fa fa-trash"></i>
                              </button>
                           </td>
                        </tr>
                        @empty
                        <tr>
                           <td colspan="6" class="text-center">No denomination entries found</td>
                        </tr>
                        @endforelse
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Denomination Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body">
            <table class="table table-bordered" id="detailTable">
               <thead>
                  <tr>
                     <th>#</th>
                     <th>Type</th>
                     <th>Value</th>
                     <th>Unit</th>
                     <th>Total</th>
                  </tr>
               </thead>
               <tbody id="detailBody"></tbody>
            </table>
         </div>
      </div>
   </div>
</div>
@endsection

@section('scripts')
<script>
function viewDetail(sno) {
   $.get('/denomination/' + sno, function(res) {
      var html = '';
      res.data.forEach(function(row, idx) {
         html += '<tr><td>' + (idx+1) + '</td><td>' + row.denominationtype + '</td><td>₹' + Number(row.denominationvalue).toLocaleString('en-IN') + '</td><td>' + row.denominationunit + '</td><td>₹' + Number(row.denominationtotal).toLocaleString('en-IN') + '</td></tr>';
      });
      $('#detailBody').html(html);
      new bootstrap.Modal('#detailModal').show();
   });
}

function printDenom(sno) {
   window.open('/denomination/print/' + sno, '_blank');
}

function deleteDenom(sno) {
   if (confirm('Are you sure you want to delete this entry?')) {
      $.ajax({
         url: '/denomination/' + sno,
         type: 'DELETE',
         headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
         success: function(res) {
            if (res.success) {
               location.reload();
            } else {
               alert(res.message);
            }
         }
      });
   }
}
</script>
@endsection
