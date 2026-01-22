@extends('layouts.app')

@section('title', 'Subscription - Querentia')

@section('content')
<div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Subscription Plans</h1>
    
    <!-- Current Plan -->
    <div class="bg-white rounded-xl shadow p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Your Current Plan</h2>
        <div class="flex items-center justify-between">
            <div>
                <span class="text-3xl font-bold text-gray-900">
                    @if(auth()->user()->isPro())
                        Pro Plan
                    @elseif(auth()->user()->subscription_tier == 'basic')
                        Basic Plan
                    @else
                        Free Plan
                    @endif
                </span>
                <p class="text-gray-600 mt-1">
                    @if(auth()->user()->isPro())
                        Unlimited AI features, priority support, and advanced tools
                    @elseif(auth()->user()->subscription_tier == 'basic')
                        Basic AI features with monthly limits
                    @else
                        Access to academic network only
                    @endif
                </p>
            </div>
            @if(!auth()->user()->isPro())
            <a href="#plans" 
               class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 font-semibold">
                Upgrade Plan
            </a>
            @endif
        </div>
    </div>
    
    <!-- Pricing Plans -->
    <div id="plans" class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Free Plan -->
        <div class="bg-white rounded-xl shadow p-6 border-2 border-gray-200">
            <div class="text-center mb-6">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Free</h3>
                <div class="mb-4">
                    <span class="text-3xl font-bold text-gray-900">₦0</span>
                    <span class="text-gray-600">/month</span>
                </div>
            </div>
            <ul class="space-y-3 mb-6">
                <li class="flex items-center text-gray-600">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span>Academic Network Access</span>
                </li>
                <li class="flex items-center text-gray-600">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span>Profile Creation</span>
                </li>
                <li class="flex items-center text-gray-600">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span>Peer Reviews</span>
                </li>
                <li class="flex items-center text-gray-400">
                    <i class="fas fa-times mr-2"></i>
                    <span>AI Journal Tools</span>
                </li>
                <li class="flex items-center text-gray-400">
                    <i class="fas fa-times mr-2"></i>
                    <span>PDF Generation</span>
                </li>
            </ul>
            @if(auth()->user()->subscription_tier == 'free')
            <button class="w-full bg-gray-100 text-gray-800 py-3 rounded-lg font-semibold cursor-default">
                Current Plan
            </button>
            @else
            <button class="w-full border-2 border-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-50">
                Downgrade
            </button>
            @endif
        </div>
        
        <!-- Basic Plan -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-2 border-purple-500 relative transform hover:-translate-y-1 transition">
            <div class="absolute top-0 right-0 bg-purple-600 text-white px-3 py-1 rounded-bl-lg text-sm">
                Popular
            </div>
            <div class="text-center mb-6">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Basic</h3>
                <div class="mb-4">
                    <span class="text-3xl font-bold text-gray-900">₦5,000</span>
                    <span class="text-gray-600">/month</span>
                </div>
            </div>
            <ul class="space-y-3 mb-6">
                <li class="flex items-center text-gray-600">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span>Everything in Free</span>
                </li>
                <li class="flex items-center text-gray-600">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span>AI Journal Drafting</span>
                </li>
                <li class="flex items-center text-gray-600">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span>Basic Formatting</span>
                </li>
                <li class="flex items-center text-gray-600">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span>5 AI Credits/Month</span>
                </li>
                <li class="flex items-center text-gray-400">
                    <i class="fas fa-times mr-2"></i>
                    <span>Advanced AI Features</span>
                </li>
            </ul>
            @if(auth()->user()->subscription_tier == 'basic')
            <button class="w-full bg-purple-100 text-purple-700 py-3 rounded-lg font-semibold cursor-default">
                Current Plan
            </button>
            @else
            <button class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700">
                Upgrade to Basic
            </button>
            @endif
        </div>
        
        <!-- Pro Plan -->
        <div class="bg-white rounded-xl shadow p-6 border-2 border-gray-200">
            <div class="text-center mb-6">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Pro</h3>
                <div class="mb-4">
                    <span class="text-3xl font-bold text-gray-900">₦10,000</span>
                    <span class="text-gray-600">/month</span>
                </div>
            </div>
            <ul class="space-y-3 mb-6">
                <li class="flex items-center text-gray-600">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span>Everything in Basic</span>
                </li>
                <li class="flex items-center text-gray-600">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span>Unlimited AI Credits</span>
                </li>
                <li class="flex items-center text-gray-600">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span>Advanced Formatting</span>
                </li>
                <li class="flex items-center text-gray-600">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span>Priority Support</span>
                </li>
                <li class="flex items-center text-gray-600">
                    <i class="fas fa-check text-green-500 mr-2"></i>
                    <span>Team Collaboration</span>
                </li>
            </ul>
            @if(auth()->user()->isPro())
            <button class="w-full bg-green-100 text-green-700 py-3 rounded-lg font-semibold cursor-default">
                Current Plan
            </button>
            @else
            <button class="w-full bg-gray-800 text-white py-3 rounded-lg font-semibold hover:bg-gray-900">
                Upgrade to Pro
            </button>
            @endif
        </div>
    </div>
</div>
@endsection