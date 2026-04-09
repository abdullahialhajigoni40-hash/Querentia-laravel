@extends('layouts.network')

@section('title', 'Create Group - Academic Groups - Querentia')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Create Academic Group</h1>
            <p class="text-gray-600 mt-2">Start a collaborative space for researchers in your field</p>
        </div>
        <a href="{{ route('groups.index') }}" class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-times mr-2"></i>Cancel
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-lg p-8">
        <form action="{{ route('groups.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Group Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Group Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" required maxlength="255"
                       value="{{ old('name') }}"
                       placeholder="Enter a descriptive name for your group..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description <span class="text-gray-500">(Optional)</span>
                </label>
                <textarea name="description" id="description" rows="4" maxlength="1000"
                          placeholder="Describe the purpose and focus of your group..."
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">{{ old('description') }}</textarea>
                <p class="mt-1 text-sm text-gray-500">{{ Str::length(old('description') ?? 0) }}/1000 characters</p>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Group Type -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Group Type <span class="text-red-500">*</span>
                </label>
                <div class="space-y-3">
                    <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="type" value="public" 
                               {{ old('type', 'public') === 'public' ? 'checked' : '' }}
                               class="mr-3 text-purple-600 focus:ring-purple-500">
                        <div>
                            <div class="font-medium text-gray-900">Public Group</div>
                            <div class="text-sm text-gray-500">Anyone can join and discover this group</div>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="type" value="private" 
                               {{ old('type') === 'private' ? 'checked' : '' }}
                               class="mr-3 text-purple-600 focus:ring-purple-500">
                        <div>
                            <div class="font-medium text-gray-900">Private Group</div>
                            <div class="text-sm text-gray-500">Only invited members can join and see content</div>
                        </div>
                    </label>
                </div>
                @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Group Avatar -->
            <div class="mb-6">
                <label for="avatar" class="block text-sm font-medium text-gray-700 mb-2">
                    Group Avatar <span class="text-gray-500">(Optional)</span>
                </label>
                <div class="flex items-center space-x-4">
                    <div class="w-20 h-20 rounded-lg bg-gray-200 flex items-center justify-center" id="avatar-preview">
                        <i class="fas fa-layer-group text-gray-400 text-2xl"></i>
                    </div>
                    <div>
                        <input type="file" name="avatar" id="avatar" accept="image/*"
                               class="hidden">
                        <button type="button" onclick="document.getElementById('avatar').click()"
                                class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition">
                            <i class="fas fa-upload mr-2"></i>Choose Image
                        </button>
                        <p class="mt-1 text-sm text-gray-500">JPG, PNG, GIF (Max 2MB)</p>
                    </div>
                </div>
                @error('avatar')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Add Members -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Add Members <span class="text-gray-500">(Optional)</span>
                </label>
                <p class="text-sm text-gray-600 mb-3">Select from your connections to add them to the group</p>
                
                @if($userConnections->count() > 0)
                    <div class="space-y-2 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-4">
                        @foreach($userConnections as $connection)
                            <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                <input type="checkbox" name="members[]" value="{{ $connection->connected_user_id }}"
                                       class="mr-3 text-purple-600 focus:ring-purple-500">
                                @if($connection->connectedUser->profile_picture)
                                    <img src="{{ asset('storage/' . $connection->connectedUser->profile_picture) }}" 
                                         alt="{{ $connection->connectedUser->full_name }}" 
                                         class="w-8 h-8 rounded-full object-cover mr-3">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-xs text-gray-600 mr-3">
                                        {{ strtoupper(substr($connection->connectedUser->first_name, 0, 1) . substr($connection->connectedUser->last_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900">{{ $connection->connectedUser->full_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $connection->connectedUser->position ?? 'Researcher' }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 bg-gray-50 rounded-lg">
                        <i class="fas fa-users text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-600">No connections available</p>
                        <p class="text-sm text-gray-500 mt-1">You can add members later from the group settings</p>
                    </div>
                @endif
                @error('members')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('groups.index') }}" 
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-plus mr-2"></i>Create Group
                </button>
            </div>
        </form>
    </div>

    <!-- Group Guidelines -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-3">
            <i class="fas fa-info-circle mr-2"></i>Group Guidelines
        </h3>
        <ul class="space-y-2 text-blue-800">
            <li class="flex items-start">
                <i class="fas fa-check-circle mt-1 mr-2 text-blue-600"></i>
                <span>Choose a clear, descriptive name that reflects your group's purpose</span>
            </li>
            <li class="flex items-start">
                <i class="fas fa-check-circle mt-1 mr-2 text-blue-600"></i>
                <span>Write a detailed description to help others understand your group's focus</span>
            </li>
            <li class="flex items-start">
                <i class="fas fa-check-circle mt-1 mr-2 text-blue-600"></i>
                <span>Consider whether your group should be public or private based on your collaboration needs</span>
            </li>
            <li class="flex items-start">
                <i class="fas fa-check-circle mt-1 mr-2 text-blue-600"></i>
                <span>Only add members who are in your connections list</span>
            </li>
            <li class="flex items-start">
                <i class="fas fa-check-circle mt-1 mr-2 text-blue-600"></i>
                <span>As the creator, you'll automatically be the group admin</span>
            </li>
        </ul>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Avatar preview
document.getElementById('avatar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('avatar-preview');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover rounded-lg">`;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '<i class="fas fa-layer-group text-gray-400 text-2xl"></i>';
    }
});

// Character counter for description
const descriptionTextarea = document.getElementById('description');
if (descriptionTextarea) {
    descriptionTextarea.addEventListener('input', () => {
        const length = descriptionTextarea.value.length;
        const counter = descriptionTextarea.parentElement.querySelector('.text-gray-500');
        if (counter) {
            counter.textContent = `${length}/1000 characters`;
        }
    });
}
</script>
@endsection
