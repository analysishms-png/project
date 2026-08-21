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
                                <i class="mdi mdi-format-list-bulleted me-2"></i>All Guest Feedback
                            </h4>
                            <p class="text-muted mb-0">Manage and respond to guest reviews</p>
                        </div>
                        <a href="{{ url('feedback') }}" class="btn btn-soft-primary btn-sm">
                            <i class="mdi mdi-arrow-left me-1"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-2">
                            <form method="GET" action="{{ url('feedback/list') }}" class="row g-3 align-items-end">
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select class="form-select form-select-sm" name="status">
                                        <option value="">All</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Rating</label>
                                    <select class="form-select form-select-sm" name="rating">
                                        <option value="">All</option>
                                        @for($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} Star</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">From</label>
                                    <input type="date" class="form-control form-control-sm" name="from_date" value="{{ request('from_date') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">To</label>
                                    <input type="date" class="form-control form-control-sm" name="to_date" value="{{ request('to_date') }}">
                                </div>
                                <div class="col-md-2 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="mdi mdi-magnify me-1"></i>Filter</button>
                                    <a href="{{ url('feedback/list') }}" class="btn btn-secondary btn-sm">Clear</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feedback Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            @if($feedback->count())
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Guest</th>
                                            <th>Room</th>
                                            <th>Check-in</th>
                                            <th>Check-out</th>
                                            <th>Rating</th>
                                            <th>Comments</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($feedback as $fb)
                                        <tr>
                                            <td>
                                                <strong>{{ $fb->guest_name }}</strong>
                                                <br><small class="text-muted">{{ $fb->docid }}</small>
                                            </td>
                                            <td>{{ $fb->roomno }}</td>
                                            <td><small>{{ $fb->checkin_date }}</small></td>
                                            <td><small>{{ $fb->checkout_date }}</small></td>
                                            <td>
                                                @if($fb->survey_status === 'completed')
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="mdi mdi-star{{ $i <= $fb->overall_rating ? '' : '-outline' }}" style="color: {{ $i <= $fb->overall_rating ? '#f59e0b' : '#d1d5db' }}; font-size: 12px;"></i>
                                                    @endfor
                                                @else
                                                    <span class="badge badge-soft-secondary">{{ ucfirst($fb->survey_status) }}</span>
                                                @endif
                                            </td>
                                            <td><small>{{ Str::limit($fb->comments ?? '—', 40) }}</small></td>
                                            <td>
                                                @if($fb->survey_status === 'completed')
                                                    @if($fb->response)
                                                        <span class="badge badge-soft-success">Responded</span>
                                                    @else
                                                        <span class="badge badge-soft-warning">Needs Response</span>
                                                    @endif
                                                @else
                                                    <span class="badge badge-soft-secondary">{{ ucfirst($fb->survey_status) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($fb->survey_status === 'completed' && !$fb->response)
                                                <button class="btn btn-sm btn-outline-primary" onclick="showRespondModal({{ $fb->id }}, '{{ addslashes($fb->guest_name) }}')">
                                                    <i class="mdi mdi-reply"></i>
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-center mt-3">
                                {{ $feedback->appends(request()->query())->links() }}
                            </div>
                            @else
                            <div class="text-center text-muted py-5">
                                <i class="mdi mdi-star-outline font-size-48 mb-2"></i>
                                <p>No feedback found matching your filters.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Respond Modal -->
<div class="modal fade" id="respondModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="mdi mdi-reply me-2"></i>Respond to <span id="respondGuest"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="respondForm" method="POST">
                @csrf
                <div class="modal-body">
                    <textarea class="form-control" name="response" rows="4" placeholder="Type your response..." required maxlength="500"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-send me-1"></i>Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function showRespondModal(id, name) {
    $('#respondGuest').text(name);
    $('#respondForm').attr('action', '{{ url("feedback/respond/") }}/' + id);
    $('#respondModal').modal('show');
}
</script>
@endsection
