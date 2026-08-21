@extends('property.layouts.property')
@section('content')

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- Header -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="page-title mb-1">
                                <i class="mdi mdi-history me-2"></i>Communication Log
                            </h4>
                            <p class="text-muted mb-0">Complete history of all sent messages</p>
                        </div>
                        <a href="{{ url('communication') }}" class="btn btn-soft-primary btn-sm">
                            <i class="mdi mdi-arrow-left me-1"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <form method="GET" action="{{ url('communication/log') }}" class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label">Type</label>
                                    <select class="form-select form-select-sm" name="type">
                                        <option value="">All Types</option>
                                        @foreach($types as $t)
                                        <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select class="form-select form-select-sm" name="status">
                                        <option value="">All</option>
                                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">From Date</label>
                                    <input type="date" class="form-control form-control-sm" name="from_date" value="{{ request('from_date') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">To Date</label>
                                    <input type="date" class="form-control form-control-sm" name="to_date" value="{{ request('to_date') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control form-control-sm" name="phone" value="{{ request('phone') }}" placeholder="Phone number">
                                </div>
                                <div class="col-md-2 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="mdi mdi-magnify me-1"></i>Filter</button>
                                    <a href="{{ url('communication/log') }}" class="btn btn-secondary btn-sm">Clear</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Log Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            @if($logs->count())
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date/Time</th>
                                            <th>Type</th>
                                            <th>Phone</th>
                                            <th>Template</th>
                                            <th>Status</th>
                                            <th>HTTP</th>
                                            <th>Sent By</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($logs as $i => $log)
                                        <tr>
                                            <td>{{ $logs->firstItem() + $i }}</td>
                                            <td><small>{{ \Carbon\Carbon::parse($log->created_at)->format('d-M-Y H:i:s') }}</small></td>
                                            <td><span class="badge badge-soft-primary">{{ $log->type }}</span></td>
                                            <td><code>{{ $log->recipient_phone_number }}</code></td>
                                            <td><small>{{ $log->template_id }}</small></td>
                                            <td>
                                                @if($log->status == 'success')
                                                    <span class="badge badge-soft-success"><i class="mdi mdi-check"></i> Delivered</span>
                                                @else
                                                    <span class="badge badge-soft-danger"><i class="mdi mdi-close"></i> Failed</span>
                                                @endif
                                            </td>
                                            <td><code>{{ $log->http_code }}</code></td>
                                            <td><small>{{ $log->u_name }}</small></td>
                                            <td>
                                                <button class="btn btn-sm btn-soft-secondary" onclick="showDetail({{ $log->id }})" title="View Details">
                                                    <i class="mdi mdi-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-center mt-3">
                                {{ $logs->appends(request()->query())->links() }}
                            </div>
                            @else
                            <div class="text-center text-muted py-5">
                                <i class="mdi mdi-message-text-outline font-size-48 mb-2"></i>
                                <p>No communications found matching your filters.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="detailContent" class="bg-light p-3 rounded" style="white-space: pre-wrap;"></pre>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
var logData = {};
@foreach($logs as $log)
logData[{{ $log->id }}] = {
    phone: '{{ $log->recipient_phone_number }}',
    type: '{{ $log->type }}',
    template: '{{ $log->template_id }}',
    parameters: {!! json_encode($log->parameters) !!},
    response: {!! json_encode($log->response) !!},
    http_code: {{ $log->http_code }},
    status: '{{ $log->status }}',
    sent_by: '{{ $log->u_name }}',
    sent_at: '{{ \Carbon\Carbon::parse($log->created_at)->format("d-M-Y H:i:s") }}'
};
@endforeach

function showDetail(id) {
    var d = logData[id];
    if (!d) return;
    var html = '<strong>Sent:</strong> ' + d.sent_at + '\n';
    html += '<strong>To:</strong> ' + d.phone + '\n';
    html += '<strong>Type:</strong> ' + d.type + '\n';
    html += '<strong>Template:</strong> ' + d.template + '\n';
    html += '<strong>Status:</strong> ' + d.status + ' (HTTP ' + d.http_code + ')\n';
    html += '<strong>Sent By:</strong> ' + d.sent_by + '\n\n';
    html += '<strong>Parameters:</strong>\n' + (d.parameters || 'N/A') + '\n\n';
    html += '<strong>API Response:</strong>\n' + (d.response || 'N/A');
    $('#detailContent').text(html);
    $('#detailModal').modal('show');
}
</script>
@endsection
