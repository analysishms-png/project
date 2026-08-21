@extends('property.layouts.property')
@section('content')

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="page-title mb-1">
                                <i class="mdi mdi-message-text-outline me-2"></i>Communication Hub
                            </h4>
                            <p class="text-muted mb-0">Manage all guest communications — WhatsApp, SMS, Email</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-soft-primary btn-sm" onclick="$('#sendManualModal').modal('show')">
                                <i class="mdi mdi-send me-1"></i>Send Message
                            </button>
                            <a href="{{ url('communication/log') }}" class="btn btn-soft-info btn-sm">
                                <i class="mdi mdi-history me-1"></i>View Log
                            </a>
                            <a href="{{ url('communication/email-templates') }}" class="btn btn-soft-secondary btn-sm">
                                <i class="mdi mdi-email-outline me-1"></i>Email Templates
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-sm rounded-circle bg-soft-primary d-flex align-items-center justify-content-center">
                                        <i class="mdi mdi-send text-primary font-size-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-0">{{ number_format($totalSent) }}</h3>
                                    <p class="text-muted mb-0 font-size-13">Total Sent (30 days)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-sm rounded-circle bg-soft-success d-flex align-items-center justify-content-center">
                                        <i class="mdi mdi-check-circle text-success font-size-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-0">{{ number_format($totalSuccess) }}</h3>
                                    <p class="text-muted mb-0 font-size-13">Delivered</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-sm rounded-circle bg-soft-danger d-flex align-items-center justify-content-center">
                                        <i class="mdi mdi-close-circle text-danger font-size-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-0">{{ number_format($totalFailed) }}</h3>
                                    <p class="text-muted mb-0 font-size-13">Failed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-sm rounded-circle bg-soft-warning d-flex align-items-center justify-content-center">
                                        <i class="mdi mdi-whatsapp text-warning font-size-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-0">{{ number_format($whatsappBalance) }}</h3>
                                    <p class="text-muted mb-0 font-size-13">WhatsApp Balance</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages by Type -->
            <div class="row">
                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0">
                            <h5 class="card-title mb-0"><i class="mdi mdi-chart-bar me-2"></i>Messages by Type (30 days)</h5>
                        </div>
                        <div class="card-body">
                            @if($byType->count())
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Delivered</th>
                                            <th class="text-center">Failed</th>
                                            <th>Success Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($byType as $row)
                                        <tr>
                                            <td>
                                                <span class="badge badge-soft-primary">{{ $row->type }}</span>
                                            </td>
                                            <td class="text-center">{{ number_format($row->cnt) }}</td>
                                            <td class="text-center text-success">{{ number_format($row->success_cnt) }}</td>
                                            <td class="text-center text-danger">{{ number_format($row->cnt - $row->success_cnt) }}</td>
                                            <td>
                                                @php $rate = $row->cnt > 0 ? round(($row->success_cnt / $row->cnt) * 100) : 0; @endphp
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-{{ $rate >= 90 ? 'success' : ($rate >= 70 ? 'warning' : 'danger') }}" style="width: {{ $rate }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $rate }}%</small>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center text-muted py-4">
                                <i class="mdi mdi-message-text-outline font-size-48 mb-2"></i>
                                <p>No messages sent yet. Send your first message!</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0">
                            <h5 class="card-title mb-0"><i class="mdi mdi-lightning-bolt me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary text-start" onclick="$('#sendManualModal').modal('show')">
                                    <i class="mdi mdi-send me-2"></i>Send Custom Message
                                    <small class="text-muted d-block">Send WhatsApp/SMS to any guest</small>
                                </button>
                                <button class="btn btn-outline-success text-start" onclick="sendBulkPreArrival()">
                                    <i class="mdi mdi-bell-ring me-2"></i>Send Pre-Arrival to All
                                    <small class="text-muted d-block">Welcome arriving guests today/tomorrow</small>
                                </button>
                                <button class="btn btn-outline-info text-start" onclick="sendBulkCheckoutFollowup()">
                                    <i class="mdi mdi-account-check me-2"></i>Send Checkout Follow-up
                                    <small class="text-muted d-block">Thank you messages to yesterday's checkouts</small>
                                </button>
                                <a href="{{ url('communication/log') }}" class="btn btn-outline-secondary text-start">
                                    <i class="mdi mdi-history me-2"></i>Communication History
                                    <small class="text-muted d-block">View all sent messages</small>
                                </a>
                                <a href="{{ url('communication/email-templates') }}" class="btn btn-outline-warning text-start">
                                    <i class="mdi mdi-email-outline me-2"></i>Email Templates
                                    <small class="text-muted d-block">Manage email templates</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pre-Arrivals & Checkout Follow-ups -->
            <div class="row">
                <!-- Pre-Arrivals -->
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="mdi mdi-airplane-landing me-2"></i>Pre-Arrivals (Today/Tomorrow)</h5>
                            <span class="badge badge-soft-primary">{{ $preArrivals->count() }}</span>
                        </div>
                        <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                            @if($preArrivals->count())
                            <div class="list-group list-group-flush">
                                @foreach($preArrivals as $arr)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $arr['guest_name'] }}</h6>
                                            <small class="text-muted">
                                                <i class="mdi mdi-door me-1"></i>{{ $arr['reservation_no'] }}
                                                @if($arr['room_no']) <span class="badge badge-soft-info ms-1">Room {{ $arr['room_no'] }}</span> @endif
                                            </small>
                                            <br><small class="text-muted"><i class="mdi mdi-calendar me-1"></i>{{ $arr['arrival_date'] }}</small>
                                            @if($arr['advance'] > 0)
                                            <br><small class="text-success"><i class="mdi mdi-currency-inr me-1"></i>Advance: ₹{{ number_format($arr['advance']) }}</small>
                                            @endif
                                        </div>
                                        <button class="btn btn-sm btn-soft-success" onclick="sendPreArrival('{{ $arr['reservation_no'] }}')" title="Send Pre-Arrival Message">
                                            <i class="mdi mdi-send"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="text-center text-muted py-3">
                                <i class="mdi mdi-calendar-check font-size-32"></i>
                                <p class="mb-0">No arrivals today/tomorrow</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Checkout Follow-ups -->
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="mdi mdi-account-check me-2"></i>Checkout Follow-ups (Yesterday)</h5>
                            <span class="badge badge-soft-info">{{ $recentCheckouts->count() }}</span>
                        </div>
                        <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                            @if($recentCheckouts->count())
                            <div class="list-group list-group-flush">
                                @foreach($recentCheckouts as $co)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $co['name'] }}</h6>
                                            <small class="text-muted">
                                                <i class="mdi mdi-door me-1"></i>{{ $co['docid'] }}
                                                <span class="badge badge-soft-secondary ms-1">Room {{ $co['roomno'] }}</span>
                                            </small>
                                            <br><small class="text-muted"><i class="mdi mdi-calendar me-1"></i>Checked out: {{ $co['chkoutdate'] }}</small>
                                        </div>
                                        <button class="btn btn-sm btn-soft-info" onclick="sendCheckoutFollowup('{{ $co['docid'] }}')" title="Send Follow-up">
                                            <i class="mdi mdi-send"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="text-center text-muted py-3">
                                <i class="mdi mdi-check-circle-outline font-size-32"></i>
                                <p class="mb-0">No checkouts yesterday</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Communication Log -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="mdi mdi-text-box-outline me-2"></i>Recent Communications</h5>
                            <a href="{{ url('communication/log') }}" class="btn btn-sm btn-soft-primary">View All</a>
                        </div>
                        <div class="card-body">
                            @if($recentLogs->count())
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>Type</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th>By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentLogs->take(10) as $log)
                                        <tr>
                                            <td><small>{{ \Carbon\Carbon::parse($log->created_at)->format('d-M H:i') }}</small></td>
                                            <td><span class="badge badge-soft-primary">{{ $log->type }}</span></td>
                                            <td><code>{{ $log->recipient_phone_number }}</code></td>
                                            <td>
                                                @if($log->status == 'success')
                                                    <span class="badge badge-soft-success"><i class="mdi mdi-check"></i> Delivered</span>
                                                @else
                                                    <span class="badge badge-soft-danger"><i class="mdi mdi-close"></i> Failed</span>
                                                @endif
                                            </td>
                                            <td><small>{{ $log->u_name }}</small></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center text-muted py-4">
                                <i class="mdi mdi-message-text-outline font-size-48 mb-2"></i>
                                <p>No communications yet</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Send Manual Message Modal -->
<div class="modal fade" id="sendManualModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="mdi mdi-send me-2"></i>Send Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="sendManualForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="phone" placeholder="91XXXXXXXXXX" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="type" required>
                            <option value="Bill Message">Bill Message</option>
                            <option value="Checkin">Check-in</option>
                            <option value="Checkout">Checkout</option>
                            <option value="Reservation">Reservation</option>
                            <option value="KOT Bill">KOT Bill</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="message" rows="4" placeholder="Type your message..." required maxlength="1000"></textarea>
                        <small class="text-muted">Max 1000 characters</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitManualMessage()">
                    <i class="mdi mdi-send me-1"></i>Send
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function submitManualMessage() {
    var form = $('#sendManualForm');
    $.ajax({
        url: '{{ url("communication/send") }}',
        type: 'POST',
        data: form.serialize(),
        success: function(res) {
            if (res.success) {
                toastr.success(res.message);
                $('#sendManualModal').modal('hide');
                location.reload();
            } else {
                toastr.error(res.message);
            }
        },
        error: function() {
            toastr.error('Failed to send message');
        }
    });
}

function sendPreArrival(docid) {
    if (!confirm('Send pre-arrival message to ' + docid + '?')) return;
    $.ajax({
        url: '{{ url("communication/pre-arrival") }}',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}', docid: docid },
        success: function(res) {
            toastr[res.success ? 'success' : 'error'](res.message);
            if (res.success) location.reload();
        }
    });
}

function sendCheckoutFollowup(docid) {
    if (!confirm('Send checkout follow-up to ' + docid + '?')) return;
    $.ajax({
        url: '{{ url("communication/checkout-followup") }}',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}', docid: docid },
        success: function(res) {
            toastr[res.success ? 'success' : 'error'](res.message);
            if (res.success) location.reload();
        }
    });
}

function sendBulkPreArrival() {
    if (!confirm('Send pre-arrival messages to ALL arriving guests today/tomorrow?')) return;
    var phones = [];
    @foreach($preArrivals as $arr)
    @if($arr['phone'])
    phones.push('{{ $arr["phone"] }}');
    @endif
    @endforeach

    if (phones.length === 0) {
        toastr.warning('No phone numbers available');
        return;
    }

    $.ajax({
        url: '{{ url("communication/bulk-send") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            phones: phones,
            message: 'Welcome! Your reservation at our hotel is confirmed. We look forward to hosting you. Regards, Hotel Team',
            type: 'Reservation'
        },
        success: function(res) {
            toastr[res.success ? 'success' : 'error'](res.message);
            if (res.success) location.reload();
        }
    });
}

function sendBulkCheckoutFollowup() {
    if (!confirm('Send follow-up messages to ALL yesterday\'s checkouts?')) return;
    var phones = [];
    @foreach($recentCheckouts as $co)
    @if($co['phone'])
    phones.push('{{ $co["phone"] }}');
    @endif
    @endforeach

    if (phones.length === 0) {
        toastr.warning('No phone numbers available');
        return;
    }

    $.ajax({
        url: '{{ url("communication/bulk-send") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            phones: phones,
            message: 'Thank you for staying with us! We hope you had a wonderful experience. We look forward to welcoming you again soon!',
            type: 'Checkout'
        },
        success: function(res) {
            toastr[res.success ? 'success' : 'error'](res.message);
            if (res.success) location.reload();
        }
    });
}
</script>
@endsection
