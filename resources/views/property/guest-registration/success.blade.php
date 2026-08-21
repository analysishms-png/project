<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Complete</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', system-ui, sans-serif; }
        .success-card { max-width: 480px; margin: 40px auto; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); overflow: hidden; text-align: center; }
        .success-header { background: linear-gradient(135deg, #059669, #10b981); color: white; padding: 40px 24px; }
        .success-icon { font-size: 64px; margin-bottom: 16px; }
        .success-body { padding: 32px 24px; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .detail-label { color: #64748b; }
        .detail-value { font-weight: 600; color: #1e293b; }
        .powered-by { text-align: center; padding: 12px; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-header">
            <div class="success-icon"><i class="ri-checkbox-circle-fill"></i></div>
            <h2 style="margin:0;">Registration Complete!</h2>
            <p style="margin:8px 0 0;opacity:0.9;">Thank you, {{ $guestName }}</p>
        </div>
        <div class="success-body">
            <div class="detail-row">
                <span class="detail-label">Reservation No.</span>
                <span class="detail-value">{{ $reservationNo }}</span>
            </div>
            @if($roomType)
            <div class="detail-row">
                <span class="detail-label">Room Type</span>
                <span class="detail-value">{{ $roomType }}</span>
            </div>
            @endif
            @if($arrivalDate)
            <div class="detail-row">
                <span class="detail-label">Arrival Date</span>
                <span class="detail-value">{{ $arrivalDate }}</span>
            </div>
            @endif
            <div class="mt-4">
                <p class="text-muted" style="font-size:13px;">
                    <i class="ri-information-line me-1"></i>
                    Your details have been saved. Please bring a valid ID proof for verification at check-in.
                </p>
            </div>
            <div class="mt-3">
                <p style="font-size:14px;color:#475569;">
                    We look forward to welcoming you!<br>
                    <strong>Have a wonderful stay.</strong>
                </p>
            </div>
        </div>
        <div class="powered-by">Powered by <strong>Analysis HMS</strong></div>
    </div>
</body>
</html>
