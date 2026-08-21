<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Guest Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', system-ui, sans-serif; }
        .survey-card { max-width: 480px; margin: 20px auto; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); overflow: hidden; }
        .survey-header { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 32px 24px; text-align: center; }
        .survey-header h2 { font-size: 20px; margin: 0; font-weight: 600; }
        .survey-header .subtitle { font-size: 13px; opacity: 0.9; margin-top: 4px; }
        .rating-group { margin: 16px 20px; }
        .rating-group label { font-size: 14px; font-weight: 500; color: #1e293b; margin-bottom: 8px; display: block; }
        .star-rating { display: flex; gap: 4px; }
        .star-rating input { display: none; }
        .star-rating label { cursor: pointer; font-size: 28px; color: #d1d5db; transition: color 0.2s; }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label { color: #f59e0b; }
        .btn-submit { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none; border-radius: 10px; padding: 14px; font-size: 16px; font-weight: 600; width: 100%; }
        .btn-submit:hover { background: linear-gradient(135deg, #d97706, #b45309); }
        .divider { border-top: 1px solid #e2e8f0; margin: 8px 20px; }
        .powered-by { text-align: center; padding: 12px; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="survey-card">
        <div class="survey-header">
            <h2><i class="mdi mdi-star me-1"></i>We'd Love Your Feedback!</h2>
            <div class="subtitle">Help us improve your experience</div>
        </div>

        <!-- Guest Info -->
        <div style="background: #f8fafc; padding: 16px 20px; border-bottom: 1px solid #e2e8f0;">
            <div style="display: flex; justify-content: space-between; font-size: 13px;">
                <span style="color: #64748b;">Guest</span>
                <strong>{{ $feedback->guest_name }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 13px; margin-top: 4px;">
                <span style="color: #64748b;">Room</span>
                <strong>{{ $feedback->roomno }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 13px; margin-top: 4px;">
                <span style="color: #64748b;">Stay</span>
                <strong>{{ $feedback->checkin_date }} — {{ $feedback->checkout_date }}</strong>
            </div>
        </div>

        <form id="surveyForm" action="{{ url('feedback/survey/' . $feedback->id) }}" method="POST">
            @csrf

            @if($errors->any())
            <div class="m-3">
                <div class="alert alert-danger" style="border-radius: 10px; font-size: 13px;">
                    Please rate all categories (1-5 stars).
                </div>
            </div>
            @endif

            <!-- Overall Rating -->
            <div class="rating-group">
                <label><i class="mdi mdi-star me-1"></i>Overall Experience</label>
                <div class="star-rating">
                    @for($i = 5; $i >= 1; $i--)
                    <input type="radio" id="overall{{ $i }}" name="overall_rating" value="{{ $i }}" required>
                    <label for="overall{{ $i }}"><i class="mdi mdi-star"></i></label>
                    @endfor
                </div>
            </div>

            <div class="divider"></div>

            <!-- Cleanliness -->
            <div class="rating-group">
                <label><i class="mdi mdi-broom me-1"></i>Cleanliness</label>
                <div class="star-rating">
                    @for($i = 5; $i >= 1; $i--)
                    <input type="radio" id="clean{{ $i }}" name="cleanliness_rating" value="{{ $i }}" required>
                    <label for="clean{{ $i }}"><i class="mdi mdi-star"></i></label>
                    @endfor
                </div>
            </div>

            <div class="divider"></div>

            <!-- Service -->
            <div class="rating-group">
                <label><i class="mdi mdi-head-account me-1"></i>Staff Service</label>
                <div class="star-rating">
                    @for($i = 5; $i >= 1; $i--)
                    <input type="radio" id="service{{ $i }}" name="service_rating" value="{{ $i }}" required>
                    <label for="service{{ $i }}"><i class="mdi mdi-star"></i></label>
                    @endfor
                </div>
            </div>

            <div class="divider"></div>

            <!-- Food -->
            <div class="rating-group">
                <label><i class="mdi mdi-food-fork-drink me-1"></i>Food & Dining</label>
                <div class="star-rating">
                    @for($i = 5; $i >= 1; $i--)
                    <input type="radio" id="food{{ $i }}" name="food_rating" value="{{ $i }}" required>
                    <label for="food{{ $i }}"><i class="mdi mdi-star"></i></label>
                    @endfor
                </div>
            </div>

            <div class="divider"></div>

            <!-- Value -->
            <div class="rating-group">
                <label><i class="mdi mdi-cash me-1"></i>Value for Money</label>
                <div class="star-rating">
                    @for($i = 5; $i >= 1; $i--)
                    <input type="radio" id="value{{ $i }}" name="value_rating" value="{{ $i }}" required>
                    <label for="value{{ $i }}"><i class="mdi mdi-star"></i></label>
                    @endfor
                </div>
            </div>

            <div class="divider"></div>

            <!-- Location -->
            <div class="rating-group">
                <label><i class="mdi mdi-map-marker me-1"></i>Location</label>
                <div class="star-rating">
                    @for($i = 5; $i >= 1; $i--)
                    <input type="radio" id="loc{{ $i }}" name="location_rating" value="{{ $i }}" required>
                    <label for="loc{{ $i }}"><i class="mdi mdi-star"></i></label>
                    @endfor
                </div>
            </div>

            <div class="divider"></div>

            <!-- Comments -->
            <div class="rating-group">
                <label><i class="mdi mdi-comment-text me-1"></i>Additional Comments</label>
                <textarea class="form-control" name="comments" rows="3" placeholder="Tell us more about your experience..." maxlength="1000" style="border-radius: 10px;"></textarea>
            </div>

            <!-- Recommend -->
            <div class="rating-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="would_recommend" value="1" checked id="recommend">
                    <label class="form-check-label" for="recommend">I would recommend this hotel to others</label>
                </div>
            </div>

            <!-- Submit -->
            <div style="padding: 16px 20px 24px;">
                <button type="submit" class="btn btn-submit">
                    <i class="mdi mdi-send me-1"></i>Submit Feedback
                </button>
            </div>
        </form>

        <div class="powered-by">Powered by <strong>Analysis HMS</strong></div>
    </div>
</body>
</html>
