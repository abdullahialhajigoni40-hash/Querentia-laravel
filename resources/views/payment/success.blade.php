@extends('layouts.network')

@section('title', 'Payment Successful - Querentia')

@section('content')
<div class="max-w-2xl mx-auto py-16 px-4 text-center">
    <div class="mb-8">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-check text-green-600 text-3xl"></i>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Payment Successful! 🎉</h1>
        <p class="text-gray-600 mb-8">
            Thank you for upgrading your Querentia account. Your subscription is now active.
        </p>
    </div>

    @if(session('transaction'))
    <div class="bg-white rounded-xl shadow-lg p-8 mb-8 text-left">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Payment Details</h2>
        
        <div class="space-y-4">
            <div class="flex justify-between">
                <span class="text-gray-600">Reference:</span>
                <span class="font-medium">{{ session('transaction')->reference }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Amount Paid:</span>
                <span class="font-bold text-green-600">₦{{ number_format(session('transaction')->amount_paid, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Date:</span>
                <span class="font-medium">{{ session('transaction')->paid_at->format('F d, Y h:i A') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Payment Method:</span>
                <span class="font-medium capitalize">{{ session('transaction')->channel ?? 'Card' }}</span>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-purple-50 border border-purple-200 rounded-xl p-6 mb-8">
        <h3 class="font-bold text-purple-900 mb-3">What's Next?</h3>
        <ul class="text-left text-purple-800 space-y-2">
            <li class="flex items-start">
                <i class="fas fa-robot text-purple-600 mt-1 mr-3"></i>
                <span>Access the AI Journal Studio to start creating publications</span>
            </li>
            <li class="flex items-start">
                <i class="fas fa-bolt text-purple-600 mt-1 mr-3"></i>
                <span>Use your AI credits to enhance your research writing</span>
            </li>
            <li class="flex items-start">
                <i class="fas fa-cloud-upload-alt text-purple-600 mt-1 mr-3"></i>
                <span>Enjoy increased storage for your research files</span>
            </li>
        </ul>
    </div>

    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="{{ route('ai-studio') }}" 
           class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg font-semibold hover:opacity-90">
            <i class="fas fa-robot mr-2"></i>Go to AI Studio
        </a>
        <a href="{{ route('dashboard') }}" 
           class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50">
            <i class="fas fa-home mr-2"></i>Return to Dashboard
        </a>
    </div>
</div>
@endsection