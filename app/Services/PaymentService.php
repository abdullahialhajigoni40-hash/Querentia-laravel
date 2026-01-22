<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $paystackSecretKey;
    protected $paystackPublicKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->paystackSecretKey = config('paystack.secretKey');
        $this->paystackPublicKey = config('paystack.publicKey');
        $this->baseUrl = config('paystack.paymentUrl');
    }

    /**
     * Initialize subscription payment
     */
    public function initializeSubscription(User $user, string $plan, string $email, float $amount, array $metadata = [])
    {
        $planPrices = [
            'basic' => config('paystack.basic_price', 2900),
            'pro' => config('paystack.pro_price', 4900),
        ];

        $amountInKobo = $amount * 100; // Convert to kobo

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->paystackSecretKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/transaction/initialize', [
            'email' => $email,
            'amount' => $amountInKobo,
            'currency' => 'NGN',
            'reference' => $this->generateReference(),
            'callback_url' => route('payment.callback'),
            'metadata' => array_merge($metadata, [
                'user_id' => $user->id,
                'plan' => $plan,
                'type' => 'subscription',
            ]),
        ]);

        $data = $response->json();

        if (!$data['status']) {
            throw new \Exception('Payment initialization failed: ' . ($data['message'] ?? 'Unknown error'));
        }

        // Create transaction record
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'reference' => $data['data']['reference'],
            'type' => 'subscription',
            'status' => 'pending',
            'amount' => $amount,
            'currency' => 'NGN',
            'email' => $email,
            'metadata' => $metadata,
        ]);

        // Create subscription record
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan' => $plan,
            'status' => 'pending',
            'amount' => $amount,
            'currency' => 'NGN',
            'paystack_reference' => $data['data']['reference'],
            'paystack_response' => $data,
        ]);

        $transaction->update(['subscription_id' => $subscription->id]);

        return [
            'authorization_url' => $data['data']['authorization_url'],
            'reference' => $data['data']['reference'],
            'access_code' => $data['data']['access_code'],
            'transaction' => $transaction,
            'subscription' => $subscription,
        ];
    }

    /**
     * Verify payment
     */
    public function verifyPayment(string $reference)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->paystackSecretKey,
        ])->get($this->baseUrl . '/transaction/verify/' . $reference);

        $data = $response->json();

        if (!$data['status']) {
            throw new \Exception('Payment verification failed: ' . ($data['message'] ?? 'Unknown error'));
        }

        return $data['data'];
    }

    /**
     * Process successful payment
     */
    public function processSuccessfulPayment(array $paymentData)
    {
        $reference = $paymentData['reference'];
        $transaction = Transaction::where('reference', $reference)->first();

        if (!$transaction) {
            throw new \Exception('Transaction not found');
        }

        if ($transaction->isSuccessful()) {
            return $transaction; // Already processed
        }

        // Update transaction
        $transaction->markAsPaid(
            $paymentData['amount'] / 100, // Convert from kobo
            $paymentData['channel'],
            $paymentData
        );

        // Update subscription
        $subscription = $transaction->subscription;
        if ($subscription) {
            $subscription->update([
                'status' => 'active',
                'paystack_customer_code' => $paymentData['customer']['customer_code'] ?? null,
                'paystack_response' => $paymentData,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
            ]);

            // Update user subscription
            $user = $transaction->user;
            $user->update([
                'subscription_tier' => $subscription->plan,
                'subscription_ends_at' => now()->addMonth(),
            ]);
        }

        // Send email notification
        $this->sendPaymentConfirmationEmail($transaction);

        return $transaction;
    }

    /**
     * Create subscription plan
     */
    public function createSubscriptionPlan(string $name, string $interval, float $amount)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->paystackSecretKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/plan', [
            'name' => $name,
            'interval' => $interval, // daily, weekly, monthly, annually
            'amount' => $amount * 100,
            'currency' => 'NGN',
        ]);

        return $response->json();
    }

    /**
     * Charge returning customer
     */
    public function chargeReturningCustomer(User $user, float $amount, string $authorizationCode)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->paystackSecretKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/transaction/charge_authorization', [
            'email' => $user->email,
            'amount' => $amount * 100,
            'authorization_code' => $authorizationCode,
            'reference' => $this->generateReference(),
        ]);

        return $response->json();
    }

    /**
     * Generate unique reference
     */
    private function generateReference()
    {
        return 'QR_' . time() . '_' . rand(1000, 9999);
    }

    /**
     * Send payment confirmation email
     */
    private function sendPaymentConfirmationEmail(Transaction $transaction)
    {
        // Implement email sending logic
        // You can use Laravel Mail or a notification
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStats()
    {
        $totalRevenue = Transaction::where('status', 'success')->sum('amount_paid');
        $monthlyRevenue = Transaction::where('status', 'success')
            ->whereMonth('created_at', now()->month)
            ->sum('amount_paid');
        
        $activeSubscriptions = Subscription::where('status', 'active')
            ->where('ends_at', '>', now())
            ->count();

        return [
            'total_revenue' => $totalRevenue,
            'monthly_revenue' => $monthlyRevenue,
            'active_subscriptions' => $activeSubscriptions,
            'currency' => 'NGN',
        ];
    }

    /**
     * Handle webhook
     */
    public function handleWebhook(array $payload)
    {
        $event = $payload['event'];
        $data = $payload['data'];

        switch ($event) {
            case 'charge.success':
                return $this->processSuccessfulPayment($data);
                
            case 'subscription.create':
            case 'subscription.disable':
                // Handle subscription events
                break;
                
            case 'transfer.success':
                // Handle transfer events
                break;
        }

        return null;
    }
}