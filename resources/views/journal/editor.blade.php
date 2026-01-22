<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Journal Editor - Querentia AI')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="{{ asset('js/ai-streaming.js') }}"></script>
    <style>
        .fade-enter-active, .fade-leave-active {
            transition: opacity 0.2s ease;
        }
        .fade-enter-from, .fade-leave-to {
            opacity: 0;
        }
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased" x-data="journalEditor()" x-init="init()">
    <!-- Top Navigation -->
    <nav class="bg-white border-b border-gray-200 fixed top-0 left-0 right-0 z-40">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <!-- Left -->
                <div class="flex items-center space-x-4">
                    <a href="{{ route('network.home') }}" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-blue-500 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-lg">Q</span>
                        </div>
                        <div>
                            <h1 class="font-bold text-gray-900">AI Journal Studio</h1>
                            <p class="text-xs text-gray-500">Transform research into publication-ready journals</p>
                        </div>
                    </div>
                </div> 

                <!-- Right -->
                <div class="flex items-center space-x-4">
                    <!-- Save Status -->
                    <div class="hidden md:block">
                        <span x-text="saveStatus" 
                              :class="{
                                'Saved': 'text-green-600',
                                'Saving...': 'text-yellow-600',
                                'Error': 'text-red-600'
                              }[saveStatus] || 'text-gray-600'"
                              class="text-sm font-medium"></span>
                        <span class="text-gray-400 mx-2">•</span>
                        <span x-text="totalWordCount.toLocaleString()" class="text-sm text-gray-600"></span> words
                        <span class="text-gray-400 mx-2">•</span>
                        <span x-text="completionPercentage + '%'" class="text-sm font-medium text-purple-600"></span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center space-x-2">
                        <button @click="saveJournal()"
                                :disabled="isSaving"
                                :class="isSaving ? 'opacity-75 cursor-not-allowed' : 'hover:bg-green-700'"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium flex items-center">
                            <template x-if="isSaving">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                            </template>
                            <template x-if="!isSaving">
                                <i class="fas fa-save mr-2"></i>
                            </template>
                            <span x-text="isSaving ? 'Saving...' : 'Save'"></span>
                        </button>
                        
                        <template x-if="journalId && canGenerateAI">
                            <button @click="generateAIDraft()"
                                    :disabled="isGeneratingAI || !canGenerateAI"
                                    :class="(isGeneratingAI || !canGenerateAI) ? 'opacity-75 cursor-not-allowed' : 'hover:opacity-90'"
                                    class="px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg text-sm font-medium flex items-center">
                                <i class="fas fa-robot mr-2"></i>
                                <span>AI Generate</span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Editor -->
    <div class="pt-20 flex">
        <!-- Left Sidebar - Sections -->
        <div class="w-64 bg-white border-r border-gray-200 min-h-screen">
            <div class="p-4">
                <!-- Progress at Top -->
                <div class="mb-6 p-4 bg-gradient-to-br from-purple-50 to-blue-50 border border-purple-200 rounded-lg">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-bold text-gray-900">Progress</span>
                        <span x-text="completionPercentage + '%'" class="text-sm font-bold text-purple-600"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div :style="'width: ' + completionPercentage + '%'" 
                             class="bg-gradient-to-r from-purple-600 to-blue-500 h-3 rounded-full transition-all duration-300"></div>
                    </div>
                    <p class="text-xs text-gray-600 mt-2">
                        <span x-text="completedSections"></span> of {{ count($sections) }} sections completed
                    </p>
                </div>
                
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Journal Sections</h2>
                
                <div class="space-y-1">
                    @foreach($sections as $index => $section)
                    <button @click="switchSection({{ $index }})"
                            :class="activeSection === {{ $index }} 
                                ? 'bg-purple-50 border-purple-200 text-purple-700' 
                                : 'hover:bg-gray-50 text-gray-700'"
                            class="w-full flex items-center space-x-3 p-3 border rounded-lg text-left transition-all duration-200 relative group">
                        <!-- Completion Indicator -->
                        <div class="absolute -left-2 top-1/2 transform -translate-y-1/2">
                            <div class="w-2 h-2 rounded-full"
                                 :class="isSectionComplete({{ $index }}) ? 'bg-green-500' : 'bg-gray-300'"></div>
                        </div>
                        
                        <div :class="activeSection === {{ $index }} 
                                ? 'bg-purple-100 text-purple-600' 
                                : 'bg-gray-100 text-gray-500 group-hover:bg-gray-200'"
                             class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors">
                            <i class="{{ $section['icon'] }} text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm truncate">{{ $section['title'] }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $section['subtitle'] }}</p>
                        </div>
                        @if(in_array($index, [0,2,3,6,7,8]))
                            <span class="text-xs text-red-500">*</span>
                        @endif
                    </button>
                    @endforeach
                </div>
                
                <!-- Word Count Summary -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Word Count</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600">Total</span>
                            <span x-text="totalWordCount.toLocaleString()" class="text-sm font-bold text-gray-900"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600">Human Content</span>
                            <span x-text="humanWordCount.toLocaleString()" class="text-sm font-medium text-green-600"></span>
                        </div>
                        <div class="flex justify-between items-center" x-show="aiWordCount > 0">
                            <span class="text-xs text-gray-600">AI Content</span>
                            <span x-text="aiWordCount.toLocaleString()" class="text-sm font-medium text-blue-600"></span>
                        </div>
                        <div class="pt-2 border-t border-gray-100">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-600">AI Ratio</span>
                                <span x-text="aiPercentage + '%'" 
                                      :class="{
                                        'text-green-600': aiPercentage <= 30,
                                        'text-yellow-600': aiPercentage > 30 && aiPercentage <= 50,
                                        'text-red-600': aiPercentage > 50
                                      }"
                                      class="text-sm font-bold"></span>
                            </div>
                            <div class="mt-1 text-xs text-gray-500">
                                <span x-show="aiPercentage > 30" class="text-yellow-600">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Max 30% recommended
                                </span>
                                <span x-show="aiPercentage <= 30" class="text-green-600">
                                    <i class="fas fa-check-circle mr-1"></i>Within guidelines
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Editor Area -->
        <div class="flex-1 p-8">
            <!-- Current Section Header -->
            <div class="mb-8">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-blue-500 rounded-lg flex items-center justify-center">
                        <i class="text-white" :class="sections[activeSection].icon"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900" x-text="sections[activeSection].title"></h2>
                                <p class="text-gray-600" x-text="sections[activeSection].subtitle"></p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <template x-if="[0,2,3,6,7,8].includes(activeSection)">
                                    <span class="text-xs text-red-500 font-medium">Required</span>
                                </template>
                                <button @click="enhanceWithAI()"
                                        :disabled="!canEnhanceSection || isEnhancing"
                                        :class="(!canEnhanceSection || isEnhancing) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-purple-50'"
                                        class="px-3 py-1.5 text-sm border border-purple-200 text-purple-600 rounded-lg flex items-center transition-colors">
                                    <template x-if="isEnhancing">
                                        <i class="fas fa-spinner fa-spin mr-1"></i>
                                    </template>
                                    <template x-if="!isEnhancing">
                                        <i class="fas fa-magic mr-1"></i>
                                    </template>
                                    <span x-text="isEnhancing ? 'Enhancing...' : 'AI Enhance'"></span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- AI Tip -->
                        <div class="mt-3 bg-blue-50 border border-blue-100 rounded-lg p-3">
                            <div class="flex items-start">
                                <i class="fas fa-robot text-blue-500 mt-0.5 mr-2"></i>
                                <div class="flex-1">
                                    <p class="text-xs text-blue-800" x-text="sections[activeSection].aiTip"></p>
                                    <template x-if="activeSection === 1">
                                        <div class="mt-1 text-xs text-blue-700">
                                            <p>Click "Add Author" below to add more authors</p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Editor Content -->
            <div class="space-y-6">
                <!-- Research Topic Section (0) -->
                <template x-if="activeSection === 0">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Research Topic Title *
                            </label>
                            <input type="text" 
                                   x-model="sections[0].content"
                                   @input="debounceSave()"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-lg font-medium"
                                   placeholder="Enter your research topic...">
                        </div>
                        <div class="flex justify-end">
                            <div class="text-xs text-gray-500">
                                <span x-text="getWordCount(sections[0].content)"></span> words
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Authors Section (1) -->
                <template x-if="activeSection === 1">
                    <div class="space-y-6">
                        <div class="flex justify-between items-center">
                            <h3 class="font-medium text-gray-900">Authors & Affiliations</h3>
                            <button @click="addAuthor()"
                                    class="px-3 py-1.5 text-sm bg-purple-50 text-purple-600 hover:bg-purple-100 rounded-lg transition-colors flex items-center">
                                <i class="fas fa-user-plus mr-1"></i>Add Author
                            </button>
                        </div>
                        
                        <div class="space-y-4" id="authors-list">
                            <template x-for="(author, index) in sections[1].authors" :key="'author-' + index">
                                <div class="border border-gray-200 rounded-lg p-4 bg-white hover:bg-gray-50 transition-colors"
                                     x-transition:enter="fade-enter-active"
                                     x-transition:leave="fade-leave-active">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Full Name *</label>
                                            <input type="text" 
                                                   x-model="sections[1].authors[index].name"
                                                   @input="debounceSave()"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-purple-500 focus:border-transparent"
                                                   placeholder="John Doe">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Affiliation *</label>
                                            <input type="text" 
                                                   x-model="sections[1].authors[index].affiliation"
                                                   @input="debounceSave()"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-purple-500 focus:border-transparent"
                                                   placeholder="University of ...">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                                            <input type="email" 
                                                   x-model="sections[1].authors[index].email"
                                                   @input="debounceSave()"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-purple-500 focus:border-transparent"
                                                   placeholder="john@university.edu">
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" 
                                                   x-model="sections[1].authors[index].corresponding"
                                                   @change="updateCorrespondingAuthor(index); debounceSave()"
                                                   class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 cursor-pointer">
                                            <span class="ml-2 text-sm text-gray-700">Corresponding Author</span>
                                        </label>
                                        <button @click="removeAuthor(index)" 
                                                :disabled="sections[1].authors.length <= 1"
                                                :class="sections[1].authors.length <= 1 
                                                    ? 'opacity-50 cursor-not-allowed' 
                                                    : 'hover:text-red-800 hover:bg-red-50'"
                                                class="px-2 py-1 text-red-600 text-sm rounded transition-colors flex items-center">
                                            <i class="fas fa-trash mr-1"></i>Remove
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        <div class="text-center py-4 border-t border-gray-200" x-show="sections[1].authors.length === 0">
                            <i class="fas fa-users text-3xl text-gray-300 mb-2"></i>
                            <p class="text-gray-500">No authors added yet</p>
                            <button @click="addAuthor()"
                                    class="mt-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                Add First Author
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Text Sections (2-9) -->
                <template x-if="activeSection >= 2 && activeSection <= 9">
                    <div class="space-y-4">
                        <!-- Word Count Recommendations -->
                        <template x-if="[2,3,6,7,8].includes(activeSection)">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-2 sm:space-y-0 p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-600">
                                    <span class="font-medium">Recommended: </span>
                                    <template x-if="activeSection === 2">150-250 words</template>
                                    <template x-if="activeSection === 3">300-500 words</template>
                                    <template x-if="activeSection === 6">200-400 words</template>
                                    <template x-if="activeSection === 7">400-600 words</template>
                                    <template x-if="activeSection === 8">150-250 words</template>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="text-xs font-medium" 
                                         :class="getWordCountClass(activeSection, sections[activeSection].content)">
                                        <span x-text="getWordCount(sections[activeSection].content)"></span> words
                                    </div>
                                    <template x-if="getWordCountClass(activeSection, sections[activeSection].content) === 'text-green-600'">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i> Good
                                        </span>
                                    </template>
                                    <template x-else-if="getWordCountClass(activeSection, sections[activeSection].content) === 'text-yellow-600'">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-exclamation mr-1"></i> Add more
                                        </span>
                                    </template>
                                    <template x-else-if="getWordCountClass(activeSection, sections[activeSection].content) === 'text-red-600'">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Too long
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </template>
                        
                        <!-- Textarea -->
                        <textarea x-model="sections[activeSection].content"
                                  @input="debounceSave()"
                                  :rows="getTextareaRows(activeSection)"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-y font-sans"
                                  :placeholder="sections[activeSection].placeholder"></textarea>
                        
                        <!-- Simple Word Count for Other Sections -->
                        <template x-if="![2,3,6,7,8].includes(activeSection)">
                            <div class="flex justify-end">
                                <div class="text-xs text-gray-500">
                                    <span x-text="getWordCount(sections[activeSection].content)"></span> words
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Annexes Section (10) -->
                <template x-if="activeSection === 10">
                    <div class="space-y-6">
                        <!-- Upload Area -->
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-purple-400 transition-colors cursor-pointer"
                             @dragover.prevent="dragOver = true"
                             @dragleave.prevent="dragOver = false"
                             @drop.prevent="handleFileDrop($event, 'annex')"
                             :class="dragOver ? 'border-purple-500 bg-purple-50' : ''"
                             @click="triggerFileUpload('annex')">
                            <i class="fas fa-paperclip text-3xl text-gray-400 mb-3"></i>
                            <p class="text-gray-600 mb-2 font-medium">Drag and drop files here or click to upload</p>
                            <p class="text-sm text-gray-500 mb-4">Supports PDF, DOCX, XLSX, images (max 10MB each)</p>
                            <button class="mt-4 px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                                Browse Files
                            </button>
                            <input type="file" id="annex-upload" class="hidden" multiple>
                        </div>
                        
                        <!-- Uploaded Files List -->
                        <div class="space-y-3" x-show="sections[10].files && sections[10].files.length > 0">
                            <h4 class="font-medium text-gray-900 mb-3 text-lg">Uploaded Files</h4>
                            <template x-for="(file, index) in sections[10].files" :key="'file-' + index">
                                <div class="border border-gray-200 rounded-lg p-4 bg-white hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-gradient-to-br from-purple-100 to-blue-100 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-file text-purple-600"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="font-medium text-gray-900 truncate" x-text="file.name"></p>
                                                <div class="flex items-center space-x-3 mt-1">
                                                    <span class="text-xs text-gray-500" x-text="formatFileSize(file.size)"></span>
                                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">
                                                        <span x-text="(file.type || '').split('/')[1]?.toUpperCase() || 'FILE'"></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <a :href="file.url" 
                                               target="_blank" 
                                               class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors"
                                               title="Preview">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button @click="removeFile(10, index)" 
                                                    class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors"
                                                    title="Remove">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Maps & Figures Section (11) -->
                <template x-if="activeSection === 11">
                    <div class="space-y-6">
                        <!-- Upload Area -->
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-purple-400 transition-colors cursor-pointer"
                             @dragover.prevent="dragOver = true"
                             @dragleave.prevent="dragOver = false"
                             @drop.prevent="handleFileDrop($event, 'figure')"
                             :class="dragOver ? 'border-purple-500 bg-purple-50' : ''"
                             @click="triggerFileUpload('figure')">
                            <i class="fas fa-images text-3xl text-gray-400 mb-3"></i>
                            <p class="text-gray-600 mb-2 font-medium">Drag and drop images here or click to upload</p>
                            <p class="text-sm text-gray-500 mb-4">Supports JPG, PNG, SVG, GIF (max 5MB each)</p>
                            <button class="mt-4 px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                                Browse Images
                            </button>
                            <input type="file" id="figure-upload" class="hidden" accept="image/*" multiple>
                        </div>
                        
                        <!-- Uploaded Images Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" 
                             x-show="sections[11].figures && sections[11].figures.length > 0">
                            <div class="col-span-full">
                                <h4 class="font-medium text-gray-900 mb-3 text-lg">Uploaded Visuals</h4>
                            </div>
                            <template x-for="(figure, index) in sections[11].figures" :key="'figure-' + index">
                                <div class="border border-gray-200 rounded-xl overflow-hidden bg-white hover:shadow-md transition-shadow">
                                    <div class="p-5">
                                        <!-- Figure Header -->
                                        <div class="flex justify-between items-start mb-4">
                                            <div class="flex-1">
                                                <div class="flex items-center">
                                                    <span class="inline-flex items-center justify-center w-8 h-8 bg-purple-100 text-purple-700 rounded-full text-sm font-bold mr-2">
                                                        <span x-text="index + 1"></span>
                                                    </span>
                                                    <div>
                                                        <p class="font-medium text-gray-900">Figure <span x-text="index + 1"></span></p>
                                                        <p class="text-xs text-gray-500 truncate max-w-[200px]" 
                                                           x-text="figure.caption || 'Add a caption...'"></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <button @click="removeFigure(index)" 
                                                    class="ml-2 p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- Image Preview -->
                                        <div class="bg-gray-50 h-48 rounded-lg flex items-center justify-center overflow-hidden mb-4">
                                            <template x-if="figure.url">
                                                <img :src="figure.url" 
                                                     :alt="figure.caption || 'Figure ' + (index + 1)"
                                                     class="max-h-full max-w-full object-contain p-2">
                                            </template>
                                            <template x-if="!figure.url && figure.file">
                                                <div class="text-center">
                                                    <i class="fas fa-image text-gray-300 text-4xl mb-2"></i>
                                                    <p class="text-xs text-gray-400">Uploading...</p>
                                                </div>
                                            </template>
                                        </div>
                                        
                                        <!-- Caption Input -->
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Figure Caption</label>
                                                <textarea x-model="sections[11].figures[index].caption"
                                                          @input="debounceSave()"
                                                          rows="2"
                                                          class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-purple-500 focus:border-transparent resize-none"
                                                          placeholder="Describe this figure..."></textarea>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Source/Citation</label>
                                                <input type="text" 
                                                       x-model="sections[11].figures[index].source"
                                                       @input="debounceSave()"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-purple-500 focus:border-transparent"
                                                       placeholder="Source or citation (if applicable)...">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Navigation -->
            <div class="mt-8 flex justify-between">
                <button @click="previousSection()"
                        :disabled="activeSection === 0 || isNavigating"
                        :class="(activeSection === 0 || isNavigating) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'"
                        class="px-6 py-3 border border-gray-300 rounded-lg font-medium flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Previous
                </button>
                
                <template x-if="activeSection === {{ count($sections) - 1 }}">
                    <button @click="generateAIDraft()"
                            :disabled="!canGenerateAI || isGeneratingAI"
                            :class="(!canGenerateAI || isGeneratingAI) ? 'opacity-75 cursor-not-allowed' : 'hover:opacity-90'"
                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg font-medium flex items-center transition-all">
                        <template x-if="isGeneratingAI">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            <span>Generating...</span>
                        </template>
                        <template x-if="!isGeneratingAI">
                            <i class="fas fa-robot mr-2"></i>
                            <span>Generate AI Draft</span>
                        </template>
                    </button>
                </template>
                
                <template x-if="activeSection !== {{ count($sections) - 1 }}">
                    <button @click="nextSection()"
                            :disabled="isNavigating"
                            :class="isNavigating ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90'"
                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg font-medium flex items-center transition-all">
                        Next <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <!-- AI Generation Modal -->
    <div id="ai-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-blue-500 text-white p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold">AI Journal Generation</h3>
                        <p id="ai-status" class="text-purple-200 text-sm mt-1">DeepSeek AI is writing your journal...</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="text-sm bg-white/20 px-3 py-1 rounded-full">Live Preview</span>
                        <button @click="closeAIModal()" 
                                class="text-white hover:text-gray-200 p-2 rounded-full hover:bg-white/10 transition-colors">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <!-- Progress -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Progress</span>
                        <span id="ai-progress-percent" class="text-sm font-bold text-purple-600">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="ai-progress-bar" class="bg-gradient-to-r from-purple-500 to-blue-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
                
                <!-- Live Preview -->
                <div class="border rounded-lg p-4 bg-gray-50 max-h-[50vh] overflow-y-auto">
                    <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-eye mr-2 text-purple-500"></i>Live Preview
                    </h4>
                    <div id="journal-preview" class="font-sans text-gray-800 bg-white p-4 rounded border min-h-[40vh] leading-relaxed">
                        <div class="text-center py-8 text-gray-400">
                            <i class="fas fa-robot text-3xl mb-3 animate-pulse"></i>
                            <p>AI-generated content will appear here...</p>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="mt-6 flex justify-end space-x-3">
                    <button @click="closeAIModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors font-medium">
                        Cancel
                    </button>
                    <button id="save-ai-draft" 
                    @click="confirmSaveAIDraft()"
                    disabled
                    class="px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed font-medium flex items-center">
                         <i class="fas fa-paper-plane mr-2"></i>Post for Review
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Enhancement Modal -->
    <div id="enhance-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl">
            <div class="bg-gradient-to-r from-purple-600 to-blue-500 text-white p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold">AI Section Enhancement</h3>
                        <p class="text-purple-200 text-sm mt-1" id="enhance-status">Enhancing your content...</p>
                    </div>
                    <button onclick="document.getElementById('enhance-modal').classList.add('hidden')" 
                            class="text-white hover:text-gray-200 p-2 rounded-full hover:bg-white/10 transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Progress</span>
                        <span id="enhance-progress-percent" class="text-sm font-bold text-purple-600">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="enhance-progress-bar" class="bg-gradient-to-r from-purple-500 to-blue-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
                
                <div class="border rounded-lg p-4 bg-gray-50 max-h-[40vh] overflow-y-auto">
                    <h4 class="font-medium text-gray-900 mb-3">Enhanced Content</h4>
                    <div id="enhanced-content" class="font-sans text-gray-800 bg-white p-4 rounded border min-h-[30vh] leading-relaxed">
                        <!-- Enhanced content will appear here -->
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button onclick="document.getElementById('enhance-modal').classList.add('hidden')"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors font-medium">
                        Cancel
                    </button>
                    <button id="apply-enhancement" 
                            onclick="applyEnhancedContent()"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium flex items-center">
                        <i class="fas fa-check mr-2"></i>Apply Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Store sections data in a global variable before Alpine initialization
        const initialSections = {!! json_encode($sections, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!};
        const initialJournalId = {{ $journal->id ?? 'null' }};
        
        // Helper function to get CSRF token
        function getCsrfToken() {
            const token = document.querySelector('meta[name="csrf-token"]');
            return token ? token.getAttribute('content') : '{{ csrf_token() }}';
        }

        // Global variables for AI streaming
        let aiStreamController = null;
        let enhanceStreamController = null;
        
        document.addEventListener('alpine:init', () => {
            Alpine.data('journalEditor', () => ({
                // State variables
                activeSection: 0,
                saveStatus: 'Saved',
                isSaving: false,
                isGeneratingAI: false,
                isEnhancing: false,
                isNavigating: false,
                dragOver: false,
                journalId: initialJournalId,
                sections: initialSections,
                
                // Initialize sections with proper data structure
                initSections() {
                    this.sections.forEach((section, index) => {
                        switch(index) {
                            case 1: // Authors
                                if (!section.authors || !Array.isArray(section.authors)) {
                                    section.authors = [{
                                        name: '{{ auth()->user()->full_name ?? "Your Name" }}',
                                        affiliation: '{{ auth()->user()->institution ?? "Your Institution" }}',
                                        email: '{{ auth()->user()->email ?? "" }}',
                                        corresponding: true
                                    }];
                                }
                                break;
                                
                            case 10: // Annexes
                                if (!section.files) section.files = [];
                                break;
                                
                            case 11: // Maps & Figures
                                if (!section.figures) section.figures = [];
                                break;
                                
                            default:
                                if (typeof section.content === 'undefined') {
                                    section.content = '';
                                }
                        }
                    });
                },
                
                init() {
                    console.log('Initializing journal editor...');
                    
                    // Initialize sections
                    this.initSections();
                    
                    // Load existing data if editing
                    @if(isset($existing_data))
                        this.loadExistingData({!! json_encode($existing_data, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!});
                    @endif
                    
                    // Setup file upload listeners
                    this.setupFileUploads();
                    
                    // Auto-save when idle
                    this.setupAutoSave();
                    
                    console.log('Editor initialized with journal ID:', this.journalId);
                },
                
                // Load existing journal data
                loadExistingData(data) {
                    Object.keys(data).forEach(key => {
                        const sectionIndex = this.sections.findIndex(s => s.key === key);
                        if (sectionIndex !== -1) {
                            if (key === 'authors' && Array.isArray(data[key])) {
                                this.sections[sectionIndex].authors = data[key];
                            } else {
                                this.sections[sectionIndex].content = data[key] || '';
                            }
                        }
                    });
                },
                
                // Setup auto-save on idle
                setupAutoSave() {
                    let saveTimeout = null;
                    
                    // Auto-save when user stops typing for 2 seconds
                    this.$watch('sections', () => {
                        if (saveTimeout) clearTimeout(saveTimeout);
                        if (this.journalId) {
                            saveTimeout = setTimeout(() => {
                                this.saveJournal(true); // silent save
                            }, 2000);
                        }
                    }, { deep: true });
                },
                
                // ====================
                // SECTION MANAGEMENT
                // ====================
                
                switchSection(index) {
                    this.activeSection = index;
                    this.scrollToTop();
                },
                
                nextSection() {
                    if (this.activeSection < this.sections.length - 1) {
                        this.isNavigating = true;
                        this.activeSection++;
                        this.scrollToTop();
                        setTimeout(() => this.isNavigating = false, 300);
                    }
                },
                
                previousSection() {
                    if (this.activeSection > 0) {
                        this.isNavigating = true;
                        this.activeSection--;
                        this.scrollToTop();
                        setTimeout(() => this.isNavigating = false, 300);
                    }
                },
                
                scrollToTop() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                
                // ====================
                // AUTHORS MANAGEMENT
                // ====================
                
                addAuthor() {
                    const newAuthor = {
                        name: '',
                        affiliation: '',
                        email: '',
                        corresponding: false
                    };
                    
                    // If no authors yet, set as corresponding
                    if (this.sections[1].authors.length === 0) {
                        newAuthor.corresponding = true;
                    }
                    
                    this.sections[1].authors.push(newAuthor);
                    this.debounceSave();
                },
                
                removeAuthor(index) {
                    if (this.sections[1].authors.length <= 1) return;
                    
                    const wasCorresponding = this.sections[1].authors[index].corresponding;
                    this.sections[1].authors.splice(index, 1);
                    
                    // If we removed the corresponding author, set first as corresponding
                    if (wasCorresponding && this.sections[1].authors.length > 0) {
                        this.sections[1].authors[0].corresponding = true;
                    }
                    
                    this.debounceSave();
                },
                
                updateCorrespondingAuthor(selectedIndex) {
                    this.sections[1].authors.forEach((author, index) => {
                        author.corresponding = (index === selectedIndex);
                    });
                    this.debounceSave();
                },
                
                // ====================
                // FILE MANAGEMENT
                // ====================
                
                setupFileUploads() {
                    // Annex upload
                    const annexInput = document.getElementById('annex-upload');
                    if (annexInput) {
                        annexInput.addEventListener('change', (e) => {
                            Array.from(e.target.files).forEach(file => 
                                this.uploadFile(file, 'annex')
                            );
                        });
                    }
                    
                    // Figure upload
                    const figureInput = document.getElementById('figure-upload');
                    if (figureInput) {
                        figureInput.addEventListener('change', (e) => {
                            Array.from(e.target.files).forEach(file => 
                                this.uploadFile(file, 'figure')
                            );
                        });
                    }
                },
                
                triggerFileUpload(type) {
                    const inputId = type === 'annex' ? 'annex-upload' : 'figure-upload';
                    document.getElementById(inputId).click();
                },
                
                handleFileDrop(event, type) {
                    event.preventDefault();
                    this.dragOver = false;
                    
                    const files = Array.from(event.dataTransfer.files);
                    files.forEach(file => this.uploadFile(file, type));
                },
                
                async uploadFile(file, type) {
                    // Validate file size
                    const maxSize = type === 'annex' ? 10 * 1024 * 1024 : 5 * 1024 * 1024;
                    if (file.size > maxSize) {
                        alert(`File too large. Maximum size for ${type}s is ${type === 'annex' ? '10MB' : '5MB'}.`);
                        return;
                    }
                    
                    // Validate file types
                    if (type === 'figure') {
                        const validImageTypes = ['image/jpeg', 'image/png', 'image/svg+xml', 'image/gif'];
                        if (!validImageTypes.includes(file.type)) {
                            alert('Please upload only image files (JPG, PNG, SVG, GIF).');
                            return;
                        }
                    }
                    
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('type', type);
                    formData.append('journal_id', this.journalId || 'new');
                    
                    this.saveStatus = `Uploading ${type}...`;
                    
                    try {
                        const response = await fetch('/api/upload/file', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                            },
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            if (type === 'annex') {
                                if (!this.sections[10].files) this.sections[10].files = [];
                                this.sections[10].files.push({
                                    name: data.file.original_name,
                                    url: data.file.url,
                                    size: data.file.size,
                                    type: data.file.type
                                });
                            } else if (type === 'figure') {
                                if (!this.sections[11].figures) this.sections[11].figures = [];
                                this.sections[11].figures.push({
                                    name: data.file.original_name,
                                    url: data.file.url,
                                    caption: '',
                                    source: '',
                                    size: data.file.size,
                                    type: data.file.type,
                                    file: file // Keep reference for preview
                                });
                            }
                            
                            this.saveStatus = 'Saved';
                            this.debounceSave();
                        } else {
                            throw new Error(data.message || 'Upload failed');
                        }
                    } catch (error) {
                        this.saveStatus = 'Error';
                        alert(`Upload error: ${error.message}`);
                    }
                },
                
                removeFile(sectionIndex, fileIndex) {
                    if (this.sections[sectionIndex].files && 
                        this.sections[sectionIndex].files[fileIndex]) {
                        this.sections[sectionIndex].files.splice(fileIndex, 1);
                        this.debounceSave();
                    }
                },
                
                removeFigure(figureIndex) {
                    if (this.sections[11].figures && 
                        this.sections[11].figures[figureIndex]) {
                        this.sections[11].figures.splice(figureIndex, 1);
                        this.debounceSave();
                    }
                },
                
                // ====================
                // TEXT UTILITIES
                // ====================
                
                getTextareaRows(sectionIndex) {
                    const rowMap = {
                        0: 2,   // Research Topic
                        2: 8,   // Abstract
                        3: 12,  // Introduction
                        4: 4,   // Area of Study
                        5: 6,   // Additional Notes
                        6: 8,   // Materials & Methods
                        7: 12,  // Results & Discussion
                        8: 6,   // Conclusion
                        9: 12,  // References
                    };
                    return rowMap[sectionIndex] || 6;
                },
                
                getWordCount(text) {
                    if (!text || !text.trim()) return 0;
                    return text.trim().split(/\s+/).length;
                },
                
                getWordCountClass(sectionIndex, content) {
                    const wordCount = this.getWordCount(content);
                    const thresholds = {
                        2: { min: 150, max: 250 }, // Abstract
                        3: { min: 300, max: 500 }, // Introduction
                        6: { min: 200, max: 400 }, // Materials & Methods
                        7: { min: 400, max: 600 }, // Results & Discussion
                        8: { min: 150, max: 250 }, // Conclusion
                    };
                    
                    if (thresholds[sectionIndex]) {
                        if (wordCount === 0) return 'text-gray-600';
                        if (wordCount < thresholds[sectionIndex].min) return 'text-yellow-600';
                        if (wordCount > thresholds[sectionIndex].max) return 'text-red-600';
                        return 'text-green-600';
                    }
                    return 'text-gray-600';
                },
                
                formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                },
                
                // ====================
                // COMPLETION TRACKING
                // ====================
                
                get totalWordCount() {
                    let count = 0;
                    this.sections.forEach(section => {
                        if (section.content) {
                            count += this.getWordCount(section.content);
                        }
                        if (section.authors) {
                            section.authors.forEach(author => {
                                if (author.name) count += this.getWordCount(author.name);
                                if (author.affiliation) count += this.getWordCount(author.affiliation);
                            });
                        }
                    });
                    return count;
                },
                
                get humanWordCount() {
                    // For now, assume all content is human-written
                    // In a real app, you'd track which content was AI-generated
                    return this.totalWordCount;
                },
                
                get aiWordCount() {
                    // This would come from your database
                    return 0;
                },
                
                get aiPercentage() {
                    if (this.totalWordCount === 0) return 0;
                    return Math.round((this.aiWordCount / this.totalWordCount) * 100);
                },
                
                get completedSections() {
                    let completed = 0;
                    this.sections.forEach((section, index) => {
                        if (this.isSectionComplete(index)) {
                            completed++;
                        }
                    });
                    return completed;
                },
                
                get completionPercentage() {
                    const total = this.sections.length;
                    return Math.round((this.completedSections / total) * 100);
                },
                
                isSectionComplete(index) {
                    const section = this.sections[index];
                    
                    switch(index) {
                        case 0: // Research Topic
                            return section.content && section.content.trim().length >= 5;
                            
                        case 1: // Authors
                            return section.authors && section.authors.length > 0 && 
                                   section.authors.some(author => 
                                       author.name && author.name.trim() && 
                                       author.affiliation && author.affiliation.trim()
                                   );
                            
                        case 2: // Abstract
                        case 3: // Introduction
                        case 6: // Methodology
                        case 7: // Results & Discussion
                        case 8: // Conclusion
                            return section.content && section.content.trim().length >= 50;
                            
                        case 4: // Area of Study
                        case 5: // Additional Notes
                        case 9: // References
                            return true; // Optional sections
                            
                        case 10: // Annexes
                        case 11: // Maps & Figures
                            return true; // File sections are optional
                            
                        default:
                            return false;
                    }
                },
                
                get canGenerateAI() {
                    // Check if required sections are complete
                    const requiredSections = [0, 1, 2, 3, 6, 7, 8];
                    return requiredSections.every(index => this.isSectionComplete(index));
                },
                
                get canEnhanceSection() {
                    const section = this.sections[this.activeSection];
                    return section.content && section.content.trim().length >= 20;
                },
                
                // ====================
                // SAVE OPERATIONS
                // ====================
                
                debounceSave() {
                    if (this.saveDebounce) clearTimeout(this.saveDebounce);
                    this.saveDebounce = setTimeout(() => {
                        if (this.journalId) {
                            this.saveJournal(true); // silent save
                        }
                    }, 1000);
                },
                
                async saveJournal(silent = false) {
                    if (this.isSaving) return false;
                    
                    this.isSaving = true;
                    if (!silent) this.saveStatus = 'Saving...';
                    
                    try {
                        // Prepare sections data
                        const sectionsData = this.prepareSectionsData();
                        
                        const response = await fetch('/api/journal/save', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken()
                            },
                            body: JSON.stringify({
                                title: this.sections[0].content || 'Untitled Journal',
                                sections: sectionsData,
                                journal_id: this.journalId || null
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.journalId = data.journal.id;
                            this.saveStatus = 'Saved';
                            if (!silent) {
                                this.showNotification('Journal saved successfully', 'success');
                            }
                            return true;
                        } else {
                            throw new Error(data.message || 'Failed to save journal');
                        }
                    } catch (error) {
                        console.error('Save error:', error);
                        this.saveStatus = 'Error';
                        if (!silent) {
                            this.showNotification('Failed to save: ' + error.message, 'error');
                        }
                        return false;
                    } finally {
                        this.isSaving = false;
                    }
                },
                
                prepareSectionsData() {
                    return this.sections.map((section, index) => {
                        if (index === 1) {
                            // Authors section
                            return section.authors;
                        } else if ([10, 11].includes(index)) {
                            // File sections - skip for now (handled separately)
                            return { content: '' };
                        } else {
                            // Text sections
                            return {
                                content: section.content || ''
                            };
                        }
                    });
                },
                
                // ====================
                // AI OPERATIONS
                // ====================
                
                async enhanceWithAI() {
                    if (!this.canEnhanceSection || this.isEnhancing) return;
                    
                    this.isEnhancing = true;
                    const section = this.sections[this.activeSection];
                    const sectionTitles = [
                        'Research Topic', 'Authors', 'Abstract', 'Introduction',
                        'Area of Study', 'Additional Notes', 'Methodology',
                        'Results & Discussion', 'Conclusion', 'References'
                    ];
                    
                    try {
                        // Show enhancement modal
                        const modal = document.getElementById('enhance-modal');
                        modal.classList.remove('hidden');
                        
                        // Reset progress
                        document.getElementById('enhance-progress-bar').style.width = '0%';
                        document.getElementById('enhance-progress-percent').textContent = '0%';
                        document.getElementById('enhanced-content').innerHTML = '<div class="text-center py-8 text-gray-400"><i class="fas fa-robot text-3xl mb-3 animate-pulse"></i><p>AI is enhancing your content...</p></div>';
                        document.getElementById('apply-enhancement').disabled = true;
                        
                        // Determine enhancement type based on section
                        let enhancementType = 'enhance';
                        if (this.activeSection === 2) enhancementType = 'abstract';
                        if (this.activeSection === 9) enhancementType = 'references';
                        if (this.activeSection === 8) enhancementType = 'conclusion';
                        
                        // Start streaming enhancement
                        await this.streamSectionEnhancement(
                            section.content,
                            enhancementType,
                            document.getElementById('enhanced-content'),
                            document.getElementById('enhance-progress-bar'),
                            document.getElementById('enhance-progress-percent'),
                            document.getElementById('enhance-status')
                        );
                        
                        document.getElementById('apply-enhancement').disabled = false;
                        
                    } catch (error) {
                        console.error('Enhancement error:', error);
                        this.showNotification('AI enhancement failed: ' + error.message, 'error');
                        document.getElementById('enhance-modal').classList.add('hidden');
                    } finally {
                        this.isEnhancing = false;
                    }
                },
                
                async streamSectionEnhancement(content, type, outputElement, progressBar, progressPercent, statusElement) {
                    return new Promise((resolve, reject) => {
                        // Abort any existing stream
                        if (enhanceStreamController) {
                            enhanceStreamController.abort();
                        }
                        
                        enhanceStreamController = new AbortController();
                        let fullContent = '';
                        let chunkCount = 0;
                        
                        fetch('/api/ai/enhance-section', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken()
                            },
                            body: JSON.stringify({
                                content: content,
                                section_type: type,
                                journal_id: this.journalId
                            }),
                            signal: enhanceStreamController.signal
                        })
                        .then(response => {
                            const reader = response.body.getReader();
                            const decoder = new TextDecoder();
                            
                            function readStream() {
                                reader.read().then(({ done, value }) => {
                                    if (done) {
                                        if (statusElement) {
                                            statusElement.textContent = 'Enhancement complete!';
                                        }
                                        if (progressBar) {
                                            progressBar.style.width = '100%';
                                        }
                                        if (progressPercent) {
                                            progressPercent.textContent = '100%';
                                        }
                                        enhanceStreamController = null;
                                        resolve(fullContent);
                                        return;
                                    }
                                    
                                    const chunk = decoder.decode(value, { stream: true });
                                    const lines = chunk.split('\n');
                                    
                                    lines.forEach(line => {
                                        if (line.startsWith('data: ')) {
                                            const data = line.substring(6);
                                            
                                            try {
                                                const parsed = JSON.parse(data);
                                                
                                                if (parsed.chunk) {
                                                    chunkCount++;
                                                    fullContent += parsed.chunk;
                                                    
                                                    // Update output
                                                    if (chunkCount === 1) {
                                                        outputElement.innerHTML = '';
                                                    }
                                                    outputElement.innerHTML += parsed.chunk;
                                                    outputElement.scrollTop = outputElement.scrollHeight;
                                                    
                                                    // Update progress
                                                    if (progressBar && progressPercent) {
                                                        const progress = Math.min((chunkCount / 50) * 100, 99);
                                                        progressBar.style.width = `${progress}%`;
                                                        progressPercent.textContent = `${Math.round(progress)}%`;
                                                    }
                                                }
                                                
                                                if (parsed.message && statusElement) {
                                                    statusElement.textContent = parsed.message;
                                                }
                                                
                                            } catch (e) {
                                                console.error('Failed to parse SSE data:', e);
                                            }
                                        }
                                    });
                                    
                                    readStream();
                                }).catch(reject);
                            }
                            
                            readStream();
                        })
                        .catch(reject);
                    });
                },
                
                async generateAIDraft() {
                    if (!this.canGenerateAI || this.isGeneratingAI) return;
                    
                    // First save the journal
                    const saved = await this.saveJournal();
                    if (!saved) {
                        this.showNotification('Please save the journal before generating AI draft.', 'error');
                        return;
                    }
                    
                    this.isGeneratingAI = true;
                    
                    try {
                        // Show AI modal
                        this.showAIModal();
                        
                        // Prepare sections for AI
                        const sectionsData = this.prepareAISectionsData();
                        
                        // Start AI streaming
                        await this.streamAIJournal(sectionsData);
                        
                    } catch (error) {
                        console.error('AI generation error:', error);
                        this.showNotification('AI generation failed: ' + error.message, 'error');
                        this.closeAIModal();
                    } finally {
                        this.isGeneratingAI = false;
                    }
                },

                // Add these methods inside the journalEditor Alpine component:

confirmSaveAIDraft() {
    if (confirm('Are you ready to post this AI-generated journal for review? Querentia users will be able to provide feedback.')) {
        this.saveAIDraft();
    }
},

async saveAIDraft() {
    const previewContent = document.getElementById('journal-preview').innerText;
    
    if (!this.journalId || !previewContent.trim()) {
        this.showNotification('No AI content to save.', 'error');
        return;
    }
    
    const button = document.getElementById('save-ai-draft');
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Posting...';
    
    try {
        const response = await fetch('/api/journal/save-ai-draft', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                journal_id: this.journalId,
                ai_content: previewContent,
                provider: 'deepseek'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            this.showNotification('Journal posted for review successfully! Querentia users will now review it.', 'success');
            this.closeAIModal();
            
            // Redirect to network home after delay
            setTimeout(() => {
                window.location.href = data.redirect_url || '/network';
            }, 1500);
            
        } else {
            throw new Error(data.message || 'Failed to save AI draft');
        }
    } catch (error) {
        console.error('Save AI Draft Error:', error);
        this.showNotification('Failed to post for review: ' + error.message, 'error');
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Post for Review';
    }
},
                
                prepareAISectionsData() {
                    const sectionsData = {};
                    this.sections.forEach((section, index) => {
                        if (index === 1 && section.authors) {
                            // Format authors
                            const authorsText = section.authors.map(author => {
                                let text = `${author.name} (${author.affiliation})`;
                                if (author.email) text += ` - ${author.email}`;
                                if (author.corresponding) text += ' [Corresponding Author]';
                                return text;
                            }).join('\n');
                            sectionsData[index] = {
                                title: section.title,
                                content: authorsText
                            };
                        } else if ([10, 11].includes(index)) {
                            // File sections
                            sectionsData[index] = {
                                title: section.title,
                                content: `[${section.files?.length || 0} ${index === 10 ? 'files' : 'images'} uploaded]`
                            };
                        } else {
                            sectionsData[index] = {
                                title: section.title,
                                content: section.content || ''
                            };
                        }
                    });
                    return sectionsData;
                },
                
async streamAIJournal(sectionsData) {
    return new Promise((resolve, reject) => {
        // Abort any existing stream
        if (aiStreamController) {
            aiStreamController.abort();
        }
        
        // FIX: Build the correct URL based on whether we have journalId
        let url = '/ai/stream';
        if (this.journalId) {
            url = `/ai/stream/${this.journalId}`;
        }
        
        aiStreamController = new AbortController();
        let fullContent = '';
        let chunkCount = 0;
        
        // FIX: Use the correct URL
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                sections: sectionsData,
                provider: 'deepseek'
            }),
            signal: aiStreamController.signal
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            
            function readStream() {
                reader.read().then(({ done, value }) => {
                    if (done) {
                        // Complete
                        document.getElementById('ai-status').textContent = 'AI Generation Complete!';
                        document.getElementById('ai-progress-bar').style.width = '100%';
                        document.getElementById('ai-progress-percent').textContent = '100%';
                        document.getElementById('save-ai-draft').disabled = false;
                        aiStreamController = null;
                        resolve(fullContent);
                        return;
                    }
                    
                    const chunk = decoder.decode(value, { stream: true });
                    const lines = chunk.split('\n');
                    
                    lines.forEach(line => {
                        if (line.startsWith('data: ')) {
                            const data = line.substring(6);
                            
                            try {
                                const parsed = JSON.parse(data);
                                
                                if (parsed.chunk) {
                                    chunkCount++;
                                    fullContent += parsed.chunk;
                                    
                                    // Update preview
                                    const preview = document.getElementById('journal-preview');
                                    if (chunkCount === 1) {
                                        preview.innerHTML = '';
                                    }
                                    preview.innerHTML += parsed.chunk;
                                    preview.scrollTop = preview.scrollHeight;
                                    
                                    // Update progress
                                    const progress = Math.min((chunkCount / 100) * 100, 99);
                                    document.getElementById('ai-progress-bar').style.width = `${progress}%`;
                                    document.getElementById('ai-progress-percent').textContent = `${Math.round(progress)}%`;
                                }
                                
                                if (parsed.message) {
                                    document.getElementById('ai-status').textContent = parsed.message;
                                }
                                
                            } catch (e) {
                                console.error('Failed to parse SSE data:', e);
                            }
                        }
                    });
                    
                    readStream();
                }).catch(reject);
            }
            
            readStream();
        })
        .catch(reject);
    });
},
                
                showAIModal() {
                    const modal = document.getElementById('ai-modal');
                    modal.classList.remove('hidden');
                    
                    // Reset state
                    document.getElementById('journal-preview').innerHTML = `
                        <div class="text-center py-8 text-gray-400">
                            <i class="fas fa-robot text-3xl mb-3 animate-pulse"></i>
                            <p>AI is generating your journal content...</p>
                        </div>
                    `;
                    document.getElementById('ai-progress-bar').style.width = '0%';
                    document.getElementById('ai-progress-percent').textContent = '0%';
                    document.getElementById('save-ai-draft').disabled = true;
                    document.getElementById('ai-status').textContent = 'DeepSeek AI is writing your journal...';
                },
                
                closeAIModal() {
                    const modal = document.getElementById('ai-modal');
                    modal.classList.add('hidden');
                    
                    // Abort any ongoing stream
                    if (aiStreamController) {
                        aiStreamController.abort();
                        aiStreamController = null;
                    }
                },
                
                async saveAIDraft() {
                    const previewContent = document.getElementById('journal-preview').innerText;
                    
                    if (!this.journalId || !previewContent.trim()) {
                        this.showNotification('No AI content to save.', 'error');
                        return;
                    }
                    
                    const button = document.getElementById('save-ai-draft');
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
                    
                    try {
                        const response = await fetch('/api/journal/save-ai-draft', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken()
                            },
                            body: JSON.stringify({
                                journal_id: this.journalId,
                                ai_content: previewContent
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.showNotification('AI draft saved successfully!', 'success');
                            this.closeAIModal();
                            
                            // Redirect to preview after delay
                            setTimeout(() => {
                                window.location.href = `/journal/${data.journal.id}/preview`;
                            }, 1000);
                            
                        } else {
                            throw new Error(data.message || 'Failed to save AI draft');
                        }
                    } catch (error) {
                        console.error('Save AI Draft Error:', error);
                        this.showNotification('Failed to save AI draft: ' + error.message, 'error');
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-save mr-2"></i>Save AI Draft';
                    }
                },
                
                // ====================
                // UTILITIES
                // ====================
                
                showNotification(message, type = 'info') {
                    // Create notification element
                    const notification = document.createElement('div');
                    notification.className = `fixed top-20 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-0 ${
                        type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' :
                        type === 'error' ? 'bg-red-100 text-red-800 border border-red-200' :
                        'bg-blue-100 text-blue-800 border border-blue-200'
                    }`;
                    
                    notification.innerHTML = `
                        <div class="flex items-center">
                            <i class="fas ${
                                type === 'success' ? 'fa-check-circle' :
                                type === 'error' ? 'fa-exclamation-circle' :
                                'fa-info-circle'
                            } mr-2"></i>
                            <span>${message}</span>
                        </div>
                    `;
                    
                    document.body.appendChild(notification);
                    
                    // Remove after 3 seconds
                    setTimeout(() => {
                        notification.style.transform = 'translateX(100%)';
                        setTimeout(() => {
                            if (notification.parentNode) {
                                notification.parentNode.removeChild(notification);
                            }
                        }, 300);
                    }, 3000);
                }
            }));
        });
        
        
        // Global functions for modals
        function applyEnhancedContent() {
            const enhancedContent = document.getElementById('enhanced-content').innerText;
            const editor = document.querySelector('[x-data]').__x.$data;
            
            if (enhancedContent.trim()) {
                editor.sections[editor.activeSection].content = enhancedContent;
                editor.debounceSave();
                editor.showNotification('Enhanced content applied successfully!', 'success');
            }
            
            document.getElementById('enhance-modal').classList.add('hidden');
        }
    </script>
</body>
</html>