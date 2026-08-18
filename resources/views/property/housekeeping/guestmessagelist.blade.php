@extends('property.layouts.main')
@section('main-container')
@include('property.layouts.pageheader', ['title' => 'Guest Messages', 'subtitle' => 'Take and track messages for in-house guests'])

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">From Date</label>
                        <input type="date" class="form-control" id="fromdate" value="{{ $fromdate }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">To Date</label>
                        <input type="date" class="form-control" id="todate" value="{{ $fromdate }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All</option>
                            <option value="Pending">Pending</option>
                            <option value="Delivered">Delivered</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button class="btn btn-primary" id="searchBtn"><i class="bi bi-search me-1"></i>Search</button>
                        <button class="btn btn-success" id="newBtn"><i class="bi bi-plus-circle me-1"></i>New Message</button>
                        <button class="btn btn-outline-secondary" id="printBtn"><i class="bi bi-printer me-1"></i>Print</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="messageTable" style="width:100%">
                        <thead class="table-primary">
                            <tr>
                                <th>Room</th>
                                <th>Caller</th>
                                <th>Telephone</th>
                                <th>Message</th>
                                <th>Guest</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="messageBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-envelope me-2"></i>Take Message</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="messageForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Room No <span class="text-danger">*</span></label>
                            <select class="form-select" id="mroomno" name="roomno" required>
                                <option value="">Select Room</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Guest</label>
                            <input type="text" class="form-control" id="mguestprof" name="guestprof" readonly>
                        </div>
                        <div class="col-md-12">
                            <input type="hidden" id="mfolionodocid" name="folionodocid">
                            <input type="hidden" id="mroomcat" name="roomcat">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Caller Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mcaller" name="caller" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Telephone</label>
                            <input type="text" class="form-control" id="mtelephone" name="telephone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="mrecddate" name="recddate" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Time</label>
                            <input type="time" class="form-control" id="mrecdtime" name="recdtime">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="mmessage" name="message" rows="3" required></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitMessage"><i class="bi bi-check-circle me-1"></i>Save</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function() {
    let table = new DataTable('#messageTable', { pageLength: 15, order: [] });

    function loadData() {
        $.post('{{ route("fetchguestmessagedata") }}', {
            _token: '{{ csrf_token() }}',
            fromdate: $('#fromdate').val(),
            todate: $('#todate').val(),
            status: $('#statusFilter').val()
        }, function(res) {
            table.clear();
            res.data.forEach(function(r) {
                var statusBadge = r.status === 'Delivered'
                    ? '<span class="badge bg-success">Delivered</span>'
                    : '<span class="badge bg-warning text-dark">Pending</span>';
                var actions = '';
                if (r.status === 'Pending') {
                    actions = '<button class="btn btn-sm btn-outline-success deliverBtn me-1" data-id="' + r.id + '" title="Mark Delivered"><i class="bi bi-check-lg"></i></button>';
                }
                actions += '<button class="btn btn-sm btn-outline-danger deleteMsgBtn" data-id="' + r.id + '"><i class="bi bi-trash"></i></button>';
                table.row.add([
                    r.roomno,
                    r.caller,
                    r.telephone || '-',
                    r.message,
                    r.guestprof || '-',
                    dmy(r.recddate),
                    r.recdtime,
                    statusBadge,
                    actions
                ]).draw();
            });
        });
    }

    loadData();

    $('#searchBtn, #statusFilter').on('click change', loadData);

    $('#newBtn').on('click', function() {
        $.get('{{ route("openguestmessageentry") }}', function(res) {
            $('#mroomno').html('<option value="">Select Room</option>');
            res.rooms.forEach(function(r) {
                $('#mroomno').append('<option value="' + r.roomno + '" data-guest="' + r.guestprof + '" data-folio="' + r.folionodocid + '" data-cat="' + r.roomcatname + '">' + r.roomno + ' - ' + r.guestprof + '</option>');
            });
            $('#mrecddate').val(res.recddate);
            $('#mrecdtime').val(res.recdtime);
            $('#messageForm')[0].reset();
            $('#messageModal').modal('show');
        });
    });

    $('#mroomno').on('change', function() {
        var opt = $(this).find(':selected');
        $('#mguestprof').val(opt.data('guest') || '');
        $('#mfolionodocid').val(opt.data('folio') || '');
        $('#mroomcat').val(opt.data('cat') || '');
    });

    $('#submitMessage').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Saving...');
        $.ajax({
            url: '{{ route("submitguestmessage") }}',
            type: 'POST',
            data: $('#messageForm').serialize(),
            success: function(res) {
                if (res.success) {
                    Swal.fire('Success', res.message, 'success');
                    $('#messageModal').modal('hide');
                    loadData();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Something went wrong', 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i>Save');
            }
        });
    });

    $(document).on('click', '.deliverBtn', function() {
        var id = $(this).data('id');
        $.post('{{ route("markmessagedelivered") }}', { _token: '{{ csrf_token() }}', id: id }, function(res) {
            Swal.fire('Done', res.message, 'success');
            loadData();
        });
    });

    $(document).on('click', '.deleteMsgBtn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Delete?',
            text: 'Delete this message?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.post('{{ route("deleteguestmessage") }}', { _token: '{{ csrf_token() }}', id: id }, function(res) {
                    Swal.fire('Deleted', res.message, 'success');
                    loadData();
                });
            }
        });
    });

    $('#printBtn').on('click', function() {
        var from = $('#fromdate').val();
        var to = $('#todate').val();
        window.open('{{ route("printguestmessagelist") }}?fromdate=' + from + '&todate=' + to, '_blank');
    });

    function dmy(dt) {
        if (!dt) return '';
        var p = dt.split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    }
});
</script>
@endpush
