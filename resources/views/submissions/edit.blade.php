@extends('layouts.network')

@section('title', 'Edit Submission - Journal Submissions - Querentia')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Submission</h1>
            <p class="text-gray-600 mt-2">Update your journal submission</p>
        </div>
        <a href="{{ route('submissions.show', $journal->id) }}" class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-times mr-2"></i>Cancel
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-lg p-8">
        <form action="{{ route('submissions.update', $journal->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Journal Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" id="title" required maxlength="255"
                       value="{{ old('title', $journal->title) }}"
                       placeholder="Enter a clear, descriptive title for your journal..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Area of Study -->
            <div class="mb-6">
                <label for="area_of_study" class="block text-sm font-medium text-gray-700 mb-2">
                    Area of Study <span class="text-red-500">*</span>
                </label>
                <select name="area_of_study" id="area_of_study" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="">Select your field of study...</option>
                    <option value="Computer Science" {{ old('area_of_study', $journal->area_of_study) == 'Computer Science' ? 'selected' : '' }}>Computer Science</option>
                    <option value="Engineering" {{ old('area_of_study', $journal->area_of_study) == 'Engineering' ? 'selected' : '' }}>Engineering</option>
                    <option value="Mathematics" {{ old('area_of_study', $journal->area_of_study) == 'Mathematics' ? 'selected' : '' }}>Mathematics</option>
                    <option value="Physics" {{ old('area_of_study', $journal->area_of_study) == 'Physics' ? 'selected' : '' }}>Physics</option>
                    <option value="Chemistry" {{ old('area_of_study', $journal->area_of_study) == 'Chemistry' ? 'selected' : '' }}>Chemistry</option>
                    <option value="Biology" {{ old('area_of_study', $journal->area_of_study) == 'Biology' ? 'selected' : '' }}>Biology</option>
                    <option value="Medicine" {{ old('area_of_study', $journal->area_of_study) == 'Medicine' ? 'selected' : '' }}>Medicine</option>
                    <option value="Psychology" {{ old('area_of_study', $journal->area_of_study) == 'Psychology' ? 'selected' : '' }}>Psychology</option>
                    <option value="Economics" {{ old('area_of_study', $journal->area_of_study) == 'Economics' ? 'selected' : '' }}>Economics</option>
                    <option value="Social Sciences" {{ old('area_of_study', $journal->area_of_study) == 'Social Sciences' ? 'selected' : '' }}>Social Sciences</option>
                    <option value="Humanities" {{ old('area_of_study', $journal->area_of_study) == 'Humanities' ? 'selected' : '' }}>Humanities</option>
                    <option value="Environmental Science" {{ old('area_of_study', $journal->area_of_study) == 'Environmental Science' ? 'selected' : '' }}>Environmental Science</option>
                    <option value="Other" {{ old('area_of_study', $journal->area_of_study) == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('area_of_study')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Abstract -->
            <div class="mb-6">
                <label for="abstract" class="block text-sm font-medium text-gray-700 mb-2">
                    Abstract <span class="text-gray-500">(Optional)</span>
                </label>
                <textarea name="abstract" id="abstract" rows="4" maxlength="1000"
                          placeholder="Provide a brief summary of your research (max 1000 characters)..."
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">{{ old('abstract', $journal->abstract) }}</textarea>
                <p class="mt-1 text-sm text-gray-500">{{ Str::length(old('abstract', $journal->abstract) ?? 0) }}/1000 characters</p>
                @error('abstract')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Keywords -->
            <div class="mb-6">
                <label for="keywords" class="block text-sm font-medium text-gray-700 mb-2">
                    Keywords <span class="text-gray-500">(Optional)</span>
                </label>
                <input type="text" name="keywords" id="keywords" maxlength="500"
                       value="{{ old('keywords', $journal->keywords) }}"
                       placeholder="Enter keywords separated by commas (e.g., machine learning, neural networks, artificial intelligence)..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <p class="mt-1 text-sm text-gray-500">Separate keywords with commas</p>
                @error('keywords')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- License -->
            <div class="mb-6">
                <label for="license" class="block text-sm font-medium text-gray-700 mb-2">
                    License <span class="text-gray-500">(Optional)</span>
                </label>
                <select name="license" id="license"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="">Select a license...</option>
                    <option value="CC-BY" {{ old('license', $journal->license) == 'CC-BY' ? 'selected' : '' }}>CC BY (Attribution)</option>
                    <option value="CC-BY-SA" {{ old('license', $journal->license) == 'CC-BY-SA' ? 'selected' : '' }}>CC BY-SA (Attribution-ShareAlike)</option>
                    <option value="CC-BY-NC" {{ old('license', $journal->license) == 'CC-BY-NC' ? 'selected' : '' }}>CC BY-NC (Attribution-NonCommercial)</option>
                    <option value="CC-BY-ND" {{ old('license', $journal->license) == 'CC-BY-ND' ? 'selected' : '' }}>CC BY-ND (Attribution-NoDerivatives)</option>
                    <option value="All Rights Reserved" {{ old('license', $journal->license) == 'All Rights Reserved' ? 'selected' : '' }}>All Rights Reserved</option>
                </select>
                @error('license')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Content -->
            <div class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                    Journal Content <span class="text-red-500">*</span>
                </label>
                <textarea name="content" id="content" rows="12" required
                          placeholder="Paste your complete journal content here. Include introduction, methodology, results, discussion, and conclusion..."
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">{{ old('content', $journal->content) }}</textarea>
                <p class="mt-1 text-sm text-gray-500">Minimum 100 characters required</p>
                @error('content')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('submissions.show', $journal->id) }}" 
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-save mr-2"></i>Update Submission
                </button>
            </div>
        </form>
    </div>

    <!-- Warning -->
    <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-yellow-900 mb-3">
            <i class="fas fa-exclamation-triangle mr-2"></i>Edit Notice
        </h3>
        <div class="text-yellow-800">
            <p class="mb-2">You are editing a draft submission. Once submitted for review, you will not be able to make changes to the content.</p>
            <p>Make sure your journal is complete and properly formatted before submitting for peer review.</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Character counter for abstract
const abstractTextarea = document.getElementById('abstract');
if (abstractTextarea) {
    abstractTextarea.addEventListener('input', () => {
        const length = abstractTextarea.value.length;
        const counter = abstractTextarea.parentElement.querySelector('.text-gray-500');
        if (counter) {
            counter.textContent = `${length}/1000 characters`;
        }
    });
}

// Character counter for content
const contentTextarea = document.getElementById('content');
if (contentTextarea) {
    contentTextarea.addEventListener('input', () => {
        const length = contentTextarea.value.length;
        const counter = contentTextarea.parentElement.querySelector('.text-gray-500');
        if (counter) {
            if (length < 100) {
                counter.textContent = `Minimum 100 characters required (${length}/100)`;
                counter.classList.add('text-red-500');
                counter.classList.remove('text-gray-500');
            } else {
                counter.textContent = `${length} characters`;
                counter.classList.remove('text-red-500');
                counter.classList.add('text-gray-500');
            }
        }
    });
}
</script>
@endsection
