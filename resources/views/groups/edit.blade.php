@extends('layouts.network')

@section('title', 'Edit Group - Academic Groups - Querentia')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Group</h1>
            <p class="text-gray-600 mt-2">Update group information and settings</p>
        </div>
        <a href="{{ route('groups.show', $group->slug) }}" class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-times mr-2"></i>Cancel
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-lg p-8">
        <form action="{{ route('groups.update', $group->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Group Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Group Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" required maxlength="255"
                       value="{{ old('name', $group->name) }}"
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
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">{{ old('description', $group->description) }}</textarea>
                <p class="mt-1 text-sm text-gray-500">{{ Str::length(old('description', $group->description) ?? 0) }}/1000 characters</p>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Group Avatar -->
            <div class="mb-6">
                <label for="avatar" class="block text-sm font-medium text-gray-700 mb-2">
                    Group Avatar <span class="text-gray-500">(Optional)</span>
                </label>
                <div class="flex items-center space-x-4">
                    <div class="w-20 h-20 rounded-lg bg-gray-200 flex items-center justify-center overflow-hidden" id="avatar-preview">
                        @if($group->avatar)
                            <img src="{{ asset('storage/' . $group->avatar) }}" 
                                 alt="{{ $group->name }}" 
                                 class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-layer-group text-gray-400 text-2xl"></i>
                        @endif
                    </div>
                    <div>
                        <input type="file" name="avatar" id="avatar" accept="image/*"
                               class="hidden">
                        <button type="button" onclick="document.getElementById('avatar').click()"
                                class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition">
                            <i class="fas fa-upload mr-2"></i>Change Image
                        </button>
                        <p class="mt-1 text-sm text-gray-500">JPG, PNG, GIF (Max 2MB)</p>
                    </div>
                </div>
                @error('avatar')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('groups.show', $group->slug) }}" 
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-save mr-2"></i>Update Group
                </button>
            </div>
        </form>
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
        @if($group->avatar)
            preview.innerHTML = `<img src="{{ asset('storage/' . $group->avatar) }}" class="w-full h-full object-cover rounded-lg">`;
        @else
            preview.innerHTML = '<i class="fas fa-layer-group text-gray-400 text-2xl"></i>';
        @endif
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
