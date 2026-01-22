@extends('layouts.app')

@section('title', 'Upgrade Subscription - Querentia')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Upgrade Your Plan</h1>
        <p class="text-gray-600 mt-2">Choose the plan that fits your research needs</p>
    </div>

    <!-- Current Plan -->
    <div class="bg-white rounded-xl shadow p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Current Plan</h2>
        <div class="flex items-center justify-between">
            <div>
                <span class="text-2xl font-bold text-gray-900">
                    {{ ucfirst(auth()->user()->subscription_tier) }}
                </span>
                <p class="text-gray-600 mt-1">
                    @if(auth()->user()->isPro())
                        Unlimited AI access • Priority support
                    @elseif(auth()->user()->subscription_tier == 'basic')
                        Limited AI access • Basic features
                    @else
                        Free account • Limited features
                    @endif
                </p>
            </div>
            <div>
                @if(auth()->user()->subscription_ends_at)
                    <p class="text-gray-600">Renews on {{ auth()->user()->subscription_ends_at->format('M d, Y') }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Pricing Plans -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Free Plan -->
        <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
            <div class="text-center mb-6">
                <h3 class="text-xl font-bold text-gray-900">Free</h3>
                <div class="mt-4">
                    <span class="text-4xl font-bold text-gray-900">₦0</span>
                    <span class="text-gray-600">/month</span>
                </div>
                <p class="text-gray-600 mt-2">Basic access for academic networking</p>
            </div>
            
            <ul class="space-y-3 mb-6">
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Academic network access</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Profile & connections</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Basic paper browsing</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Peer review participation</span>
                </li>
                <li class="flex items-center text-gray-400">
                    <i class="fas fa-times mr-3"></i>
                    <span>AI journal creation</span>
                </li>
                <li class="flex items-center text-gray-400">
                    <i class="fas fa-times mr-3"></i>
                    <span>Advanced formatting</span>
                </li>
                <li class="flex items-center text-gray-400">
                    <i class="fas fa-times mr-3"></i>
                    <span>Priority support</span>
                </li>
            </ul>
            
            @if(auth()->user()->subscription_tier == 'free')
            <button class="w-full bg-gray-200 text-gray-700 font-semibold py-3 px-4 rounded-lg cursor-not-allowed" disabled>
                Current Plan
            </button>
            @else
            <button class="w-full border border-gray-300 text-gray-700 font-semibold py-3 px-4 rounded-lg hover:bg-gray-50 transition">
                Downgrade to Free
            </button>
            @endif
        </div>

        <!-- Basic Plan -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-500 p-6 relative">
            <div class="absolute top-0 right-0 bg-purple-500 text-white px-4 py-1 rounded-bl-lg rounded-tr-xl text-sm font-semibold">
                MOST POPULAR
            </div>
            
            <div class="text-center mb-6">
                <h3 class="text-xl font-bold text-gray-900">Basic</h3>
                <div class="mt-4">
                    <span class="text-4xl font-bold text-gray-900">₦5,000</span>
                    <span class="text-gray-600">/month</span>
                </div>
                <p class="text-gray-600 mt-2">Essential AI tools for researchers</p>
            </div>
            
            <ul class="space-y-3 mb-6">
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Everything in Free</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>AI journal drafting (5/month)</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Basic formatting</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Citation assistance</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Basic plagiarism check</span>
                </li>
                <li class="flex items-center text-gray-400">
                    <i class="fas fa-times mr-3"></i>
                    <span>Advanced AI models</span>
                </li>
                <li class="flex items-center text-gray-400">
                    <i class="fas fa-times mr-3"></i>
                    <span>Priority support</span>
                </li>
            </ul>
            
            @if(auth()->user()->subscription_tier == 'basic')
            <button class="w-full bg-purple-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-purple-700 transition">
                Current Plan
            </button>
            @else
            <button class="w-full bg-purple-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-purple-700 transition">
                Upgrade to Basic
            </button>
            @endif
        </div>

        <!-- Pro Plan -->
        <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
            <div class="text-center mb-6">
                <h3 class="text-xl font-bold text-gray-900">Pro</h3>
                <div class="mt-4">
                    <span class="text-4xl font-bold text-gray-900">₦10,000</span>
                    <span class="text-gray-600">/month</span>
                </div>
                <p class="text-gray-600 mt-2">Complete AI research suite</p>
            </div>
            
            <ul class="space-y-3 mb-6">
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Everything in Basic</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Unlimited AI journal drafting</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Advanced AI models (3 providers)</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Advanced formatting & templates</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Comprehensive plagiarism check</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Priority support</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-500 mr-3"></i>
                    <span>Early access to new features</span>
                </li>
            </ul>
            
            @if(auth()->user()->subscription_tier == 'pro')
            <button class="w-full bg-gradient-to-r from-purple-600 to-blue-500 text-white font-semibold py-3 px-4 rounded-lg cursor-not-allowed" disabled>
                Current Plan
            </button>
            @else
            <button class="w-full bg-gradient-to-r from-purple-600 to-blue-500 text-white font-semibold py-3 px-4 rounded-lg hover:opacity-90 transition">
                Upgrade to Pro
            </button>
            @endif
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="bg-white rounded-xl shadow p-6 mt-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Payment Methods</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Flutterwave -->
            <div class="border border-gray-300 rounded-lg p-4 hover:border-purple-500 transition">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mr-4">
                            <span class="font-bold text-orange-600">FW</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Flutterwave</h3>
                            <p class="text-sm text-gray-600">Card, Bank Transfer, USSD</p>
                        </div>
                    </div>
                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                </div>
            </div>

            <!-- Paystack -->
            <div class="border border-gray-300 rounded-lg p-4 hover:border-purple-500 transition">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                            <span class="font-bold text-green-600">PS</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Paystack</h3>
                            <p class="text-sm text-gray-600">Card, Bank Transfer</p>
                        </div>
                    </div>
                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ -->
    <div class="bg-white rounded-xl shadow p-6 mt-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Frequently Asked Questions</h2>
        <div class="space-y-4">
            <div>
                <h3 class="font-semibold text-gray-900">Can I cancel anytime?</h3>
                <p class="text-gray-600 mt-1">Yes, you can cancel your subscription at any time. You'll continue to have access until the end of your billing period.</p>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900">Do you offer student discounts?</h3>
                <p class="text-gray-600 mt-1">Yes! Students can get 50% off on all plans. Contact support with your student ID for verification.</p>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900">Can I switch between plans?</h3>
                <p class="text-gray-600 mt-1">Yes, you can upgrade or downgrade your plan at any time. Changes take effect immediately.</p>
            </div>
        </div>
    </div>
</div>

<script>
    // Handle upgrade buttons
    document.querySelectorAll('button').forEach(button => {
        if (button.textContent.includes('Upgrade to')) {
            button.addEventListener('click', function() {
                const plan = this.textContent.replace('Upgrade to ', '').trim();
                if (confirm(`Upgrade to ${plan} plan? This will redirect you to the payment page.`)) {
                    // Here you would integrate with Flutterwave/Paystack
                    // For now, show a success message
                    alert(`Payment integration for ${plan} plan coming soon!`);
                }
            });
        }
    });
</script>
@endsection