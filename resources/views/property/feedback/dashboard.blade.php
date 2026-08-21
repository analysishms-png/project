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
                                <i class="mdi mdi-star me-2"></i>Guest Feedback
                            </h4>
                            <p class="text-muted mb-0">Manage guest reviews, surveys, and satisfaction analytics</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-soft-success btn-sm" onclick="autoSendSurveys()">
                                <i class="mdi mdi-send me-1"></i>Auto-Send Surveys
                            </button>
                            <a href="{{ url('feedback/list') }}" class="btn btn-soft-primary btn-sm">
                                <i class="mdi mdi-format-list-bulleted me-1"></i>View All
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
                                    <div class="avatar-sm rounded-circle bg-soft-warning d-flex align-items-center justify-content-center">
                                        <i class="mdi mdi-star text-warning font-size-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-0">{{ number_format($avgRatings->avg_overall ?? 0, 1) }}</h3>
                                    <p class="text-muted mb-0 font-size-13">Average Rating</p>
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
                                        <i class="mdi mdi-message-check text-success font-size-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-0">{{ number_format($completedFeedback) }}</h3>
                                    <p class="text-muted mb-0 font-size-13">Completed Reviews</p>
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
                                    <div class="avatar-sm rounded-circle bg-soft-info d-flex align-items-center justify-content-center">
                                        <i class="mdi mdi-phone-check text-info font-size-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-0">{{ $responseRate }}%</h3>
                                    <p class="text-muted mb-0 font-size-13">Response Rate</p>
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
                                    <div class="avatar-sm rounded-circle bg-soft-primary d-flex align-items-center justify-content-center">
                                        <i class="mdi mdi-account-group text-primary font-size-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-0">{{ number_format($completedFeedback > 0 ? round(($avgRatings->recommend_count ?? 0) / $completedFeedback * 100) : 0) }}%</h3>
                                    <p class="text-muted mb-0 font-size-13">Would Recommend</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rating Breakdown + Distribution -->
            <div class="row">
                <!-- Rating Breakdown -->
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0">
                            <h5 class="card-title mb-0"><i class="mdi mdi-star-circle me-2"></i>Rating Breakdown</h5>
                        </div>
                        <div class="card-body">
                            @php
                            $ratings = [
                                ['label' => 'Overall', 'value' => $avgRatings->avg_overall ?? 0, 'icon' => 'mdi-star'],
                                ['label' => 'Cleanliness', 'value' => $avgRatings->avg_cleanliness ?? 0, 'icon' => 'mdi-broom'],
                                ['label' => 'Service', 'value' => $avgRatings->avg_service ?? 0, 'icon' => 'mdi-head-account'],
                                ['label' => 'Food', 'value' => $avgRatings->avg_food ?? 0, 'icon' => 'mdi-food-fork-drink'],
                                ['label' => 'Value', 'value' => $avgRatings->avg_value ?? 0, 'icon' => 'mdi-cash'],
                                ['label' => 'Location', 'value' => $avgRatings->avg_location ?? 0, 'icon' => 'mdi-map-marker'],
                            ];
                            @endphp
                            @foreach($ratings as $r)
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0 me-3" style="width: 120px;">
                                    <small class="text-muted"><i class="mdi {{ $r['icon'] }} me-1"></i>{{ $r['label'] }}</small>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-warning" style="width: {{ ($r['value'] / 5) * 100 }}%"></div>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 ms-2" style="width: 40px;">
                                    <strong>{{ number_format($r['value'], 1) }}</strong>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Rating Distribution -->
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0">
                            <h5 class="card-title mb-0"><i class="mdi mdi-chart-bar me-2"></i>Rating Distribution</h5>
                        </div>
                        <div class="card-body">
                            @for($i = 5; $i >= 1; $i--)
                                @php
                                    $count = $distribution->where('overall_rating', $i)->first()->cnt ?? 0;
                                    $pct = $completedFeedback > 0 ? round(($count / $completedFeedback) * 100) : 0;
                                @endphp
                                <div class="d-flex align-items-center mb-2">
                                    <div class="flex-shrink-0 me-2" style="width: 50px;">
                                        <small>{{ $i }} <i class="mdi mdi-star text-warning" style="font-size: 12px;"></i></small>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="progress" style="height: 12px;">
                                            <div class="progress-bar bg-{{ $i >= 4 ? 'success' : ($i >= 3 ? 'warning' : 'danger') }}" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 ms-2" style="width: 60px;">
                                        <small class="text-muted">{{ $count }} ({{ $pct }}%)</small>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Feedback -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="mdi mdi-message-text me-2"></i>Recent Reviews</h5>
                            <a href="{{ url('feedback/list') }}" class="btn btn-sm btn-soft-primary">View All</a>
                        </div>
                        <div class="card-body">
                            @if($recentFeedback->count())
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Guest</th>
                                            <th>Room</th>
                                            <th>Rating</th>
                                            <th>Comments</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentFeedback as $fb)
                                        <tr>
                                            <td>
                                                <strong>{{ $fb->guest_name }}</strong>
                                                <br><small class="text-muted">{{ $fb->docid }}</small>
                                            </td>
                                            <td>{{ $fb->roomno }}</td>
                                            <td>
                                                @if($fb->survey_status === 'completed')
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="mdi mdi-star{{ $i <= $fb->overall_rating ? '' : '-outline' }}" style="color: {{ $i <= $fb->overall_rating ? '#f59e0b' : '#d1d5db' }}; font-size: 14px;"></i>
                                                    @endfor
                                                @else
                                                    <span class="badge badge-soft-secondary">{{ ucfirst($fb->survey_status) }}</span>
                                                @endif
                                            </td>
                                            <td><small>{{ Str::limit($fb->comments ?? '—', 50) }}</small></td>
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
                                            <td><small>{{ \Carbon\Carbon::parse($fb->created_at)->format('d M Y') }}</small></td>
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
                            @else
                            <div class="text-center text-muted py-4">
                                <i class="mdi mdi-star-outline font-size-48 mb-2"></i>
                                <p>No feedback received yet. Send surveys to guests!</p>
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
                    <div class="mb-3">
                        <label class="form-label">Your Response</label>
                        <textarea class="form-control" name="response" rows="4" placeholder="Type your response to the guest..." required maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-send me-1"></i>Send Response</button>
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

function autoSendSurveys() {
    if (!confirm('Send feedback surveys to all yesterday\'s checkouts?')) return;
    $.ajax({
        url: '{{ url("feedback/auto-send") }}',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
            toastr[res.success ? 'success' : 'error'](res.message);
            if (res.success) location.reload();
        }
    });
}
</script>
@endsection
