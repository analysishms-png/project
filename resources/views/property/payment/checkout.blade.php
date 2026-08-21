<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Online Payment — Analysis HMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .payment-card { max-width: 480px; margin: 60px auto; border: none; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); overflow: hidden; }
        .payment-header { background: linear-gradient(135deg, #1a237e 0%, #4a148c 100%); color: white; padding: 30px; text-align: center; }
        .payment-header h3 { margin: 0; font-size: 22px; font-weight: 600; }
        .payment-header p { margin: 5px 0 0; opacity: 0.8; font-size: 14px; }
        .payment-body { padding: 30px; }
        .amount-display { background: #f8f9fa; border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 20px; }
        .amount-display .amount { font-size: 36px; font-weight: 700; color: #1a237e; }
        .amount-display .label { font-size: 13px; color: #666; text-transform: uppercase; letter-spacing: 1px; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .detail-row:last-child { border-bottom: none; }
        .detail-row .key { color: #666; font-size: 13px; }
        .detail-row .value { font-weight: 600; font-size: 14px; }
        .pay-btn { width: 100%; padding: 14px; font-size: 16px; font-weight: 600; border: none; border-radius: 10px; background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%); color: white; cursor: pointer; transition: all 0.3s; }
        .pay-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(76,175,80,0.4); }
        .pay-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .secure-badge { text-align: center; margin-top: 15px; font-size: 12px; color: #888; }
        .secure-badge i { color: #4caf50; }
        .status-box { display: none; padding: 20px; border-radius: 10px; text-align: center; margin-top: 15px; }
        .status-box.success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .status-box.error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
        .spinner { display: none; }
        .payment-methods { display: flex; gap: 10px; margin-top: 15px; }
        .payment-methods img { height: 30px; opacity: 0.6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="payment-card">
            <div class="payment-header">
                <h3><i class="fas fa-hotel"></i> Analysis HMS</h3>
                <p>Secure Online Payment</p>
            </div>
            <div class="payment-body">
                <div class="amount-display">
                    <div class="label">Amount to Pay</div>
                    <div class="amount">₹{{ number_format($amount, 2) }}</div>
                </div>

                <div class="detail-row">
                    <span class="key">Purpose</span>
                    <span class="value">{{ ucfirst(str_replace('_', ' ', $purpose)) }}</span>
                </div>
                @if($roomno)
                <div class="detail-row">
                    <span class="key">Room No</span>
                    <span class="value">{{ $roomno }}</span>
                </div>
                @endif
                @if($guestname)
                <div class="detail-row">
                    <span class="key">Guest</span>
                    <span class="value">{{ $guestname }}</span>
                </div>
                @endif
                @if($folio_no)
                <div class="detail-row">
                    <span class="key">Folio No</span>
                    <span class="value">{{ $folio_no }}</span>
                </div>
                @endif

                <button id="payBtn" class="pay-btn" onclick="initiatePayment()">
                    <span class="btn-text"><i class="fas fa-lock"></i> Pay ₹{{ number_format($amount, 2) }}</span>
                    <span class="spinner"><i class="fas fa-spinner fa-spin"></i> Processing...</span>
                </button>

                <div id="statusBox" class="status-box"></div>

                <div class="secure-badge">
                    <i class="fas fa-shield-alt"></i> Secured by Razorpay | 256-bit SSL Encrypted
                </div>

                <div class="payment-methods" style="justify-content:center; margin-top:15px;">
                    <img src="https://razorpay.com/assets/razorpay-glyph.svg" alt="Razorpay" title="Powered by Razorpay">
                </div>
            </div>
        </div>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var razorpayKey = '{{ $razorpay_key }}';
        var amount = {{ $amount }};
        var purpose = '{{ $purpose }}';
        var docid = '{{ $docid }}';
        var roomno = '{{ $roomno }}';
        var guestname = '{{ $guestname }}';
        var folioNo = '{{ $folio_no }}';

        function initiatePayment() {
            $('#payBtn').prop('disabled', true).find('.btn-text').hide().end().find('.spinner').show();
            $('#statusBox').hide();

            $.ajax({
                url: '{{ route("payment.createOrder") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    amount: amount,
                    purpose: purpose
                },
                success: function(response) {
                    if (response.success) {
                        openRazorpay(response);
                    } else {
                        showStatus('error', 'Failed to create order: ' + (response.error || 'Unknown error'));
                        resetBtn();
                    }
                },
                error: function(xhr) {
                    showStatus('error', 'Server error. Please try again.');
                    resetBtn();
                }
            });
        }

        function openRazorpay(order) {
            var options = {
                key: razorpayKey,
                amount: order.amount,
                currency: order.currency,
                name: 'Analysis HMS',
                description: purpose.replace('_', ' ').toUpperCase(),
                order_id: order.order_id,
                handler: function(response) {
                    verifyPayment(response);
                },
                prefill: {
                    name: guestname || '',
                    contact: '',
                    email: ''
                },
                theme: {
                    color: '#1a237e'
                },
                modal: {
                    ondismiss: function() {
                        showStatus('error', 'Payment cancelled by user.');
                        resetBtn();
                    }
                }
            };

            var rzp = new Razorpay(options);
            rzp.on('payment.failed', function(response) {
                showStatus('error', 'Payment failed: ' + (response.error.description || 'Unknown error'));
                resetBtn();
            });
            rzp.open();
        }

        function verifyPayment(response) {
            $.ajax({
                url: '{{ route("payment.verify") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_signature: response.razorpay_signature,
                    amount: amount,
                    purpose: purpose,
                    docid: docid,
                    roomno: roomno,
                    guestname: guestname,
                    folio_no: folioNo
                },
                success: function(res) {
                    if (res.success) {
                        showStatus('success',
                            '<i class="fas fa-check-circle" style="font-size:48px;margin-bottom:10px;display:block"></i>' +
                            '<strong>Payment Successful!</strong><br>' +
                            'Transaction ID: ' + res.transaction_id + '<br>' +
                            'Amount: ₹' + res.amount + '<br>' +
                            '<small>Payment ID: ' + res.payment_id + '</small>'
                        );
                    } else {
                        showStatus('error', res.message);
                    }
                    resetBtn();
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Verification failed.';
                    showStatus('error', msg);
                    resetBtn();
                }
            });
        }

        function showStatus(type, message) {
            $('#statusBox').removeClass('success error').addClass(type).html(message).show();
        }

        function resetBtn() {
            $('#payBtn').prop('disabled', false).find('.btn-text').show().end().find('.spinner').hide();
        }
    </script>
</body>
</html>
