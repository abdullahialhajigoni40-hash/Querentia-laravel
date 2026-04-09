@extends('layouts.guest')

@section('title', 'Privacy Policy')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Privacy Policy</h1>

        <div class="prose max-w-none text-gray-700">
            <p>
                This Privacy Policy describes how Querentia collects, uses, and shares information when you
                use the platform.
            </p>

            <h2>1. Information We Collect</h2>
            <p>
                We collect information you provide (such as your name, email address, institution, profile details, and content you submit), and information generated through your use of the service.
                This may include:
            </p>
            <ul>
                <li>Account and profile data (name, email, institution, academic position, profile links)</li>
                <li>Content you create (journals, posts, comments, reviews, and messages you submit)</li>
                <li>Technical data (IP address, device/browser information, timestamps, and basic usage logs)</li>
            </ul>

            <h2>2. How We Use Information</h2>
            <p>
                We use information to provide and improve the service, maintain security, communicate with you,
                and comply with legal obligations.
            </p>

            <h2>3. Sharing</h2>
            <p>
                We may share information with service providers who help operate the platform, and when required
                by law.
            </p>

            <h2>4. Data Retention</h2>
            <p>
                We retain information for as long as necessary to provide the service and meet legal requirements.
                You may request account deletion, and we will take reasonable steps to remove or anonymize personal data unless retention is required for security, fraud prevention, dispute resolution, or legal compliance.
            </p>

            <h2>5. Contact</h2>
            <p>
                If you have questions about this policy, contact us at <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.
            </p>
        </div>
    </div>
</div>
@endsection
