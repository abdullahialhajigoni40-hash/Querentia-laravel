<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Querentia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .form-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .tab-active {
            border-bottom: 3px solid #8b5cf6;
            color: #8b5cf6;
        }
        .skill-tag {
            display: inline-flex;
            align-items: center;
            background: #e9d5ff;
            color: #7c3aed;
            padding: 4px 12px;
            border-radius: 20px;
            margin: 4px;
        }
        .skill-tag-remove {
            margin-left: 8px;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Profile</h1>
        <p class="text-gray-600 mb-8">Update your academic profile information</p>

        <!-- Success Message -->
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
        @endif

        <!-- Tabs -->
        <div class="flex border-b mb-6">
            <button id="basic-tab" class="tab-active px-6 py-3 font-medium text-lg">Basic Info</button>
            <button id="education-tab" class="px-6 py-3 font-medium text-lg text-gray-600">Education</button>
            <button id="experience-tab" class="px-6 py-3 font-medium text-lg text-gray-600">Experience</button>
            <button id="skills-tab" class="px-6 py-3 font-medium text-lg text-gray-600">Skills</button>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-form">
            @csrf
            @method('PUT')

            <!-- Basic Info Tab -->
            <div id="basic-content" class="form-card p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Basic Information</h2>
                
                <!-- Profile Picture -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Profile Picture</label>
                    <div class="flex items-center space-x-6">
                        <div class="w-24 h-24 rounded-full overflow-hidden bg-gray-200">
                            @if($user->profile_picture)
                                <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                     alt="Current profile"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-500">
                                    <i class="fas fa-user text-3xl"></i>
                                </div>
                            @endif
                        </div>
                        <div>
                            <input type="file" name="profile_picture" 
                                   accept="image/*" 
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                            <p class="text-xs text-gray-500 mt-2">JPG, PNG or GIF (Max 2MB)</p>
                        </div>
                    </div>
                </div>

                <!-- Name -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                        <input type="text" id="first_name" name="first_name" 
                               value="{{ old('first_name', $user->first_name) }}"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" 
                               value="{{ old('last_name', $user->last_name) }}"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    </div>
                </div>

                <!-- Title & Position -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                        <input type="text" id="title" name="title" 
                               value="{{ old('title', $profile->title ?? '') }}"
                               placeholder="e.g., Professor of Computer Science"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    </div>
                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700 mb-2">Position *</label>
                        <select id="position" name="position" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                            <option value="student" {{ old('position', $user->position) == 'student' ? 'selected' : '' }}>Student</option>
                            <option value="researcher" {{ old('position', $user->position) == 'researcher' ? 'selected' : '' }}>Researcher</option>
                            <option value="lecturer" {{ old('position', $user->position) == 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                            <option value="professor" {{ old('position', $user->position) == 'professor' ? 'selected' : '' }}>Professor</option>
                            <option value="phd" {{ old('position', $user->position) == 'phd' ? 'selected' : '' }}>PhD Candidate</option>
                            <option value="other" {{ old('position', $user->position) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>

                <!-- Institution -->
                <div class="mb-6">
                    <label for="institution" class="block text-sm font-medium text-gray-700 mb-2">Institution *</label>
                    <input type="text" id="institution" name="institution" 
                           value="{{ old('institution', $user->institution) }}"
                           required
                           placeholder="University or Research Center"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                </div>

                <!-- Department & Research Interests -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <input type="text" id="department" name="department" 
                               value="{{ old('department', $user->department) }}"
                               placeholder="e.g., Computer Science Department"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    </div>
                    <div>
                        <label for="research_interests" class="block text-sm font-medium text-gray-700 mb-2">Research Interests</label>
                        <input type="text" id="research_interests" name="research_interests" 
                               value="{{ old('research_interests', is_array($user->research_interests) ? implode(', ', $user->research_interests) : $user->research_interests) }}"
                               placeholder="e.g., Machine Learning, Public Health, Climate Change"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                        <p class="text-xs text-gray-500 mt-2">Separate with commas</p>
                    </div>
                </div>

                <!-- Bio -->
                <div class="mb-6">
                    <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                    <textarea id="bio" name="bio" rows="4"
                              placeholder="Tell us about your research, achievements, and interests..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">{{ old('bio', $profile->bio ?? '') }}</textarea>
                </div>

                <!-- Social Links -->
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Social Links</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="website" class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                        <input type="url" id="website" name="website" 
                               value="{{ old('website', $profile->website ?? '') }}"
                               placeholder="https://yourwebsite.com"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    </div>
                    <div>
                        <label for="linkedin" class="block text-sm font-medium text-gray-700 mb-2">LinkedIn</label>
                        <input type="text" id="linkedin" name="linkedin" 
                               value="{{ old('linkedin', $profile->linkedin ?? '') }}"
                               placeholder="https://linkedin.com/in/username"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    </div>
                    <div>
                        <label for="google_scholar" class="block text-sm font-medium text-gray-700 mb-2">Google Scholar</label>
                        <input type="text" id="google_scholar" name="google_scholar" 
                               value="{{ old('google_scholar', $profile->google_scholar ?? '') }}"
                               placeholder="https://scholar.google.com/citations?user=ID"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    </div>
                    <div>
                        <label for="researchgate" class="block text-sm font-medium text-gray-700 mb-2">ResearchGate</label>
                        <input type="text" id="researchgate" name="researchgate" 
                               value="{{ old('researchgate', $profile->researchgate ?? '') }}"
                               placeholder="https://researchgate.net/profile/username"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    </div>
                </div>
            </div>

            <!-- Education Tab (Hidden by default) -->
            <div id="education-content" class="form-card p-6 hidden">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Education</h2>
                <div id="education-container">
                    <!-- Education entries will be added here dynamically -->
                </div>
                <button type="button" onclick="addEducation()" 
                        class="mt-4 bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">
                    <i class="fas fa-plus mr-2"></i>Add Education
                </button>
            </div>

            <!-- Skills Tab (Hidden by default) -->
            <div id="skills-content" class="form-card p-6 hidden">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Skills & Expertise</h2>
                <div class="mb-4">
                    <input type="text" id="skill-input" 
                           placeholder="Add a skill and press Enter"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    <input type="hidden" id="skills" name="skills" value="{{ old('skills', json_encode($profile->skills ?? [])) }}">
                </div>
                <div id="skills-container" class="flex flex-wrap mb-6">
                    <!-- Skills will be added here -->
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4 mt-8">
                <a href="{{ route('profile.show') }}" 
                   class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <script>
        // Tab switching
        document.querySelectorAll('button[id$="-tab"]').forEach(tab => {
            tab.addEventListener('click', function() {
                // Update tabs
                document.querySelectorAll('button[id$="-tab"]').forEach(t => {
                    t.classList.remove('tab-active', 'text-purple-600');
                    t.classList.add('text-gray-600');
                });
                this.classList.add('tab-active', 'text-purple-600');
                this.classList.remove('text-gray-600');

                // Update content
                const tabId = this.id.replace('-tab', '-content');
                document.querySelectorAll('div[id$="-content"]').forEach(content => {
                    content.classList.add('hidden');
                });
                document.getElementById(tabId).classList.remove('hidden');
            });
        });

        // Education management
        function addEducation(education = {}) {
            const container = document.getElementById('education-container');
            const index = container.children.length;
            
            const html = `
                <div class="education-entry border rounded-lg p-4 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Institution</label>
                            <input type="text" name="education[${index}][institution]" 
                                   value="${education.institution || ''}"
                                   class="w-full px-3 py-2 border rounded">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Degree</label>
                            <input type="text" name="education[${index}][degree]" 
                                   value="${education.degree || ''}"
                                   class="w-full px-3 py-2 border rounded">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Field of Study</label>
                            <input type="text" name="education[${index}][field]" 
                                   value="${education.field || ''}"
                                   class="w-full px-3 py-2 border rounded">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                            <input type="text" name="education[${index}][year]" 
                                   value="${education.year || ''}"
                                   class="w-full px-3 py-2 border rounded">
                        </div>
                    </div>
                    <button type="button" onclick="removeEducation(this)" 
                            class="text-red-600 hover:text-red-800 text-sm">
                        <i class="fas fa-trash mr-1"></i>Remove
                    </button>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', html);
        }

        function removeEducation(button) {
            button.closest('.education-entry').remove();
            // Renumber remaining entries
            const container = document.getElementById('education-container');
            container.querySelectorAll('.education-entry').forEach((entry, index) => {
                entry.querySelectorAll('input').forEach(input => {
                    input.name = input.name.replace(/education\[\d+\]/, `education[${index}]`);
                });
            });
        }

        // Skills management
        const skillsInput = document.getElementById('skill-input');
        const skillsHidden = document.getElementById('skills');
        const skillsContainer = document.getElementById('skills-container');
        let skills = JSON.parse(skillsHidden.value || '[]');

        function renderSkills() {
            skillsContainer.innerHTML = '';
            skills.forEach((skill, index) => {
                const tag = document.createElement('div');
                tag.className = 'skill-tag';
                tag.innerHTML = `
                    ${skill}
                    <span class="skill-tag-remove" onclick="removeSkill(${index})">&times;</span>
                `;
                skillsContainer.appendChild(tag);
            });
            skillsHidden.value = JSON.stringify(skills);
        }

        function addSkill(skill) {
            skill = skill.trim();
            if (skill && !skills.includes(skill)) {
                skills.push(skill);
                renderSkills();
                skillsInput.value = '';
            }
        }

        function removeSkill(index) {
            skills.splice(index, 1);
            renderSkills();
        }

        skillsInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addSkill(skillsInput.value);
            }
        });

        // Initialize with existing data
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize skills
            renderSkills();

            // Initialize education from existing data
            const existingEducation = @json($profile->education ?? []);
            existingEducation.forEach(edu => addEducation(edu));
            if (existingEducation.length === 0) {
                addEducation(); // Add one empty field
            }
        });
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>