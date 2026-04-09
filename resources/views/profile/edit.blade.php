@extends('layouts.network')

@section('title', 'Edit Profile - Querentia')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Profile</h1>
            <p class="text-gray-600 mt-2">Update your profile information and preferences</p>
        </div>
        <a href="{{ route('profile.show') }}" class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-arrow-left mr-2"></i>Back to Profile
        </a>
    </div>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">
                        <i class="fas fa-user mr-2 text-purple-500"></i>Basic Information
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                            <input type="text" name="first_name" value="{{ $user->first_name }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                            <input type="text" name="last_name" value="{{ $user->last_name }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Professional Title</label>
                        <input type="text" name="title" value="{{ $profile->title ?? '' }}" 
                               placeholder="e.g., Professor, Research Scientist, PhD Student"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Institution</label>
                        <input type="text" name="institution" value="{{ $user->institution ?? '' }}" required
                               placeholder="e.g., Stanford University, MIT, Harvard Medical School"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <input type="text" name="department" value="{{ $user->department ?? '' }}" 
                               placeholder="e.g., Computer Science, Biology, Physics"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                        <select name="position" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="" {{ !$user->position ? 'selected' : '' }}>Select Position</option>
                            <option value="student" {{ $user->position === 'student' ? 'selected' : '' }}>Student</option>
                            <option value="researcher" {{ $user->position === 'researcher' ? 'selected' : '' }}>Researcher</option>
                            <option value="lecturer" {{ $user->position === 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                            <option value="professor" {{ $user->position === 'professor' ? 'selected' : '' }}>Professor</option>
                            <option value="phd" {{ $user->position === 'phd' ? 'selected' : '' }}>PhD Candidate</option>
                            <option value="other" {{ $user->position === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ $user->email }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Profile Details -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">
                        <i class="fas fa-info-circle mr-2 text-purple-500"></i>Profile Details
                    </h2>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                        <textarea name="bio" rows="4" 
                                  placeholder="Tell us about yourself, your research interests, and academic background..."
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">{{ $profile->bio ?? '' }}</textarea>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Research Interests</label>
                        <input type="text" name="research_interests" 
                               value="{{ $user->research_interests ? implode(', ', $user->research_interests) : '' }}" 
                               placeholder="e.g., Machine Learning, Neuroscience, Climate Change"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Separate interests with commas</p>
                    </div>
                </div>

                <!-- Education -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">
                        <i class="fas fa-graduation-cap mr-2 text-purple-500"></i>Education
                    </h2>
                    
                    <div id="education-container">
                        @if($profile && $profile->education)
                            @foreach($profile->education as $index => $edu)
                            <div class="education-entry border rounded-lg p-4 mb-4">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="font-semibold text-gray-800">Education {{ $index + 1 }}</h3>
                                    @if($index > 0)
                                    <button type="button" onclick="removeEducation(this)" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input type="text" name="education[{{ $index }}][degree]" value="{{ $edu['degree'] ?? '' }}" 
                                           placeholder="Degree" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <input type="text" name="education[{{ $index }}][institution]" value="{{ $edu['institution'] ?? '' }}" 
                                           placeholder="Institution" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <input type="text" name="education[{{ $index }}][year]" value="{{ $edu['year'] ?? '' }}" 
                                           placeholder="Year" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>
                            </div>
                            @endforeach
                        @else
                        <div class="education-entry border rounded-lg p-4 mb-4">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="font-semibold text-gray-800">Education 1</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <input type="text" name="education[0][degree]" placeholder="Degree" 
                                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <input type="text" name="education[0][institution]" placeholder="Institution" 
                                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <input type="text" name="education[0][year]" placeholder="Year" 
                                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <button type="button" onclick="addEducation()" class="mt-4 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-plus mr-2"></i>Add Education
                    </button>
                </div>

                <!-- Skills -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">
                        <i class="fas fa-tools mr-2 text-purple-500"></i>Skills & Expertise
                    </h2>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Add Skill</label>
                        <div class="flex gap-2">
                            <input type="text" id="skill-input" placeholder="e.g., Python, Data Analysis, Statistical Modeling"
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <button type="button" onclick="addSkill()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                                Add
                            </button>
                        </div>
                    </div>

                    <div id="skills-container" class="flex flex-wrap gap-2">
                        @if($profile && $profile->skills)
                            @foreach($profile->skills as $skill)
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm flex items-center gap-2">
                                {{ $skill }}
                                <button type="button" onclick="removeSkill(this)" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-times"></i>
                                </button>
                                <input type="hidden" name="skills[]" value="{{ $skill }}">
                            </span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column - Profile Picture -->
            <div class="space-y-6">
                <!-- Profile Picture -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">
                        <i class="fas fa-camera mr-2 text-purple-500"></i>Profile Picture
                    </h2>
                    
                    <div class="text-center">
                        <div class="mb-4">
                            @if($user->profile_picture)
                                <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                     alt="Current profile picture" 
                                     class="w-32 h-32 rounded-full object-cover mx-auto border-4 border-gray-200">
                            @else
                                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-purple-500 to-blue-600 flex items-center justify-center text-white text-4xl font-bold mx-auto">
                                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        
                        <div>
                            <label class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition cursor-pointer inline-block">
                                <i class="fas fa-upload mr-2"></i>Upload New Picture
                                <input type="file" name="profile_picture" accept="image/*" class="hidden" onchange="previewProfilePicture(this)">
                            </label>
                        </div>
                        
                        <p class="text-xs text-gray-500 mt-2">JPG, PNG or GIF. Max size 2MB.</p>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">
                        <i class="fas fa-link mr-2 text-purple-500"></i>Social Links
                    </h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">LinkedIn</label>
                            <input type="url" name="linkedin" value="{{ $profile->linkedin ?? '' }}" 
                                   placeholder="https://linkedin.com/in/yourprofile"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ORCID</label>
                            <input type="text" name="orcid" value="{{ $profile->orcid ?? '' }}" 
                                   placeholder="0000-0002-1825-0097 or https://orcid.org/0000-0002-1825-0097"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Twitter</label>
                            <input type="url" name="twitter" value="{{ $profile->twitter ?? '' }}" 
                                   placeholder="https://twitter.com/yourhandle"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                            <input type="url" name="website" value="{{ $profile->website ?? '' }}" 
                                   placeholder="https://yourwebsite.com"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="bg-white rounded-xl shadow p-6">
                    <button type="submit" class="w-full bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition font-semibold">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    let educationCount = {{ $profile && $profile->education ? count($profile->education) : 1 }};

    function addEducation() {
        const container = document.getElementById('education-container');
        const entry = document.createElement('div');
        entry.className = 'education-entry border rounded-lg p-4 mb-4';
        entry.innerHTML = `
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-semibold text-gray-800">Education ${educationCount + 1}</h3>
                <button type="button" onclick="removeEducation(this)" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="education[${educationCount}][degree]" placeholder="Degree" 
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <input type="text" name="education[${educationCount}][institution]" placeholder="Institution" 
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <input type="text" name="education[${educationCount}][year]" placeholder="Year" 
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
        `;
        container.appendChild(entry);
        educationCount++;
    }

    function removeEducation(button) {
        button.closest('.education-entry').remove();
    }

    function addSkill() {
        const input = document.getElementById('skill-input');
        const skill = input.value.trim();
        
        if (skill) {
            const container = document.getElementById('skills-container');
            const skillElement = document.createElement('span');
            skillElement.className = 'bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm flex items-center gap-2';
            skillElement.innerHTML = `
                ${skill}
                <button type="button" onclick="removeSkill(this)" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-times"></i>
                </button>
                <input type="hidden" name="skills[]" value="${skill}">
            `;
            container.appendChild(skillElement);
            input.value = '';
        }
    }

    function removeSkill(button) {
        button.closest('span').remove();
    }

    function previewProfilePicture(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = input.closest('.text-center').querySelector('img, div');
                if (img.tagName === 'IMG') {
                    img.src = e.target.result;
                } else {
                    const newImg = document.createElement('img');
                    newImg.src = e.target.result;
                    newImg.alt = 'Profile preview';
                    newImg.className = 'w-32 h-32 rounded-full object-cover mx-auto border-4 border-gray-200';
                    img.parentNode.replaceChild(newImg, img);
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Handle Enter key in skill input
    document.getElementById('skill-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addSkill();
        }
    });
</script>
@endsection
