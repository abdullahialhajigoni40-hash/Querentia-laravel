<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Journal Editor - Querentia AI') - {{ time() }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        /* Font Awesome fallback icons */
        .fa-robot:before { content: "🤖"; }
        .fa-spinner:before { content: "⏳"; }
        .fa-check:before { content: "✓"; }
        .fa-times:before { content: "✕"; }
        .fa-save:before { content: "💾"; }
        .fa-eye:before { content: "👁"; }
        .fa-download:before { content: "⬇"; }
    </style>
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
                            <h1 class="font-bold text-gray-900">AI Journal</h1>
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
                            <div x-show="isSaving" style="display: none;">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                            </div>
                            <div x-show="!isSaving" style="display: none;">
                                <i class="fas fa-save mr-2"></i>
                            </div>
                            <span x-text="isSaving ? 'Saving...' : 'Save'"></span>
                        </button>
                        
                        <div x-show="canGenerateAI" style="display: none;">
                            <button @click="generateAIDraft()"
                                    :disabled="isGeneratingAI || !canGenerateAI"
                                    :class="(isGeneratingAI || !canGenerateAI) ? 'opacity-75 cursor-not-allowed' : 'hover:opacity-90'"
                                    class="px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg text-sm font-medium flex items-center">
                                <i class="fas fa-robot mr-2"></i>
                                <span>AI Generate</span>
                            </button>
                        </div>
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
                                <div x-show="[0,2,3,6,7,8].includes(activeSection)" style="display: none;">
                                    <span class="text-xs text-red-500 font-medium">Required</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Editor Content -->
            <div class="space-y-6">
                <!-- Research Topic Section (0) -->
                <div x-show="activeSection === 0" style="display: none;">
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
                </div>

                <!-- Authors Section (1) -->
                <div x-show="activeSection === 1" style="display: none;">
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
                                <div class="border border-gray-200 rounded-lg p-4 bg-white hover:bg-gray-50 transition-colors">
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
                </div>

                <!-- Text Sections (2-9) -->
                <div x-show="activeSection >= 2 && activeSection <= 9" style="display: none;">
                    <div class="space-y-4">
                        <!-- Word Count Recommendations -->
                        <div x-show="[2,3,4,5,6,7,8].includes(activeSection)" style="display: none;">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-2 sm:space-y-0 p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-600">
                                    <span class="font-medium">Recommended: </span>
                                    <span x-show="activeSection === 2" style="display: none;">150-250 words</span>
                                    <span x-show="activeSection === 3" style="display: none;">300-500 words</span>
                                    <span x-show="activeSection === 4" style="display: none;">50-100 words</span>
                                    <span x-show="activeSection === 5" style="display: none;">300-600 words</span>
                                    <span x-show="activeSection === 6" style="display: none;">200-400 words</span>
                                    <span x-show="activeSection === 7" style="display: none;">400-600 words</span>
                                    <span x-show="activeSection === 8" style="display: none;">150-250 words</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="text-xs font-medium" 
                                         :class="getWordCountClass(activeSection, sections[activeSection].content)">
                                        <span x-text="getWordCount(sections[activeSection].content)"></span> words
                                    </div>
                                    <div x-show="getWordCountClass(activeSection, sections[activeSection].content) === 'text-green-600'" style="display: none;">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i> Good
                                        </span>
                                    </div>
                                    <div x-show="getWordCountClass(activeSection, sections[activeSection].content) === 'text-yellow-600'" style="display: none;">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-exclamation mr-1"></i> Add more
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Textarea -->
                        <textarea x-model="sections[activeSection].content"
                                  @input="debounceSave()"
                                  :rows="getTextareaRows(activeSection)"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-y font-sans"
                                  :placeholder="sections[activeSection].placeholder"></textarea>

                        <!-- Maps & Figures (under Area of Study) -->
                        <div x-show="activeSection === 4" style="display: none;">
                            <div class="border-t border-gray-200 pt-6 mt-2">
                                <div class="text-sm font-semibold text-gray-800 tracking-wider">MAPS &amp; FIGURES</div>

                                <div class="mt-3 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                    <div class="text-xs text-gray-500">JPG, PNG, PDF only</div>
                                    <div class="flex items-center gap-2">
                                        <input id="figure-upload-input" type="file" class="hidden" multiple accept=".jpg,.jpeg,.png,.pdf" @change="handleFigureFileInput($event)">
                                        <button type="button"
                                                @click="openFigureFilePicker()"
                                                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                                            Upload Map/Figure
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="text-sm font-medium text-gray-800">Uploaded Files:</div>
                                    <div class="mt-2 space-y-2">
                                        <template x-for="(fig, fIndex) in (sections[4].figures || [])" :key="'fig-' + fig.id">
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border border-gray-200 rounded-lg p-3 bg-white">
                                                <div class="flex items-center gap-3">
                                                    <div class="text-sm font-semibold text-gray-900" x-text="'Figure ' + (fIndex + 1) + ':'"></div>
                                                    <a :href="fig.url" target="_blank" class="text-sm text-blue-700 hover:underline" x-text="fig.original_name || ('Figure ' + (fIndex + 1))"></a>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <button type="button"
                                                            @click="selectFigureForCaption(fig.id)"
                                                            class="px-3 py-1.5 text-sm bg-gray-50 hover:bg-gray-100 rounded-lg">
                                                        Edit Caption
                                                    </button>
                                                    <button type="button"
                                                            @click="deleteFigure(fig.id)"
                                                            class="px-3 py-1.5 text-sm text-red-700 bg-red-50 hover:bg-red-100 rounded-lg">
                                                        Delete
                                                    </button>
                                                    <div class="flex items-center gap-1">
                                                        <button type="button"
                                                                @click="moveFigureUp(fIndex)"
                                                                :disabled="fIndex === 0"
                                                                :class="fIndex === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                                                                class="px-2 py-1.5 text-sm bg-gray-50 rounded-lg">
                                                            ↑
                                                        </button>
                                                        <button type="button"
                                                                @click="moveFigureDown(fIndex)"
                                                                :disabled="fIndex === (sections[4].figures || []).length - 1"
                                                                :class="fIndex === (sections[4].figures || []).length - 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                                                                class="px-2 py-1.5 text-sm bg-gray-50 rounded-lg">
                                                            ↓
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <div x-show="!(sections[4].figures && sections[4].figures.length)" class="text-sm text-gray-500 py-2">
                                            No maps/figures uploaded yet.
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="text-sm font-medium text-gray-800">Caption for selected:</div>
                                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div class="sm:col-span-2">
                                            <input type="text"
                                                   x-model="figureCaptionDraft"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                                   placeholder="Write a short caption..."
                                                   :disabled="!selectedFigureId">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                    @click="saveSelectedFigureCaption()"
                                                    :disabled="!selectedFigureId"
                                                    :class="!selectedFigureId ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-700'"
                                                    class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium">
                                                Save Caption
                                            </button>
                                            <button type="button"
                                                    @click="clearSelectedFigure()"
                                                    :disabled="!selectedFigureId"
                                                    :class="!selectedFigureId ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                                                    class="px-4 py-2 bg-gray-50 rounded-lg text-sm font-medium">
                                                Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Methodology Sub-sections (repeatable blocks) -->
                        <div x-show="sections[activeSection].key === 'methodology'" style="display: none;">
                            <div class="mt-4 border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <div class="flex justify-between items-center">
                                    <div class="text-sm font-medium text-gray-800">Additional Methodology Notes</div>
                                    <button type="button"
                                            @click="addMethodologyBlock()"
                                            class="px-3 py-1.5 text-sm bg-purple-50 text-purple-600 hover:bg-purple-100 rounded-lg transition-colors flex items-center">
                                        <i class="fas fa-plus mr-1"></i>Add
                                    </button>
                                </div>

                                <div class="mt-4 space-y-4">
                                    <template x-for="(block, bIndex) in (sections[activeSection].blocks || [])" :key="'method-block-' + bIndex">
                                        <div class="border border-gray-200 rounded-lg p-3 bg-white">
                                            <div class="flex justify-between items-center mb-2">
                                                <input type="text"
                                                       x-model="sections[activeSection].blocks[bIndex].title"
                                                       @input="debounceSave()"
                                                       class="w-full mr-3 px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-purple-500 focus:border-transparent"
                                                       placeholder="e.g., Data Sampling / Data Analysis">
                                                <button type="button"
                                                        @click="removeMethodologyBlock(bIndex)"
                                                        class="px-2 py-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition-colors flex items-center">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <textarea x-model="sections[activeSection].blocks[bIndex].content"
                                                      @input="debounceSave()"
                                                      rows="4"
                                                      class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-purple-500 focus:border-transparent resize-y"
                                                      placeholder="Write additional methodology details..."></textarea>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Simple Word Count for Other Sections -->
                        <div x-show="![2,3,6,7,8].includes(activeSection)" style="display: none;">
                            <div class="flex justify-end">
                                <div class="text-xs text-gray-500">
                                    <span x-text="getWordCount(sections[activeSection].content)"></span> words
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Annexes Section (10) -->
                <div x-show="activeSection === 10" style="display: none;">
                    <div class="space-y-6">
                        <p class="text-gray-600">File upload functionality coming soon...</p>
                    </div>
                </div>

                <!-- Maps & Figures Section (11) -->
                <div x-show="activeSection === 11" style="display: none;">
                    <div class="space-y-6">
                        <p class="text-gray-600">Image upload functionality coming soon...</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="mt-8 flex justify-between">
                <button @click="previousSection()"
                        :disabled="activeSection === 0"
                        :class="activeSection === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'"
                        class="px-6 py-3 border border-gray-300 rounded-lg font-medium flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Previous
                </button>
                
                <div x-show="activeSection === {{ count($sections) - 1 }}" style="display: none;">
                    <button @click="generateAIDraft()"
                            :disabled="!canGenerateAI || isGeneratingAI"
                            :class="(!canGenerateAI || isGeneratingAI) ? 'opacity-75 cursor-not-allowed' : 'hover:opacity-90'"
                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg font-medium flex items-center transition-all">
                        <div x-show="isGeneratingAI" style="display: none;">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            <span>Generating...</span>
                        </div>
                        <div x-show="!isGeneratingAI" style="display: none;">
                            <i class="fas fa-robot mr-2"></i>
                            <span>Generate AI Draft</span>
                        </div>
                    </button>
                </div>
                
                <div x-show="activeSection !== {{ count($sections) - 1 }}" style="display: none;">
                    <button @click="nextSection()"
                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg font-medium flex items-center transition-all">
                        Next <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Generation Modal -->
    <div id="ai-modal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-blue-500 text-white p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold">AI Journal Generation</h3>
                        <p id="ai-status" class="text-purple-200">Querentia AI is writing your journal...</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="text-sm bg-white/20 px-3 py-1 rounded-full">Live Preview</span>
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
                    <h4 class="font-medium text-gray-900 mb-3">Live Preview</h4>
                    <div id="journal-preview" class="font-mono text-sm bg-white p-4 rounded border whitespace-pre-wrap min-h-[40vh]">
                        <!-- AI content will appear here -->
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button @click="closeAIModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100">
                        Cancel
                    </button>
                    <button id="post-for-review" 
                            disabled
                            onclick="goToPreviewOrEdit()"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-eye mr-2"></i>Click to preview or edit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // @ts-nocheck
        // Minimal bootstrap: expose server-side data as window globals, then load the editor script inline.
        (function() {
        window.initialSections = @json($sections, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
        window.initialJournalId = {{ $journal->id ?? 'null' }};
        window.journalTitle = "{{ $journal->title ?? 'New Journal' }}";
        window.csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '{{ csrf_token() }}';
        window.currentUser = {
            full_name: '{{ auth()->user()->full_name ?? "Your Name" }}',
            institution: '{{ auth()->user()->institution ?? "Your Institution" }}',
            email: '{{ auth()->user()->email ?? "" }}'
        };
        
        // Debug logging
        console.log('=== JOURNAL DEBUG ===');
        console.log('Loading Journal ID:', window.initialJournalId);
        console.log('Journal Title:', window.journalTitle);
        @if(isset($existing_data))
            console.log('Existing Data:', @json($existing_data, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT));
            window.__existingData = @json($existing_data, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
        @else
            console.log('No existing data - fresh journal');
            window.__existingData = {};
        @endif
        console.log('==================');

        // Inline journal editor script - Register component immediately
        (() => {
            // Use globals provided by the Blade template
            const initialSections = window.initialSections || [];
            const initialJournalId = typeof window.initialJournalId !== 'undefined' ? window.initialJournalId : null;

            function getCsrfToken() {
                return window.csrfToken || '';
            }

            // Global variables for AI streaming
            let aiStreamController = null;
            let enhanceStreamController = null;

            // Register Alpine component after Alpine loads
            document.addEventListener('alpine:init', () => {
                // Register the Alpine component
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

                // Computed properties
                get completedSections() {
                    return this.sections.filter((section, index) => this.isSectionComplete(index)).length;
                },
                get totalWordCount() {
                    return this.sections.reduce((count, section, index) => {
                        if (index === 1 && section.authors) {
                            return count + section.authors.reduce((sum, author) => sum + (author.name + ' ' + author.affiliation).split(/\s+/).length, 0);
                        }
                        // Include methodology blocks in word count
                        if (section.key === 'methodology' && Array.isArray(section.blocks)) {
                            const blocksWords = section.blocks.reduce((sum, block) => {
                                return sum + (block.content || '').split(/\s+/).filter(w => w).length;
                            }, 0);
                            return count + blocksWords;
                        }
                        return count + (section.content || '').split(/\s+/).filter(w => w).length;
                    }, 0);
                },
                get humanWordCount() {
                    return this.totalWordCount - this.aiWordCount;
                },
                get aiWordCount() {
                    return this.sections.reduce((count, section, index) => {
                        if (index >= 2 && index <= 9) {
                            return count + (section.content || '').split(/\s+/).filter(w => w).length * 0.7;
                        }
                        return count;
                    }, 0);
                },
                get aiPercentage() {
                    return this.totalWordCount > 0 ? Math.round((this.aiWordCount / this.totalWordCount) * 100) : 0;
                },
                get completionPercentage() {
                    return Math.round((this.completedSections / this.sections.length) * 100);
                },
                get canGenerateAI() {
                    // Allow AI generation with minimal content
                    return this.sections.some(section => {
                        return section.content && section.content.trim().length > 10;
                    });
                },
                get canEnhanceSection() {
                    // Don't allow AI enhancement for Title section (index 0) as titles come from school
                    if (this.activeSection === 0) return false;
                    
                    const section = this.sections[this.activeSection];
                    return section && section.content && section.content.trim().length > 0;
                },

                // Initialize sections with proper data structure
                initSections() {
                    this.sections.forEach((section, index) => {
                        switch(index) {
                            case 1: // Authors
                                if (!section.authors || !Array.isArray(section.authors)) {
                                    section.authors = [{
                                        name: (window.currentUser && window.currentUser.full_name) || 'Your Name',
                                        affiliation: (window.currentUser && window.currentUser.institution) || 'Your Institution',
                                        email: (window.currentUser && window.currentUser.email) || '',
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

                                if (section && section.key === 'area_of_study') {
                                    if (!Array.isArray(section.figures)) {
                                        section.figures = [];
                                    }
                                }

                                if (section && section.key === 'methodology') {
                                    if (!Array.isArray(section.blocks)) {
                                        section.blocks = [];
                                    }
                                }
                        }
                    });
                },

                init() {
                    console.log('Initializing journal editor...');
                    console.log('Initial sections count:', this.sections.length);
                    console.log('Initial journal ID:', this.journalId);
                    
                    // Clean up any old shared localStorage data (security fix)
                    this.cleanupOldLocalStorage();
                    
                    this.initSections();

                    // Load from localStorage first (user-specific)
                    this.loadFromLocalStorage();

                    // Load existing data if editing (server-side data)
                    if (typeof window.__existingData !== 'undefined') {
                        console.log('Loading existing data:', window.__existingData);
                        this.loadExistingData(window.__existingData);
                    }

                    // Load figures for this journal
                    if (this.journalId) {
                        this.loadFigures();
                    }
                },

                selectedFigureId: null,
                figureCaptionDraft: '',

                async loadFigures() {
                    try {
                        const url = '{{ route('api.upload.figures.list') }}' + '?journal_id=' + encodeURIComponent(this.journalId);
                        const res = await fetch(url, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                            },
                        });
                        const data = await res.json().catch(() => null);
                        if (!res.ok || !data || !data.success) {
                            return;
                        }
                        if (!Array.isArray(this.sections[4].figures)) {
                            this.sections[4].figures = [];
                        }
                        this.sections[4].figures = data.figures || [];
                    } catch (e) {
                        console.error('Failed to load figures', e);
                    }
                },

                openFigureFilePicker() {
                    const el = document.getElementById('figure-upload-input');
                    if (el) el.click();
                },

                async handleFigureFileInput(evt) {
                    const input = evt && evt.target ? evt.target : null;
                    const files = input && input.files ? Array.from(input.files) : [];
                    if (!files.length) return;
                    await this.uploadFigures(files);
                    if (input) input.value = '';
                },

                async uploadFigures(files) {
                    if (!this.journalId) {
                        alert('Please save the journal first before uploading figures.');
                        return;
                    }

                    for (const file of files) {
                        try {
                            const formData = new FormData();
                            formData.append('file', file);
                            formData.append('journal_id', String(this.journalId));

                            const res = await fetch('{{ route('api.upload.figure') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': getCsrfToken(),
                                    'Accept': 'application/json',
                                },
                                body: formData,
                            });

                            const data = await res.json().catch(() => null);
                            if (!res.ok || !data || !data.success) {
                                const msg = (data && (data.message || (data.errors && JSON.stringify(data.errors)))) || 'Failed to upload figure';
                                alert(msg);
                                continue;
                            }

                            if (!Array.isArray(this.sections[4].figures)) {
                                this.sections[4].figures = [];
                            }
                            this.sections[4].figures.push(data.figure);
                        } catch (e) {
                            console.error('Failed to upload figure', e);
                            alert('Failed to upload figure');
                        }
                    }
                },

                selectFigureForCaption(id) {
                    this.selectedFigureId = id;
                    const fig = (this.sections[4].figures || []).find(f => f.id === id);
                    this.figureCaptionDraft = fig ? (fig.caption || '') : '';
                },

                clearSelectedFigure() {
                    this.selectedFigureId = null;
                    this.figureCaptionDraft = '';
                },

                async saveSelectedFigureCaption() {
                    if (!this.selectedFigureId) return;
                    try {
                        const res = await fetch('{{ route('api.upload.figure.update') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                id: this.selectedFigureId,
                                caption: this.figureCaptionDraft,
                            }),
                        });

                        const data = await res.json().catch(() => null);
                        if (!res.ok || !data || !data.success) {
                            const msg = (data && (data.message || (data.errors && JSON.stringify(data.errors)))) || 'Failed to update caption';
                            alert(msg);
                            return;
                        }

                        const idx = (this.sections[4].figures || []).findIndex(f => f.id === this.selectedFigureId);
                        if (idx !== -1) {
                            this.sections[4].figures[idx].caption = this.figureCaptionDraft;
                        }
                    } catch (e) {
                        console.error('Failed to update caption', e);
                        alert('Failed to update caption');
                    }
                },

                async deleteFigure(id) {
                    if (!confirm('Delete this figure?')) return;
                    try {
                        const res = await fetch('{{ route('api.upload.figure.delete') }}', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ id }),
                        });
                        const data = await res.json().catch(() => null);
                        if (!res.ok || !data || !data.success) {
                            const msg = (data && (data.message || (data.errors && JSON.stringify(data.errors)))) || 'Failed to delete figure';
                            alert(msg);
                            return;
                        }
                        this.sections[4].figures = (this.sections[4].figures || []).filter(f => f.id !== id);
                        if (this.selectedFigureId === id) {
                            this.clearSelectedFigure();
                        }
                    } catch (e) {
                        console.error('Failed to delete figure', e);
                        alert('Failed to delete figure');
                    }
                },

                async persistFigureOrder() {
                    try {
                        const orderedIds = (this.sections[4].figures || []).map(f => f.id);
                        if (!orderedIds.length) return;
                        const res = await fetch('{{ route('api.upload.figures.reorder') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                journal_id: this.journalId,
                                ordered_ids: orderedIds,
                            }),
                        });
                        const data = await res.json().catch(() => null);
                        if (!res.ok || !data || !data.success) {
                            return;
                        }
                    } catch (e) {
                        console.error('Failed to persist figure order', e);
                    }
                },

                async moveFigureUp(index) {
                    if (index <= 0) return;
                    const arr = this.sections[4].figures || [];
                    const tmp = arr[index - 1];
                    arr[index - 1] = arr[index];
                    arr[index] = tmp;
                    this.sections[4].figures = arr;
                    await this.persistFigureOrder();
                },

                async moveFigureDown(index) {
                    const arr = this.sections[4].figures || [];
                    if (index >= arr.length - 1) return;
                    const tmp = arr[index + 1];
                    arr[index + 1] = arr[index];
                    arr[index] = tmp;
                    this.sections[4].figures = arr;
                    await this.persistFigureOrder();
                },
                
                // Clean up old shared localStorage data for security
                cleanupOldLocalStorage() {
                    try {
                        const userId = window.currentUser?.email || 'anonymous';
                        
                        // Remove old shared keys if they exist
                        const oldKeys = ['journal_draft', 'ai_preview'];
                        oldKeys.forEach(key => {
                            if (localStorage.getItem(key)) {
                                console.warn('Removing old shared localStorage key:', key);
                                localStorage.removeItem(key);
                            }
                        });
                        
                        // Also clear any stale user-specific data older than 24 hours
                        const allKeys = Object.keys(localStorage);
                        allKeys.forEach(key => {
                            if (key.startsWith('journal_draft_') || key.startsWith('ai_preview_')) {
                                try {
                                    const data = JSON.parse(localStorage.getItem(key));
                                    if (data.timestamp) {
                                        const savedTime = new Date(data.timestamp);
                                        const now = new Date();
                                        const hoursDiff = (now - savedTime) / (1000 * 60 * 60);
                                        
                                        // Clear if older than 24 hours or belongs to different user
                                        if (hoursDiff > 24 || (data.userId && data.userId !== userId)) {
                                            console.warn('Removing stale localStorage key:', key);
                                            localStorage.removeItem(key);
                                        }
                                    }
                                } catch (e) {
                                    // Invalid JSON, remove it
                                    console.warn('Removing invalid localStorage key:', key);
                                    localStorage.removeItem(key);
                                }
                            }
                        });
                    } catch (e) {
                        console.error('Failed to cleanup old localStorage:', e);
                    }
                },
                
                // Watch for changes and auto-save
                watch: {
                    sections: {
                        handler() {
                            this.debounceSave();
                        },
                        deep: true
                    }
                },

                loadExistingData(data) {
                    Object.keys(data).forEach(key => {
                        if (key === 'methodology_blocks') {
                            const methodologyIndex = this.sections.findIndex(s => s.key === 'methodology');
                            if (methodologyIndex !== -1) {
                                this.sections[methodologyIndex].blocks = Array.isArray(data[key]) ? data[key] : [];
                            }
                            return;
                        }

                        const sectionIndex = this.sections.findIndex(s => s.key === key);
                        if (sectionIndex !== -1) {
                            if (key === 'authors' && Array.isArray(data[key])) {
                                this.sections[sectionIndex].authors = data[key];
                            } else {
                                // Ensure content is always a string
                                const content = data[key] || '';
                                this.sections[sectionIndex].content = typeof content === 'string' ? content : String(content);
                            }
                        }
                    });
                },

                addMethodologyBlock() {
                    const idx = this.sections.findIndex(s => s.key === 'methodology');
                    if (idx === -1) return;
                    if (!Array.isArray(this.sections[idx].blocks)) this.sections[idx].blocks = [];
                    this.sections[idx].blocks.push({ title: '', content: '' });
                    this.debounceSave();
                },

                removeMethodologyBlock(blockIndex) {
                    const idx = this.sections.findIndex(s => s.key === 'methodology');
                    if (idx === -1) return;
                    if (!Array.isArray(this.sections[idx].blocks)) this.sections[idx].blocks = [];
                    this.sections[idx].blocks.splice(blockIndex, 1);
                    this.debounceSave();
                },

                switchSection(index) {
                    this.activeSection = index;
                },

                isSectionComplete(index) {
                    const section = this.sections[index];
                    if (!section) return false;
                    
                    // Check content-based sections (including Area of Study and Methodology)
                    if ([0, 2, 3, 4, 5, 6, 7, 8].includes(index)) {
                        return section.content && section.content.trim().length > 0;
                    }
                    if (index === 1 && section.authors) {
                        return section.authors.length > 0 && section.authors.every(a => a.name && a.affiliation);
                    }
                    return false;
                },

                previousSection() {
                    if (this.activeSection > 0) {
                        this.activeSection--;
                    }
                },

                nextSection() {
                    if (this.activeSection < this.sections.length - 1) {
                        this.activeSection++;
                    }
                },

                addAuthor() {
                    const newAuthor = {
                        name: '',
                        affiliation: '',
                        email: '',
                        corresponding: false
                    };
                    this.sections[1].authors.push(newAuthor);
                },

                removeAuthor(index) {
                    if (this.sections[1].authors.length > 1) {
                        this.sections[1].authors.splice(index, 1);
                    }
                },

                getTextareaRows(sectionIndex) {
                    const rowMap = {
                        2: 4,
                        3: 8,
                        4: 6,
                        5: 6,
                        6: 6,
                        7: 10,
                        8: 4,
                        9: 6
                    };
                    return rowMap[sectionIndex] || 6;
                },

                debounceSave() {
                    clearTimeout(this.saveTimeout);
                    this.saveTimeout = setTimeout(() => {
                        this.saveJournal(true);
                    }, 1000);
                },

                saveJournal(silent = false) {
                    if (!silent) {
                        this.isSaving = true;
                        this.saveStatus = 'Saving...';
                    }
                    
                    // Save to localStorage first
                    this.saveToLocalStorage();
                    
                    const journalData = {
                        title: this.sections[0]?.content || 'Untitled Journal',
                        sections: this.sections.map((section, index) => {
                            if (index === 1) { // Authors section
                                return {
                                    title: section.title,
                                    key: section.key,
                                    authors: section.authors || []
                                };
                            }
                            return {
                                title: section.title,
                                key: section.key,
                                content: section.content || '',
                                blocks: section.key === 'methodology' ? (section.blocks || []) : undefined
                            };
                        })
                    };
                    
                    fetch('/api/journal/save', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify({
                            journal_id: this.journalId,
                            ...journalData
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.journalId = data.journal.id;
                            this.saveStatus = 'Saved';
                            if (!silent) {
                                console.log('Journal saved successfully');
                            }
                        } else {
                            throw new Error(data.message || 'Save failed');
                        }
                    })
                    .catch(error => {
                        this.saveStatus = 'Error';
                        console.error('Save error:', error);
                        if (!silent) {
                            alert('Failed to save journal. Please try again.');
                        }
                    })
                    .finally(() => {
                        if (!silent) {
                            this.isSaving = false;
                        }
                    });
                },
                
                saveToLocalStorage() {
                    // Make localStorage user-specific by including user ID
                    const userId = window.currentUser?.email || 'anonymous';
                    const storageKey = `journal_draft_${userId}`;
                    
                    const data = {
                        journalId: this.journalId,
                        sections: this.sections,
                        timestamp: new Date().toISOString(),
                        userId: userId
                    };
                    localStorage.setItem(storageKey, JSON.stringify(data));
                },
                
                loadFromLocalStorage() {
                    // Only load data for the current user
                    const userId = window.currentUser?.email || 'anonymous';
                    const storageKey = `journal_draft_${userId}`;
                    
                    const saved = localStorage.getItem(storageKey);
                    if (saved) {
                        try {
                            const data = JSON.parse(saved);
                            
                            // Verify the data belongs to current user
                            if (data.userId !== userId) {
                                console.warn('localStorage data belongs to different user, ignoring');
                                return;
                            }
                            
                            // Only load if less than 24 hours old
                            const savedTime = new Date(data.timestamp);
                            const now = new Date();
                            const hoursDiff = (now - savedTime) / (1000 * 60 * 60);
                            
                            if (hoursDiff < 24) {
                                this.journalId = data.journalId;
                                this.sections = data.sections;
                                console.log('Loaded draft from localStorage for user:', userId);
                            }
                        } catch (e) {
                            console.error('Failed to load from localStorage:', e);
                        }
                    }
                },
                
                getWordCount(text) {
                    if (!text || typeof text !== 'string') return 0;
                    return text.split(/\s+/).filter(word => word.length > 0).length;
                },
                
                getWordCountClass(sectionIndex, content) {
                    const wordCount = this.getWordCount(content);
                    const recommendations = {
                        2: { min: 150, max: 250 },  // Abstract
                        3: { min: 300, max: 500 },  // Introduction
                        4: { min: 50, max: 100 },   // Area of Study
                        5: { min: 300, max: 600 },  // Methodology
                        6: { min: 200, max: 400 },  // Results & Discussion
                        7: { min: 400, max: 600 },  // Conclusion
                        8: { min: 150, max: 250 }   // References
                    };
                    
                    const rec = recommendations[sectionIndex];
                    if (!rec) return 'text-gray-600';
                    
                    if (wordCount < rec.min) return 'text-red-600';
                    if (wordCount > rec.max) return 'text-yellow-600';
                    return 'text-green-600';
                },

                closeAIModal() {
                    const modal = document.getElementById('ai-modal');
                    if (modal) {
                        modal.classList.remove('flex');
                        modal.classList.add('hidden');
                        const preview = document.getElementById('journal-preview');
                        if (preview) preview.innerHTML = '';
                        const progressBar = document.getElementById('ai-progress-bar');
                        if (progressBar) progressBar.style.width = '0%';
                        const progressPercent = document.getElementById('ai-progress-percent');
                        if (progressPercent) progressPercent.textContent = '0%';
                        const postBtn = document.getElementById('post-for-review');
                        if (postBtn) postBtn.disabled = true;
                    }
                },

                generateAIDraft() {
                    if (!this.canGenerateAI) {
                        alert('Please complete required sections (Title, Abstract, Introduction, Methods, Results, Discussion) before generating AI draft.');
                        return;
                    }
                    
                    this.isGeneratingAI = true;
                    
                    // Collect all section data as array, including methodology blocks
                    const sectionsArray = [];
                    this.sections.forEach((section, index) => {
                        if (section.key === 'methodology' && Array.isArray(section.blocks)) {
                            // Flatten methodology blocks into a single content string for AI
                            const flattenedContent = section.blocks
                                .map(block => {
                                    const title = block.title ? `**${block.title}**\n` : '';
                                    return title + (block.content || '');
                                })
                                .join('\n\n');
                            sectionsArray.push({
                                title: section.title,
                                content: flattenedContent
                            });
                        } else {
                            sectionsArray.push({
                                title: section.title,
                                content: section.content || ''
                            });
                        }
                    });
                    
                    // Debug: Log what we're sending to AI
                    console.log('Sending sections to AI:', sectionsArray);
                    console.log('Total sections with content:', sectionsArray.filter(s => s.content && s.content.trim().length > 0).length);
                    
                    // Show AI modal
                    const modal = document.getElementById('ai-modal');
                    if (modal) {
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    }
                    
                    // Initialize AI streaming using existing class
                    if (window.QuerentiaAI) {
                        window.QuerentiaAI.initStreaming({
                            onStart: (data) => {
                                console.log('AI generation started:', data);
                                const status = document.getElementById('ai-status');
                                if (status) status.textContent = data.message || 'Starting AI generation...';
                            },
                            onChunk: (content) => {
                                const preview = document.getElementById('journal-preview');
                                if (preview) {
                                    preview.innerHTML += content;
                                    preview.scrollTop = preview.scrollHeight;
                                }
                                
                                // persist preview so it survives refreshes (user-specific)
                                try { 
                                    const userId = window.currentUser?.email || 'anonymous';
                                    const previewKey = `ai_preview_${userId}`;
                                    localStorage.setItem(previewKey, (preview && preview.innerText) || ''); 
                                } catch (e) { console.error('Failed to persist ai_preview', e); }

                                // Update progress (rough estimate)
                                const currentContent = preview.innerText;
                                const progress = Math.min((currentContent.length / 5000) * 100, 99);
                                const progressBar = document.getElementById('ai-progress-bar');
                                const progressPercent = document.getElementById('ai-progress-percent');
                                if (progressBar) progressBar.style.width = `${progress}%`;
                                if (progressPercent) progressPercent.textContent = `${Math.round(progress)}%`;
                            },
                            onComplete: (data) => {
                                console.log('AI generation complete:', data);
                                this.isGeneratingAI = false;
                                const status = document.getElementById('ai-status');
                                if (status) status.textContent = 'Generation complete!';
                                
                                const progressBar = document.getElementById('ai-progress-bar');
                                const progressPercent = document.getElementById('ai-progress-percent');
                                if (progressBar) progressBar.style.width = '100%';
                                if (progressPercent) progressPercent.textContent = '100%';
                                
                                const postBtn = document.getElementById('post-for-review');
                                if (postBtn) postBtn.disabled = false;

                                // persist preview on completion (user-specific)
                                const previewContent = document.getElementById('journal-preview')?.innerText || '';
                                try { 
                                    const userId = window.currentUser?.email || 'anonymous';
                                    const previewKey = `ai_preview_${userId}`;
                                    localStorage.setItem(previewKey, previewContent); 
                                } catch (e) { console.error('Failed to persist ai_preview', e); }

                                // Store journal ID for posting
                                if (data.journal_id) {
                                    this.journalId = data.journal_id;
                                }

                                // Trigger an automatic save of the AI draft (best-effort)
                                try {
                                    if (window.autosaveAIDraft) {
                                        window.autosaveAIDraft(data, previewContent);
                                    }
                                } catch (e) { console.error('autosaveAIDraft error', e); }
                            },
                            onError: (message) => {
                                try {
                                    console.error('AI generation error:', message);
                                    this.isGeneratingAI = false;
                                    // Show a user-friendly alert if possible
                                    if (typeof message === 'string' && message.length > 0) {
                                        alert('AI generation failed: ' + message);
                                    } else {
                                        alert('AI generation failed. Please check logs.');
                                    }
                                } catch (err) {
                                    console.error('Error in onError handler:', err);
                                } finally {
                                    // Ensure modal is closed to avoid stuck UI
                                    try { this.closeAIModal(); } catch (e) { /* ignore */ }
                                }
                            }
                        });
                        
                        // Start generation
                        window.QuerentiaAI.generate(this.journalId, sectionsArray, 'deepseek');
                    } else {
                        console.error('QuerentiaAI not available');
                        this.isGeneratingAI = false;
                        alert('AI system not loaded. Please refresh the page.');
                    }
                },
                }));
            });
        })();
        })();
        
        // Autosave helper: attempt to save AI-generated preview as a draft
        window.autosaveAIDraft = async function(data, previewContent) {
            try {
                let journalId = data && data.journal_id ? data.journal_id : (window.initialJournalId || null);
                if (!journalId) {
                    // Try to create a journal from initialSections if possible
                    const sectionsPayload = {};
                    const src = window.initialSections || [];
                    src.forEach((s, idx) => {
                        if (idx === 1) sectionsPayload[idx] = s.authors || [];
                        else sectionsPayload[idx] = (s && s.content) || s || '';
                    });
                    const title = (src[0] && (src[0].content || src[0].title)) || 'Untitled Research';
                    const saveResp = await fetch('/api/journal/save', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ title: title, sections: sectionsPayload })
                    });

                    // Accept both JSON and non-JSON responses for diagnostics
                    let saveData = null;
                    const ct = saveResp.headers.get('content-type') || '';
                    if (ct.includes('application/json')) {
                        saveData = await saveResp.json().catch(() => null);
                    } else {
                        const text = await saveResp.text().catch(() => null);
                        console.warn('autosaveAIDraft: non-json response when creating journal', saveResp.status, text);
                    }

                    if (saveData && saveData.success && saveData.journal) {
                        data = data || {};
                        data.journal_id = saveData.journal.id;
                        journalId = saveData.journal.id;
                    } else {
                        console.warn('autosaveAIDraft: unable to create journal automatically', saveResp, saveData);
                    }
                }

                const finalJournalId = journalId;
                if (!finalJournalId) return; // nothing to do

                // Post AI draft
                const resp = await fetch('/api/journal/save-ai-draft', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ journal_id: finalJournalId, ai_content: previewContent })
                });

                const contentType = resp.headers.get('content-type') || '';
                let result = null;
                if (contentType.includes('application/json')) {
                    result = await resp.json().catch(() => null);
                } else {
                    const text = await resp.text().catch(() => null);
                    console.warn('autosaveAIDraft: non-json response from save-ai-draft', resp.status, text);
                }

                if (resp.ok && result && result.success) {
                    console.log('autosaveAIDraft: saved AI draft for journal', finalJournalId);
                } else {
                    console.warn('autosaveAIDraft: failed to save AI draft', resp, result);
                }
            } catch (e) {
                console.error('autosaveAIDraft error', e);
            }
        };

        // Global function for posting AI draft for review
        function postForReview() {
            let previewContent = document.getElementById('journal-preview').innerText;
            const journalId = window.Alpine?.store?.('journalEditor')?.journalId || null;
            
            if (!previewContent.trim()) {
                alert('No AI content to post');
                return;
            }

            // If there is persisted preview from localStorage but previewContent is empty, restore it (user-specific)
            try {
                if (!previewContent.trim()) {
                    const userId = window.currentUser?.email || 'anonymous';
                    const previewKey = `ai_preview_${userId}`;
                    const stored = localStorage.getItem(previewKey);
                    if (stored) previewContent = stored;
                }
            } catch (e) { /* ignore */ }
            
            // First ensure we have a journal id; create journal if needed, then save AI draft and redirect
                (async function() {
                    try {
                        let targetJournalId = journalId || window.initialJournalId || null;

                        if (!targetJournalId) {
                            // Attempt to create the journal from existing sections
                            const sectionsPayload = {};
                            const src = window.initialSections || [];
                            src.forEach((s, idx) => {
                                if (idx === 1) sectionsPayload[idx] = s.authors || [];
                                else sectionsPayload[idx] = (s && s.content) || s || '';
                            });
                            const title = (src[0] && (src[0].content || src[0].title)) || 'Untitled Research';
                            const saveResp = await fetch('/api/journal/save', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({ title: title, sections: sectionsPayload })
                            });

                            const ct = saveResp.headers.get('content-type') || '';
                            let saveData = null;
                            if (ct.includes('application/json')) {
                                saveData = await saveResp.json().catch(() => null);
                            } else {
                                const text = await saveResp.text().catch(() => null);
                                console.warn('postForReview: non-json response when creating journal', saveResp.status, text);
                            }

                            if (saveData && saveData.success && saveData.journal) {
                                targetJournalId = saveData.journal.id;
                            } else {
                                alert('Failed to create journal before posting for review.');
                                return;
                            }
                        }

                        // Save AI draft
                        const resp = await fetch('/api/journal/save-ai-draft', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ journal_id: targetJournalId, ai_content: previewContent })
                        });

                        const contentType = resp.headers.get('content-type') || '';
                        let data = null;
                        if (contentType.includes('application/json')) {
                            data = await resp.json().catch(() => null);
                        } else {
                            const text = await resp.text().catch(() => null);
                            console.error('postForReview: non-json response from save-ai-draft', resp.status, text);
                            alert('Failed to post for review: server returned an unexpected response. Check console/logs.');
                            return;
                        }

                        if (resp.ok && data && data.success) {
                            // Close modal if present
                            const modal = document.getElementById('ai-modal');
                            if (modal) {
                                modal.classList.remove('flex');
                                modal.classList.add('hidden');
                            }
                            // Redirect to server-provided URL if available, otherwise fallback to journal preview
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else if (data.journal && data.journal.id) {
                                window.location.href = `/journal/${data.journal.id}/preview`;
                            } else {
                                window.location.href = '/network';
                            }
                        } else {
                            const msg = (data && (data.message || (data.errors && JSON.stringify(data.errors)))) || 'Unknown error';
                            alert('Failed to prepare journal for review: ' + msg);
                        }
                    } catch (error) {
                        console.error('Post error:', error);
                        alert('Failed to post for review: ' + (error && error.message ? error.message : 'Unknown error'));
                    }
                })();
        }

        // Global function for previewing/editing after AI generation
        function goToPreviewOrEdit() {
            let previewContent = document.getElementById('journal-preview').innerText;
            const journalId = window.Alpine?.store?.('journalEditor')?.journalId || null;

            if (!previewContent.trim()) {
                alert('No AI content to preview');
                return;
            }

            // If there is persisted preview from localStorage but previewContent is empty, restore it (user-specific)
            try {
                if (!previewContent.trim()) {
                    const userId = window.currentUser?.email || 'anonymous';
                    const previewKey = `ai_preview_${userId}`;
                    const stored = localStorage.getItem(previewKey);
                    if (stored) previewContent = stored;
                }
            } catch (e) { /* ignore */ }

            (async function() {
                try {
                    let targetJournalId = journalId || window.initialJournalId || null;

                    if (!targetJournalId) {
                        const sectionsPayload = {};
                        const src = window.initialSections || [];
                        src.forEach((s, idx) => {
                            if (idx === 1) sectionsPayload[idx] = s.authors || [];
                            else sectionsPayload[idx] = (s && s.content) || s || '';
                        });
                        const title = (src[0] && (src[0].content || src[0].title)) || 'Untitled Research';

                        const saveResp = await fetch('/api/journal/save', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ title: title, sections: sectionsPayload })
                        });

                        const ct = saveResp.headers.get('content-type') || '';
                        let saveData = null;
                        if (ct.includes('application/json')) {
                            saveData = await saveResp.json().catch(() => null);
                        } else {
                            const text = await saveResp.text().catch(() => null);
                            console.warn('goToPreviewOrEdit: non-json response when creating journal', saveResp.status, text);
                        }

                        if (saveData && saveData.success && saveData.journal) {
                            targetJournalId = saveData.journal.id;
                        } else {
                            alert('Failed to create journal before previewing.');
                            return;
                        }
                    }

                    // Save AI draft (so preview page has content)
                    const resp = await fetch('/api/journal/save-ai-draft', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ journal_id: targetJournalId, ai_content: previewContent })
                    });

                    const contentType = resp.headers.get('content-type') || '';
                    let data = null;
                    if (contentType.includes('application/json')) {
                        data = await resp.json().catch(() => null);
                    } else {
                        const text = await resp.text().catch(() => null);
                        console.error('goToPreviewOrEdit: non-json response from save-ai-draft', resp.status, text);
                        alert('Failed to save AI draft: server returned an unexpected response. Check console/logs.');
                        return;
                    }

                    if (resp.ok && data && data.success) {
                        const modal = document.getElementById('ai-modal');
                        if (modal) {
                            modal.classList.remove('flex');
                            modal.classList.add('hidden');
                        }
                        window.location.href = `/journal/${targetJournalId}/preview`;
                    } else {
                        const msg = (data && (data.message || (data.errors && JSON.stringify(data.errors)))) || 'Unknown error';
                        alert('Failed to prepare preview: ' + msg);
                    }
                } catch (error) {
                    console.error('Preview error:', error);
                    alert('Failed to open preview: ' + (error && error.message ? error.message : 'Unknown error'));
                }
            })();
        }
    </script>
</body>
</html>
