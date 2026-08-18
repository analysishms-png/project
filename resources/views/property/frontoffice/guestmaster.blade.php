@extends('property.layouts.main')
@section('main-container')
@include('property.layouts.pageheader', ['title' => 'Guest Master', 'subtitle' => 'Browse guest profiles and stay history'])

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by name, mobile, email or guest code...">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary me-2" id="searchBtn"><i class="bi bi-search me-1"></i>Search</button>
                        <button class="btn btn-outline-secondary" id="resetBtn">Reset</button>
                    </div>
                    <div class="col-md-3 text-md-end d-flex align-items-end justify-content-md-end">
                        <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Shows up to 500 profiles</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="guestTable" style="width:100%">
                        <thead class="table-primary">
                            <tr>
                                <th>Guest Code</th>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th>City</th>
                                <th>Country</th>
                                <th>Type</th>
                                <th>VIP</th>
                                <th>Stays</th>
                                <th>Last Check-in</th>
                                <th>Last Check-out</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="guestBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Visit History Modal -->
<div class="modal fade" id="visitsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-clock-history me-2"></i>Stay History</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 class="fw-semibold mb-3" id="visitGuestName"></h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Room</th>
                                <th>Category</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Folio</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="visitBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function() {
    let table = new DataTable('#guestTable', { pageLength: 25, order: [] });

    function loadData(search) {
        $.post('{{ route("fetchguestmaster") }}', {
            _token: '{{ csrf_token() }}',
            search: search || ''
        }, function(res) {
            table.clear();
            res.data.forEach(function(r) {
                var vip = r.vipStatus === 'VIP'
                    ? '<span class="badge bg-warning text-dark">VIP</span>'
                    : '<span class="badge bg-secondary">No</span>';
                var actions = '<button class="btn btn-sm btn-outline-primary visitBtn me-1" data-code="' + r.guestcode + '" data-name="' + (r.name || '').replace(/'/g, "\\'") + '" title="Stay History"><i class="bi bi-clock-history"></i></button>';
                actions += '<a class="btn btn-sm btn-outline-success" href="{{ route("guestaddprofile.route") }}?docid=' + r.guestcode + '" title="Edit Profile"><i class="bi bi-pencil"></i></a>';
                table.row.add([
                    r.guestcode,
                    r.name || '-',
                    r.mobile_no || '-',
                    r.email_id || '-',
                    r.city_name || '-',
                    r.country_name || '-',
                    r.type || '-',
                    vip,
                    r.total_stays,
                    r.last_checkin || '-',
                    r.last_checkout || '-',
                    actions
                ]).draw();
            });
        });
    }

    loadData('');

    $('#searchBtn').on('click', function() {
        loadData($('#searchInput').val());
    });

    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) loadData($('#searchInput').val());
    });

    $('#resetBtn').on('click', function() {
        $('#searchInput').val('');
        loadData('');
    });

    $(document).on('click', '.visitBtn', function() {
        var code = $(this).data('code');
        var name = $(this).data('name') || '';
        $('#visitGuestName').text(name + ' (' + code + ')');
        $('#visitBody').html('<tr><td colspan="7" class="text-center text-muted">Loading...</td></tr>');
        $('#visitsModal').modal('show');
        $.post('{{ route("guestmastervisits") }}', {
            _token: '{{ csrf_token() }}',
            guestcode: code
        }, function(res) {
            var html = '';
            if (!res.data.length) {
                html = '<tr><td colspan="7" class="text-center text-muted">No stay records found</td></tr>';
            }
            res.data.forEach(function(v, i) {
                var status = v.type === 'I' ? '<span class="badge bg-success">In-house</span>'
                    : v.type === 'O' ? '<span class="badge bg-secondary">Checked out</span>'
                    : '<span class="badge bg-light text-dark">' + (v.type || '-') + '</span>';
                html += '<tr><td>' + (i + 1) + '</td><td>' + v.roomno + '</td><td>' + (v.roomcatname || '-') + '</td><td>' + (v.checkin || '-') + '</td><td>' + (v.checkout || '-') + '</td><td>' + v.folionodocid + '</td><td>' + status + '</td></tr>';
            });
            $('#visitBody').html(html);
        });
    });
});
</script>
@endpush
