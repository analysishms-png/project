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
                                <i class="mdi mdi-email-outline me-2"></i>Email & Message Templates
                            </h4>
                            <p class="text-muted mb-0">Manage communication templates for all guest touchpoints</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-soft-info btn-sm" onclick="$('#testEmailModal').modal('show')">
                                <i class="mdi mdi-email-check me-1"></i>Test Email
                            </button>
                            <a href="{{ url('communication') }}" class="btn btn-soft-primary btn-sm">
                                <i class="mdi mdi-arrow-left me-1"></i>Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Configuration Status -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm bg-soft-success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="mdi mdi-whatsapp text-success font-size-24"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">WhatsApp Configuration</h6>
                                    <small class="text-muted">
                                        API: {{ $wpenv->whatsappurl ?? 'Not configured' }} |
                                        Balance: {{ $wpenv->whatsappbal ?? 0 }} messages |
                                        Display: {{ $wpenv->whatsappdisplayname ?? 'N/A' }} |
                                        Status: {{ ($wpenv->whatsappurl && $wpenv->bearercode) ? '✅ Active' : '❌ Not Configured' }}
                                    </small>
                                </div>
                                <a href="{{ url('whatsappenviro') }}" class="btn btn-sm btn-outline-success">Configure</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Config Status -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm bg-soft-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="mdi mdi-email text-info font-size-24"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">Email Configuration</h6>
                                    <small class="text-muted">
                                        Mailer: {{ config('mail.default') }} |
                                        From: {{ config('mail.from.address', 'Not configured') }} |
                                        Status: {{ config('mail.default') !== 'log' ? '✅ Active' : '⚠️ Using log driver (no real emails)' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Templates Grid -->
            <div class="row">
                @foreach($templates as $key => $tpl)
                <div class="col-xl-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-sm rounded-circle bg-soft-primary d-flex align-items-center justify-content-center me-3">
                                    @if($key == 'reservation_confirm')
                                        <i class="mdi mdi-calendar-check text-primary"></i>
                                    @elseif($key == 'pre_arrival')
                                        <i class="mdi mdi-airplane text-primary"></i>
                                    @elseif($key == 'checkin')
                                        <i class="mdi mdi-door-open text-primary"></i>
                                    @elseif($key == 'checkout')
                                        <i class="mdi mdi-door-closed text-primary"></i>
                                    @elseif($key == 'invoice')
                                        <i class="mdi mdi-receipt text-primary"></i>
                                    @elseif($key == 'feedback')
                                        <i class="mdi mdi-star text-primary"></i>
                                    @elseif($key == 'cancellation')
                                        <i class="mdi mdi-cancel text-primary"></i>
                                    @elseif($key == 'payment_receipt')
                                        <i class="mdi mdi-cash text-primary"></i>
                                    @else
                                        <i class="mdi mdi-email text-primary"></i>
                                    @endif
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $tpl['name'] }}</h6>
                                    <small class="text-muted">{{ $tpl['description'] }}</small>
                                </div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted fw-bold">Subject:</small>
                                <div class="bg-light rounded p-2 font-size-12">{{ $tpl['subject'] }}</div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted fw-bold">Variables:</small>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach($tpl['variables'] as $var)
                                    <span class="badge badge-soft-secondary font-size-11">{{ '{{' . $var . '}}' }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary flex-grow-1" onclick="editTemplate('{{ $key }}')">
                                    <i class="mdi mdi-pencil me-1"></i>Edit
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="previewTemplate('{{ $key }}')">
                                    <i class="mdi mdi-eye me-1"></i>Preview
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </div>
</div>

<!-- Test Email Modal -->
<div class="modal fade" id="testEmailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="mdi mdi-email-check me-2"></i>Send Test Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="testEmailForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" placeholder="test@example.com" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitTestEmail()">
                    <i class="mdi mdi-send me-1"></i>Send Test
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function editTemplate(key) {
    toastr.info('Template editing coming soon. Currently using WhatsApp API templates configured at /whatsappenviro');
}

function previewTemplate(key) {
    toastr.info('Preview will show sample message with placeholder values');
}

function submitTestEmail() {
    $.ajax({
        url: '{{ url("communication/test-email") }}',
        type: 'POST',
        data: $('#testEmailForm').serialize(),
        success: function(res) {
            toastr[res.success ? 'success' : 'error'](res.message);
            if (res.success) $('#testEmailModal').modal('hide');
        },
        error: function() {
            toastr.error('Failed to send test email');
        }
    });
}
</script>
@endsection
