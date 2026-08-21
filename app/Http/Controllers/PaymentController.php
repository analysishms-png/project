<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\RazorpayService;
use App\Models\Paycharge;
use App\Models\CashCardTrans;

class PaymentController extends Controller
{
    protected $razorpay;
    protected $propertyid;

    public function __construct(RazorpayService $razorpay)
    {
        $this->razorpay = $razorpay;
        $this->propertyid = session('propertyid') ?? 103;
    }

    /**
     * Show payment checkout page
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'purpose' => 'required|string|max:50',
            'docid' => 'nullable|string',
            'roomno' => 'nullable|string',
            'guestname' => 'nullable|string',
            'folio_no' => 'nullable|string',
        ]);

        return view('property.payment.checkout', [
            'amount' => $request->amount,
            'purpose' => $request->purpose,
            'docid' => $request->docid ?? '',
            'roomno' => $request->roomno ?? '',
            'guestname' => $request->guestname ?? '',
            'folio_no' => $request->folio_no ?? '',
            'razorpay_key' => $this->razorpay->getClientKey(),
        ]);
    }

    /**
     * Create Razorpay order via AJAX
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'purpose' => 'required|string',
        ]);

        $result = $this->razorpay->createOrder(
            $request->amount,
            'INR',
            [
                'propertyid' => $this->propertyid,
                'purpose' => $request->purpose,
                'user' => auth()->user()->u_name ?? 'guest',
            ]
        );

        return response()->json($result);
    }

    /**
     * Verify payment and record transaction
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'amount' => 'required|numeric',
            'purpose' => 'required|string',
            'docid' => 'nullable|string',
            'roomno' => 'nullable|string',
            'guestname' => 'nullable|string',
            'folio_no' => 'nullable|string',
        ]);

        // Verify signature
        $isValid = $this->razorpay->verifyPayment(
            $request->razorpay_order_id,
            $request->razorpay_payment_id,
            $request->razorpay_signature
        );

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed!',
            ], 400);
        }

        // Fetch payment details
        $paymentDetails = $this->razorpay->fetchPayment($request->razorpay_payment_id);

        // Record in database
        DB::beginTransaction();
        try {
            $paymentMethod = strtoupper($paymentDetails['method'] ?? 'ONLINE');
            $currentTime = now()->format('Y-m-d H:i:s');

            // Generate docid for paycharge
            $vprefix = 'ONP';
            $sno = DB::table('paycharge')
                ->where('propertyid', $this->propertyid)
                ->where('vtype', 'ADV')
                ->max('sno');
            $newSno = ($sno ?? 0) + 1;

            $docid = $this->propertyid . 'ADV‎ ‎ ' . $vprefix . '‎ ‎ ‎ ‎ ' . $newSno;

            // Insert paycharge record
            Paycharge::create([
                'propertyid' => $this->propertyid,
                'docid' => $docid,
                'vno' => $newSno,
                'vdate' => now()->format('Y-m-d'),
                'sno' => '1',
                'sno1' => '1',
                'vtype' => 'ADV',
                'vtime' => $currentTime,
                'vprefix' => $vprefix,
                'name' => $request->guestname ?? 'ONLINE PAYMENT',
                'roomno' => $request->roomno ?? '',
                'amtcr' => number_format($request->amount, 2),
                'amtdr' => '0.00',
                'paymode' => 'ONLINE',
                'refdocid' => $request->docid ?? '',
                'folionodocid' => $request->folio_no ?? '',
                'u_entdt' => $currentTime,
                'u_name' => auth()->user()->u_name ?? 'system',
                'u_ae' => 'a',
                'comments' => 'Online payment via Razorpay | Order: ' . $request->razorpay_order_id . ' | Payment: ' . $request->razorpay_payment_id,
            ]);

            // Insert suntran (accounting entry)
            DB::table('suntran')->insert([
                'propertyid' => $this->propertyid,
                'docid' => $docid,
                'sno' => '1',
                'vdate' => now()->format('Y-m-d'),
                'vtime' => $currentTime,
                'vtype' => 'ADV',
                'vprefix' => $vprefix,
                'vno' => $newSno,
                'sundry_code' => $this->propertyid . '101',
                'amtdr' => '0.00',
                'amtcr' => number_format($request->amount, 2),
                'paycode' => 'ONLINE',
                'u_entdt' => $currentTime,
                'u_name' => auth()->user()->u_name ?? 'system',
                'u_ae' => 'a',
                'comments' => 'Online payment via Razorpay',
            ]);

            DB::commit();

            // Log activity
            Log::info('Online payment recorded', [
                'propertyid' => $this->propertyid,
                'docid' => $docid,
                'amount' => $request->amount,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'user' => auth()->user()->u_name ?? 'guest',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment successful! Transaction recorded.',
                'transaction_id' => $docid,
                'payment_id' => $request->razorpay_payment_id,
                'amount' => number_format($request->amount, 2),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment recording failed', [
                'error' => $e->getMessage(),
                'razorpay_payment_id' => $request->razorpay_payment_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verified but recording failed. Contact support. Ref: ' . $request->razorpay_payment_id,
            ], 500);
        }
    }

    /**
     * Razorpay webhook handler
     */
    public function webhook(Request $request)
    {
        $webhookSecret = config('services.razorpay.webhook_secret');

        // Verify webhook signature
        $signature = $request->header('X-Razorpay-Signature');
        $body = $request->getContent();

        try {
            $utils = new \Razorpay\Api\Utility(array(
                $this->razorpay->getClientKey(),
                config('services.razorpay.secret')
            ));

            $isValid = $utils->verifyWebhookSignature($body, $signature, $webhookSecret);

            if (!$isValid) {
                Log::warning('Invalid Razorpay webhook signature');
                return response()->json(['status' => 'invalid_signature'], 400);
            }

            $payload = json_decode($body, true);
            $event = $payload['event'] ?? '';

            Log::info('Razorpay webhook received', ['event' => $event]);

            // Handle payment.captured event
            if ($event === 'payment.captured') {
                $payment = $payload['payload']['payment']['entity'] ?? null;
                if ($payment) {
                    Log::info('Payment captured', [
                        'payment_id' => $payment['id'],
                        'amount' => $payment['amount'] / 100,
                    ]);
                }
            }

            // Handle payment.failed event
            if ($event === 'payment.failed') {
                $payment = $payload['payload']['payment']['entity'] ?? null;
                if ($payment) {
                    Log::warning('Payment failed', [
                        'payment_id' => $payment['id'],
                        'error' => $payment['error_description'] ?? 'Unknown',
                    ]);
                }
            }

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('Webhook processing error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Refund payment
     */
    public function refund(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'reason' => 'nullable|string',
        ]);

        $result = $this->razorpay->createRefund(
            $request->payment_id,
            $request->amount,
            $request->reason ?? 'Refund requested'
        );

        if ($result['success']) {
            // Record refund in paycharge
            DB::beginTransaction();
            try {
                $currentTime = now()->format('Y-m-d H:i:s');
                $sno = DB::table('paycharge')
                    ->where('propertyid', $this->propertyid)
                    ->where('vtype', 'REF')
                    ->max('sno');
                $newSno = ($sno ?? 0) + 1;
                $docid = $this->propertyid . 'REF‎ ‎ ONP‎ ‎ ‎ ‎ ' . $newSno;

                Paycharge::create([
                    'propertyid' => $this->propertyid,
                    'docid' => $docid,
                    'vno' => $newSno,
                    'vdate' => now()->format('Y-m-d'),
                    'sno' => '1',
                    'sno1' => '1',
                    'vtype' => 'REF',
                    'vtime' => $currentTime,
                    'vprefix' => 'ONP',
                    'name' => 'ONLINE REFUND',
                    'amtcr' => '0.00',
                    'amtdr' => number_format($request->amount, 2),
                    'paymode' => 'ONLINE',
                    'u_entdt' => $currentTime,
                    'u_name' => auth()->user()->u_name ?? 'system',
                    'u_ae' => 'a',
                    'comments' => 'Refund | Refund ID: ' . $result['refund_id'] . ' | ' . ($request->reason ?? ''),
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Refund processed! Refund ID: ' . $result['refund_id'],
                    'refund_id' => $result['refund_id'],
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Refund processed but recording failed. Refund ID: ' . $result['refund_id'],
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Refund failed: ' . ($result['error'] ?? 'Unknown error'),
        ], 400);
    }
}
