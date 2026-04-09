@extends('layouts.guest')

@section('title', 'Community Guidelines')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="bg-white rounded-xl shadow p-8">
        <div class="flex items-center gap-4 mb-6">
            <img src="{{ asset('logo.png') }}" alt="Querentia" class="h-10 w-auto">
            <h1 class="text-3xl font-bold text-gray-900">Community Guidelines</h1>
        </div>

        <div class="prose max-w-none">
            <p>Querentia is a professional academic network. These guidelines help keep discussions constructive, safe, and trustworthy.</p>

            <h2>Be respectful and professional</h2>
            <p>Engage with ideas, not people. Harassment, hate speech, or personal attacks are not allowed.</p>

            <h2>Keep content academic and relevant</h2>
            <p>Posts and comments should be relevant to research, scholarship, peer review, and professional collaboration.</p>

            <h2>No spam or deceptive content</h2>
            <p>Do not post unsolicited promotions, repetitive content, phishing links, or misleading claims.</p>

            <h2>Respect privacy and intellectual property</h2>
            <p>Do not share private personal information. Share content you have permission to share and cite sources where appropriate.</p>

            <h2>Preprints and peer review</h2>
            <p>Preprints are not peer reviewed. Provide feedback constructively and disclose conflicts of interest if applicable.</p>

            <h2>Reporting and moderation</h2>
            <p>If you see content that violates these guidelines, use the report feature. Querentia may remove content or restrict accounts to protect the community.</p>

            <p class="text-sm text-gray-500 mt-8">Last updated: {{ now()->toDateString() }}</p>
        </div>
    </div>
</div>
@endsection
