@extends('layouts.network')

@section('title', 'My Subscriptions - Querentia')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">My Subscriptions</h1>
    <p class="text-gray-600 mb-8">Manage your subscription and payment history</p>

    <!-- Current Subscription -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Current Plan</h2>
                <p class="text-gray-600">
                    @if($user->isSubscribed())
                        Active until {{ $user->subscription_ends_at->format('F d, Y') }}
                    @else
                        Free plan
                    @endif
                </p>
            </div>
            <div class="text-right">
                <span class="inline-block px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg font-bold">
                    {{ ucfirst($user->subscription_tier) }}
                </span>
                @if($user->isSubscribed())
                <p class="text-sm text-gray-500 mt-2">Auto-renews monthly</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">Storage</p>
                <p class="text-2xl font-bold text-gray-900">
                    @if($user->isPro())
                        1 GB
                    @elseif($user->subscription_tier === 'basic')
                        256 MB
                    @else
                        100 MB
                    @endif
                </p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">AI Credits</p>
                <p class="text-2xl font-bold text-gray-900">
                    @if($user->isPro())
                        Unlimited
                    @elseif($user->subscription_tier === 'basic')
                        10/month
                    @else
                        0
                    @endif
                </p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">Monthly Price</p>
                <p class="text-2xl font-bold text-gray-900">
                    @if($user->isPro())
                        ₦{{ config('paystack.pro_price', 4900) / 100 }}
                    @elseif($user->subscription_tier === 'basic')
                        ₦{{ config('paystack.basic_price', 2900) / 100 }}
                    @else
                        ₦0
                    @endif
                </p>
            </div>
        </div>

        @if($user->isSubscribed())
        <div class="mt-6 pt-6 border-t">
            <button onclick="cancelSubscription()"
                    class="px-6 py-3 border border-red-300 text-red-600 rounded-lg font-semibold hover:bg-red-50">
                <i class="fas fa-times-circle mr-2"></i>Cancel Subscription
            </button>
            <p class="text-sm text-gray-500 mt-2">
                You'll continue to have access until the end of your billing period.
            </p>
        </div>
        @endif
    </div>

    <!-- Subscription History -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Subscription History</h2>
        
        @if($subscriptions->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 font-semibold text-gray-900">Plan</th>
                        <th class="text-left py-3 font-semibold text-gray-900">Amount</th>
                        <th class="text-left py-3 font-semibold text-gray-900">Status</th>
                        <th class="text-left py-3 font-semibold text-gray-900">Start Date</th>
                        <th class="text-left py-3 font-semibold text-gray-900">End Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subscriptions as $subscription)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-4">
                            <span class="font-medium">{{ ucfirst($subscription->plan) }}</span>
                        </td>
                        <td class="py-4">₦{{ number_format($subscription->amount, 2) }}</td>
                        <td class="py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                @if($subscription->status === 'active') bg-green-100 text-green-800
                                @elseif($subscription->status === 'canceled') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst($subscription->status) }}
                            </span>
                        </td>
                        <td class="py-4">
                            {{ $subscription->starts_at?->format('M d, Y') ?? 'N/A' }}
                        </td>
                        <td class="py-4">
                            {{ $subscription->ends_at?->format('M d, Y') ?? 'N/A' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $subscriptions->links() }}
        </div>
        @else
        <div class="text-center py-8">
            <i class="fas fa-history text-3xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">No subscription history</p>
        </div>
        @endif
    </div>

    <!-- Transaction History -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Transaction History</h2>
        
        @if($transactions->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 font-semibold text-gray-900">Reference</th>
                        <th class="text-left py-3 font-semibold text-gray-900">Amount</th>
                        <th class="text-left py-3 font-semibold text-gray-900">Status</th>
                        <th class="text-left py-3 font-semibold text-gray-900">Date</th>
                        <th class="text-left py-3 font-semibold text-gray-900">Method</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-4">
                            <span class="font-mono text-sm">{{ $transaction->reference }}</span>
                        </td>
                        <td class="py-4">₦{{ number_format($transaction->amount_paid, 2) }}</td>
                        <td class="py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                @if($transaction->status === 'success') bg-green-100 text-green-800
                                @elseif($transaction->status === 'failed') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </td>
                        <td class="py-4">
                            {{ $transaction->created_at->format('M d, Y h:i A') }}
                        </td>
                        <td class="py-4 capitalize">
                            {{ $transaction->channel ?? 'Card' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
        @else
        <div class="text-center py-8">
            <i class="fas fa-receipt text-3xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">No transactions yet</p>
        </div>
        @endif
    </div>
</div>

<script>
    function cancelSubscription() {
        if (!confirm('Are you sure you want to cancel your subscription? You will lose access to premium features at the end of your billing period.')) {
            return;
        }
        
        const activeSubscription = {{ $subscriptions->firstWhere('status', 'active') ? $subscriptions->firstWhere('status', 'active')->id : 'null' }};
        
        if (!activeSubscription) {
            alert('No active subscription found');
            return;
        }
        
        fetch(`/subscription/${activeSubscription}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Subscription cancelled successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
</script>
@endsection