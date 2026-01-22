@extends('layouts.network')

@section('title', 'Payment Failed - Querentia')

@section('content')
<div class="max-w-2xl mx-auto py-16 px-4 text-center">
    <div class="mb-8">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-times text-red-600 text-3xl"></i>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Payment Failed</h1>
        <p class="text-gray-600 mb-8">
            @if(session('error'))
                {{ session('error') }}
            @else
                We were unable to process your payment. Please try again.
            @endif
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Possible reasons:</h2>
        
        <ul class="text-left text-gray-700 space-y-3">
            <li class="flex items-start">
                <i class="fas fa-credit-card text-gray-400 mt-1 mr-3"></i>
                <span>Insufficient funds in your account</span>
            </li>
            <li class="flex items-start">
                <i class="fas fa-shield-alt text-gray-400 mt-1 mr-3"></i>
                <span>Bank declined the transaction for security reasons</span>
            </li>
            <li class="flex items-start">
                <i class="fas fa-wifi text-gray-400 mt-1 mr-3"></i>
                <span>Network connectivity issues</span>
            </li>
            <li class="flex items-start">
                <i class="fas fa-clock text-gray-400 mt-1 mr-3"></i>
                <span>Payment session expired</span>
            </li>
        </ul>
    </div>

    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="{{ route('payment.pricing') }}" 
           class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg font-semibold hover:opacity-90">
            <i class="fas fa-redo mr-2"></i>Try Again
        </a>
        <a href="{{ route('dashboard') }}" 
           class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50">
            <i class="fas fa-home mr-2"></i>Return to Dashboard
        </a>
    </div>
</div>
@endsection