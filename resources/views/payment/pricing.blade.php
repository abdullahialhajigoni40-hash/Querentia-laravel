@extends('layouts.network')

@section('title', 'Pricing - Querentia')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Upgrade Your Research Journey</h1>
        <p class="text-xl text-gray-600">Choose the plan that fits your academic needs</p>
    </div>

    <!-- Current Plan Banner -->
    @if($currentPlan !== 'free')
    <div class="mb-8 p-6 bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">Current Plan: {{ ucfirst($currentPlan) }}</h2>
                <p class="mt-2">Your subscription ends on {{ $user->subscription_ends_at->format('F d, Y') }}</p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold">₦{{ $currentPlan === 'basic' ? $basicPrice : $proPrice }}</p>
                <p class="text-sm opacity-90">per month</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Pricing Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <!-- Free Plan -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-lg p-8">
            <div class="text-center mb-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Free</h3>
                <p class="text-5xl font-bold text-gray-900 mb-4">₦0</p>
                <p class="text-gray-600">Forever free</p>
            </div>
            
            <ul class="space-y-4 mb-8">
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Academic Network Access</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Connect with Researchers</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Peer Review Participation</span>
                </li>
                <li class="flex items-center text-gray-400">
                    <i class="fas fa-times mr-3"></i>
                    <span>AI Journal Studio</span>
                </li>
                <li class="flex items-center text-gray-400">
                    <i class="fas fa-times mr-3"></i>
                    <span>Advanced AI Features</span>
                </li>
                <li class="flex items-center text-gray-400">
                    <i class="fas fa-times mr-3"></i>
                    <span>Priority Support</span>
                </li>
            </ul>
            
            @if($currentPlan === 'free')
            <button class="w-full py-3 bg-gray-100 text-gray-700 rounded-lg font-semibold cursor-default">
                Current Plan
            </button>
            @else
            <button onclick="downgradeToFree()"
                    class="w-full py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50">
                Downgrade to Free
            </button>
            @endif
        </div>

        <!-- Basic Plan -->
        <div class="bg-white rounded-2xl border-2 border-purple-500 shadow-xl p-8 relative transform scale-105">
            <div class="absolute top-0 right-0 bg-purple-500 text-white px-4 py-2 rounded-bl-lg rounded-tr-2xl">
                <span class="font-bold">POPULAR</span>
            </div>
            
            <div class="text-center mb-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Basic</h3>
                <p class="text-5xl font-bold text-gray-900 mb-4">₦{{ $basicPrice }}</p>
                <p class="text-gray-600">per month</p>
            </div>
            
            <ul class="space-y-4 mb-8">
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span><strong>Everything in Free</strong></span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>AI Journal Studio Access</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>10 AI Credits per Month</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Basic AI Formatting</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>256MB Storage</span>
                </li>
                <li class="flex items-center text-gray-400">
                    <i class="fas fa-times mr-3"></i>
                    <span>Advanced AI Features</span>
                </li>
            </ul>
            
            @if($currentPlan === 'basic')
            <button class="w-full py-3 bg-purple-100 text-purple-700 rounded-lg font-semibold cursor-default">
                Current Plan
            </button>
            @else
            <button onclick="subscribeToPlan('basic')"
                    class="w-full py-3 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg font-semibold hover:opacity-90">
                {{ $currentPlan === 'free' ? 'Upgrade to Basic' : 'Switch to Basic' }}
            </button>
            @endif
        </div>

        <!-- Pro Plan -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-lg p-8">
            <div class="text-center mb-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Pro</h3>
                <p class="text-5xl font-bold text-gray-900 mb-4">₦{{ $proPrice }}</p>
                <p class="text-gray-600">per month</p>
            </div>
            
            <ul class="space-y-4 mb-8">
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span><strong>Everything in Basic</strong></span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Unlimited AI Credits</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Advanced AI Features</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Priority Peer Review</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>1GB Storage</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Priority Support</span>
                </li>
            </ul>
            
            @if($currentPlan === 'pro')
            <button class="w-full py-3 bg-purple-100 text-purple-700 rounded-lg font-semibold cursor-default">
                Current Plan
            </button>
            @else
            <button onclick="subscribeToPlan('pro')"
                    class="w-full py-3 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg font-semibold hover:opacity-90">
                {{ $currentPlan === 'free' ? 'Upgrade to Pro' : 'Switch to Pro' }}
            </button>
            @endif
        </div>
    </div>

    <!-- Feature Comparison -->
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">Feature Comparison</h2>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-4 font-semibold text-gray-900">Feature</th>
                        <th class="text-center py-4 font-semibold text-gray-900">Free</th>
                        <th class="text-center py-4 font-semibold text-gray-900">Basic</th>
                        <th class="text-center py-4 font-semibold text-purple-700">Pro</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="py-4 font-medium">AI Journal Studio</td>
                        <td class="text-center py-4"><i class="fas fa-times text-red-500"></i></td>
                        <td class="text-center py-4"><i class="fas fa-check text-green-500"></i></td>
                        <td class="text-center py-4"><i class="fas fa-check text-green-500"></i></td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-4 font-medium">AI Credits per Month</td>
                        <td class="text-center py-4">0</td>
                        <td class="text-center py-4">10</td>
                        <td class="text-center py-4">Unlimited</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-4 font-medium">Storage Space</td>
                        <td class="text-center py-4">100MB</td>
                        <td class="text-center py-4">256MB</td>
                        <td class="text-center py-4">1GB</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-4 font-medium">Advanced AI Features</td>
                        <td class="text-center py-4"><i class="fas fa-times text-red-500"></i></td>
                        <td class="text-center py-4"><i class="fas fa-times text-red-500"></i></td>
                        <td class="text-center py-4"><i class="fas fa-check text-green-500"></i></td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-4 font-medium">Priority Support</td>
                        <td class="text-center py-4"><i class="fas fa-times text-red-500"></i></td>
                        <td class="text-center py-4"><i class="fas fa-times text-red-500"></i></td>
                        <td class="text-center py-4"><i class="fas fa-check text-green-500"></i></td>
                    </tr>
                    <tr>
                        <td class="py-4 font-medium">Monthly Price</td>
                        <td class="text-center py-4 text-2xl font-bold">₦0</td>
                        <td class="text-center py-4 text-2xl font-bold">₦{{ $basicPrice }}</td>
                        <td class="text-center py-4 text-2xl font-bold text-purple-700">₦{{ $proPrice }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- FAQ -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">Frequently Asked Questions</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="font-bold text-gray-900 mb-2">Can I cancel anytime?</h3>
                <p class="text-gray-600">Yes, you can cancel your subscription at any time. You'll continue to have access until the end of your billing period.</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="font-bold text-gray-900 mb-2">Is there a free trial?</h3>
                <p class="text-gray-600">Yes! All paid plans come with a 7-day free trial. No credit card required to start the trial.</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="font-bold text-gray-900 mb-2">What payment methods do you accept?</h3>
                <p class="text-gray-600">We accept all major debit/credit cards (Visa, MasterCard, Verve) through Paystack. Bank transfers are also supported.</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="font-bold text-gray-900 mb-2">Can I upgrade or downgrade my plan?</h3>
                <p class="text-gray-600">Yes, you can change your plan at any time. The changes will take effect at the start of your next billing cycle.</p>
            </div>
        </div>
    </div>
</div>

<script>
    function subscribeToPlan(plan) {
        // Show loading
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
        button.disabled = true;
        
        fetch('/payment/initialize', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ plan: plan })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to Paystack payment page
                window.location.href = data.authorization_url;
            } else {
                alert('Error: ' + data.message);
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            alert('An error occurred. Please try again.');
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
    
    function downgradeToFree() {
        if (!confirm('Are you sure you want to downgrade to the Free plan? You will lose access to AI features.')) {
            return;
        }
        
        fetch('/subscription/cancel', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Successfully downgraded to Free plan');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
</script>
@endsection