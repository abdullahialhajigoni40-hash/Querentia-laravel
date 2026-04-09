@extends('layouts.network')

@section('title', 'Profile - Querentia')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Profile Settings</h1>
            <p class="text-gray-600 mt-2">Manage your account information and preferences</p>
        </div>
    </div>
    
    <div class="space-y-6">
        <div class="p-6 bg-white shadow rounded-lg">
            <div class="max-w-2xl">
                <livewire:profile.update-profile-information-form />
            </div>
        </div>

        <div class="p-6 bg-white shadow rounded-lg">
            <div class="max-w-2xl">
                <livewire:profile.update-password-form />
            </div>
        </div>

        <div class="p-6 bg-white shadow rounded-lg">
            <div class="max-w-2xl">
                <livewire:profile.delete-user-form />
            </div>
        </div>
    </div>
</div>
@endsection
