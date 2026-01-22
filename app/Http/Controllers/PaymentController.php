<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PaymentService;
use App\Models\Subscription;
use App\Models\Transaction;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        $this->middleware('auth')->except(['callback', 'webhook']);
    }

    /**
     * Show pricing page
     */
    public function pricing()
    {
        $user = Auth::user();
        $currentPlan = $user->subscription_tier;
        
        return view('payment.pricing', [
            'currentPlan' => $currentPlan,
            'basicPrice' => config('paystack.basic_price', 2900) / 100,
            'proPrice' => config('paystack.pro_price', 4900) / 100,
            'user' => $user,
        ]);
    }

    /**
     * Initialize payment
     */
    public function initialize(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:basic,pro',
        ]);

        $user = Auth::user();
        $plan = $request->plan;
        
        $prices = [
            'basic' => config('paystack.basic_price', 2900) / 100,
            'pro' => config('paystack.pro_price', 4900) / 100,
        ];

        try {
            $result = $this->paymentService->initializeSubscription(
                $user,
                $plan,
                $user->email,
                $prices[$plan],
                [
                    'plan_name' => ucfirst($plan) . ' Plan',
                    'user_email' => $user->email,
                ]
            );

            return response()->json([
                'success' => true,
                'authorization_url' => $result['authorization_url'],
                'reference' => $result['reference'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Payment callback
     */
    public function callback(Request $request)
    {
        $reference = $request->query('reference');
        
        if (!$reference) {
            return redirect()->route('payment.failed')
                ->with('error', 'Payment reference not found');
        }

        try {
            // Verify payment
            $paymentData = $this->paymentService->verifyPayment($reference);
            
            if ($paymentData['status'] === 'success') {
                // Process successful payment
                $transaction = $this->paymentService->processSuccessfulPayment($paymentData);
                
                return redirect()->route('payment.success')
                    ->with('success', 'Payment successful!')
                    ->with('transaction', $transaction);
            } else {
                return redirect()->route('payment.failed')
                    ->with('error', 'Payment was not successful');
            }

        } catch (\Exception $e) {
            Log::error('Payment callback error: ' . $e->getMessage());
            
            return redirect()->route('payment.failed')
                ->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Paystack webhook
     */
    public function webhook(Request $request)
    {
        // Verify webhook signature
        $secret = config('paystack.webhook_secret');
        $signature = $request->header('x-paystack-signature');
        
        if (!$signature) {
            return response()->json(['error' => 'No signature'], 400);
        }

        $payload = $request->getContent();
        $computedSignature = hash_hmac('sha512', $payload, $secret);

        if (!hash_equals($computedSignature, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $payload = json_decode($payload, true);
        
        try {
            $this->paymentService->handleWebhook($payload);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Payment success page
     */
    public function success(Request $request)
    {
        $transaction = $request->session()->get('transaction');
        
        return view('payment.success', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * Payment failed page
     */
    public function failed(Request $request)
    {
        $error = $request->session()->get('error');
        
        return view('payment.failed', [
            'error' => $error,
        ]);
    }

    /**
     * User subscriptions
     */
    public function subscriptions()
    {
        $user = Auth::user();
        $subscriptions = $user->subscriptions()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        $transactions = $user->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('payment.subscriptions', [
            'subscriptions' => $subscriptions,
            'transactions' => $transactions,
            'user' => $user,
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(Request $request, Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$subscription->isActive()) {
            return back()->with('error', 'Subscription is not active');
        }

        $subscription->cancel();
        
        // Update user subscription tier
        Auth::user()->update([
            'subscription_tier' => 'free',
            'subscription_ends_at' => null,
        ]);

        return back()->with('success', 'Subscription cancelled successfully');
    }

    /**
     * Get payment statistics (admin)
     */
    public function stats(Request $request)
    {
        if (!Auth::user()->is_admin) {
            abort(403);
        }

        $stats = $this->paymentService->getPaymentStats();
        
        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}