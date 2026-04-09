@extends('layouts.network')

@section('title', 'Redirect to Journal Editor - Querentia')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-8 text-center">
        <div class="mb-6">
            <i class="fas fa-robot text-6xl text-purple-600 mb-4"></i>
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Journal Creation Moved</h1>
            <p class="text-lg text-gray-600 mb-6">
                Journal creation has been moved to our AI Journal Editor for a better writing experience.
            </p>
        </div>

        <div class="bg-purple-50 border border-purple-200 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-purple-900 mb-3">
                <i class="fas fa-sparkles mr-2"></i>Why the AI Journal Editor?
            </h2>
            <ul class="text-left text-purple-800 space-y-2">
                <li><i class="fas fa-check mr-2 text-purple-600"></i>AI-powered writing assistance</li>
                <li><i class="fas fa-check mr-2 text-purple-600"></i>Real-time grammar and style suggestions</li>
                <li><i class="fas fa-check mr-2 text-purple-600"></i>Automatic formatting for academic standards</li>
                <li><i class="fas fa-check mr-2 text-purple-600"></i>Citation management</li>
                <li><i class="fas fa-check mr-2 text-purple-600"></i>Collaboration features</li>
            </ul>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('create_journal') }}" 
               class="bg-purple-600 text-white px-8 py-3 rounded-lg hover:bg-purple-700 transition font-semibold inline-flex items-center justify-center">
                <i class="fas fa-robot mr-2"></i>
                Go to AI Journal Editor
            </a>
            <a href="{{ route('network.home') }}" 
               class="bg-gray-200 text-gray-800 px-8 py-3 rounded-lg hover:bg-gray-300 transition font-semibold inline-flex items-center justify-center">
                <i class="fas fa-home mr-2"></i>
                Back to Home
            </a>
        </div>

        <div class="mt-8 text-sm text-gray-500">
            <p>You will be redirected automatically in <span id="countdown">10</span> seconds...</p>
        </div>
    </div>
</div>

<script>
// Auto-redirect after 10 seconds
let countdown = 10;
const countdownElement = document.getElementById('countdown');

const timer = setInterval(() => {
    countdown--;
    countdownElement.textContent = countdown;
    
    if (countdown <= 0) {
        clearInterval(timer);
        window.location.href = "{{ route('create_journal') }}";
    }
}, 1000);

// Manual redirect on button click
document.addEventListener('DOMContentLoaded', function() {
    const redirectButton = document.querySelector('a[href="{{ route('create_journal') }}"]');
    if (redirectButton) {
        redirectButton.addEventListener('click', function(e) {
            clearInterval(timer);
        });
    }
});
</script>
@endsection
