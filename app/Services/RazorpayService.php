<?php

namespace App\Services;

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected $api;
    protected $keyId;
    protected $keySecret;

    public function __construct()
    {
        $this->keyId = config('services.razorpay.key');
        $this->keySecret = config('services.razorpay.secret');
        $this->api = new Api($this->keyId, $this->keySecret);
    }

    /**
     * Create a new order
     */
    public function createOrder(float $amount, string $currency = 'INR', array $meta = []): array
    {
        $receipt = 'ORD_' . auth()->id() . '_' . time();

        $orderData = [
            'receipt' => $receipt,
            'amount' => (int) ($amount * 100), // Convert to paise
            'currency' => $currency,
            'notes' => $meta,
        ];

        try {
            $order = $this->api->order->create($orderData);
            return [
                'success' => true,
                'order_id' => $order['id'],
                'amount' => $order['amount'],
                'currency' => $order['currency'],
                'receipt' => $order['receipt'],
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay order creation failed', [
                'error' => $e->getMessage(),
                'amount' => $amount,
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment signature
     */
    public function verifyPayment(string $orderId, string $paymentId, string $signature): bool
    {
        try {
            $attributes = [
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ];

            $this->api->utility->verifySignedUrl($attributes);
            return true;
        } catch (SignatureVerificationError $e) {
            Log::warning('Razorpay signature verification failed', [
                'order_id' => $orderId,
                'payment_id' => $paymentId,
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Razorpay verification error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Fetch payment details
     */
    public function fetchPayment(string $paymentId): ?array
    {
        try {
            $payment = $this->api->payment->fetch($paymentId);
            return [
                'id' => $payment['id'],
                'amount' => $payment['amount'] / 100, // Convert from paise
                'currency' => $payment['currency'],
                'status' => $payment['status'],
                'method' => $payment['method'] ?? 'unknown',
                'description' => $payment['description'] ?? '',
                'created_at' => $payment['created_at'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay fetch payment failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Create refund
     */
    public function createRefund(string $paymentId, float $amount, string $notes = ''): array
    {
        try {
            $refundData = [
                'amount' => (int) ($amount * 100),
                'notes' => ['notes' => $notes],
            ];

            $refund = $this->api->payment->fetch($paymentId)->refund($refundData);
            return [
                'success' => true,
                'refund_id' => $refund['id'],
                'amount' => $refund['amount'] / 100,
                'status' => $refund['status'],
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay refund failed', [
                'payment_id' => $paymentId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get client key for frontend
     */
    public function getClientKey(): string
    {
        return $this->keyId;
    }
}
