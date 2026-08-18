@extends('property.layouts.main')
@section('main-container')
@include('property.layouts.pageheader', ['title' => 'Wake-up Call Booking', 'subtitle' => 'Manage guest wake-up call requests'])

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">From Date</label>
                        <input type="date" class="form-control" id="fromdate" value="{{ $fromdate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">To Date</label>
                        <input type="date" class="form-control" id="todate" value="{{ $fromdate }}">
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button class="btn btn-primary" id="searchBtn"><i class="bi bi-search me-1"></i>Search</button>
                        <button class="btn btn-success" id="newBtn"><i class="bi bi-plus-circle me-1"></i>New Wake-up</button>
                        <button class="btn btn-outline-secondary" id="printBtn"><i class="bi bi-printer me-1"></i>Print</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="wakeupTable" style="width:100%">
                        <thead class="table-primary">
                            <tr>
                                <th>V.No</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Room</th>
                                <th>Guest</th>
                                <th>Extension</th>
                                <th>Reminder</th>
                                <th>Food Order</th>
                                <th>Other Request</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="wakeupBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Wake-up Modal -->
<div class="modal fade" id="wakeupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-alarm me-2"></i>New Wake-up Call</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="wakeupForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Room No <span class="text-danger">*</span></label>
                            <select class="form-select" id="wroomno" name="roomno" required>
                                <option value="">Select Room</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Guest</label>
                            <input type="text" class="form-control" id="wguestprof" name="guestprof" readonly>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Folio Doc ID</label>
                            <input type="hidden" id="wfolionodocid" name="folionodocid">
                            <input type="text" class="form-control" id="wroomcat" name="roomcat" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Wake-up Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="wwdate" name="wdate" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Wake-up Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="wwtime" name="wtime" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Extension</label>
                            <input type="text" class="form-control" id="wextension" name="extension">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Room Category</label>
                            <input type="text" class="form-control" id="wroomcatname" readonly>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="wremreqd" name="remreqd" value="Y">
                                <label class="form-check-label" for="wremreqd">Reminder Required</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="wfoodord" name="foodord" value="Y">
                                <label class="form-check-label" for="wfoodord">Food Order</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Other Request</label>
                            <textarea class="form-control" id="wotherreq" name="otherreq" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitWakeup"><i class="bi bi-check-circle me-1"></i>Save</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function() {
    let table = new DataTable('#wakeupTable', { pageLength: 15, order: [] });

    function loadData() {
        $.post('{{ route("fetchwakeupdata") }}', {
            _token: '{{ csrf_token() }}',
            fromdate: $('#fromdate').val(),
            todate: $('#todate').val()
        }, function(res) {
            table.clear();
            res.data.forEach(function(r) {
                table.row.add([
                    r.vno,
                    dmy(r.wdate),
                    r.wtime,
                    r.roomno,
                    r.guestprof,
                    r.extension,
                    r.remreqd === 'Y' ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>',
                    r.foodord === 'Y' ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>',
                    r.otherreq || '-',
                    '<button class="btn btn-sm btn-outline-danger deleteBtn" data-id="' + r.id + '"><i class="bi bi-trash"></i></button>'
                ]).draw();
            });
        });
    }

    loadData();

    $('#searchBtn').on('click', loadData);

    $('#newBtn').on('click', function() {
        $.get('{{ route("openwakeupentry") }}', function(res) {
            $('#wroomno').html('<option value="">Select Room</option>');
            res.rooms.forEach(function(r) {
                $('#wroomno').append('<option value="' + r.roomno + '" data-guest="' + r.guestprof + '" data-folio="' + r.folionodocid + '" data-cat="' + r.roomcatname + '">' + r.roomno + ' - ' + r.guestprof + '</option>');
            });
            $('#wwdate').val(res.wdate);
            $('#wwtime').val(res.wtime);
            $('#wakeupForm')[0].reset();
            $('#wakeupModal').modal('show');
        });
    });

    $('#wroomno').on('change', function() {
        var opt = $(this).find(':selected');
        $('#wguestprof').val(opt.data('guest') || '');
        $('#wfolionodocid').val(opt.data('folio') || '');
        $('#wroomcatname').val(opt.data('cat') || '');
    });

    $('#submitWakeup').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Saving...');
        var fd = new FormData($('#wakeupForm')[0]);
        fd.set('remreqd', $('#wremreqd').is(':checked') ? 'Y' : 'N');
        fd.set('foodord', $('#wfoodord').is(':checked') ? 'Y' : 'N');
        $.ajax({
            url: '{{ route("submitwakeup") }}',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    Swal.fire('Success', res.message, 'success');
                    $('#wakeupModal').modal('hide');
                    loadData();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Something went wrong', 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i>Save');
            }
        });
    });

    $(document).on('click', '.deleteBtn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Delete?',
            text: 'Delete this wake-up call?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.post('{{ route("deletewakeup") }}', { _token: '{{ csrf_token() }}', id: id }, function(res) {
                    Swal.fire('Deleted', res.message, 'success');
                    loadData();
                });
            }
        });
    });

    $('#printBtn').on('click', function() {
        var from = $('#fromdate').val();
        var to = $('#todate').val();
        window.open('{{ route("printwakeuplist") }}?fromdate=' + from + '&todate=' + to, '_blank');
    });

    function dmy(dt) {
        if (!dt) return '';
        var p = dt.split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    }
});
</script>
@endpush
