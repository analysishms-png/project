<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', system-ui, sans-serif; }
        .error-card { max-width: 480px; margin: 60px auto; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); overflow: hidden; text-align: center; }
        .error-header { background: linear-gradient(135deg, #dc2626, #ef4444); color: white; padding: 40px 24px; }
        .error-icon { font-size: 64px; margin-bottom: 16px; }
        .error-body { padding: 32px 24px; }
        .powered-by { text-align: center; padding: 12px; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-header">
            <div class="error-icon"><i class="ri-error-warning-fill"></i></div>
            <h2 style="margin:0;">Reservation Not Found</h2>
        </div>
        <div class="error-body">
            <p class="text-muted">We couldn't find reservation <strong>{{ $reservationNo }}</strong>.</p>
            <p style="font-size:13px;color:#64748b;">
                Please check your reservation number and try again.<br>
                If you need help, please contact the hotel directly.
            </p>
        </div>
        <div class="powered-by">Powered by <strong>Analysis HMS</strong></div>
    </div>
</body>
</html>
