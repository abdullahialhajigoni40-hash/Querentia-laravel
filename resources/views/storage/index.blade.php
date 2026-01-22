@extends('layouts.network')

@section('title', 'Storage Management - Querentia')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Storage Management</h1>
        <p class="text-gray-600">Manage your uploaded files and storage usage</p>
    </div>

    <!-- Storage Usage Card -->
    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Storage Usage</h2>
                <p class="text-sm text-gray-500">Based on your current subscription</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-gray-900" x-text="usage.total_size_mb + ' MB'"></p>
                <p class="text-sm text-gray-500">of <span x-text="usage.limit_mb"></span> MB used</p>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="mb-4">
            <div class="flex justify-between text-sm mb-2">
                <span class="font-medium">Usage</span>
                <span x-text="usage.usage_percentage + '%'"></span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-gradient-to-r from-green-500 to-yellow-500 h-3 rounded-full transition-all duration-500"
                     :style="'width: ' + Math.min(usage.usage_percentage, 100) + '%'"></div>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 text-center">
            <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">Files</p>
                <p class="text-lg font-bold text-gray-900" x-text="usage.file_count"></p>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">Used</p>
                <p class="text-lg font-bold text-gray-900" x-text="usage.total_size_mb + ' MB'"></p>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">Available</p>
                <p class="text-lg font-bold text-gray-900" x-text="(usage.limit_mb - usage.total_size_mb).toFixed(1) + ' MB'"></p>
            </div>
        </div>

        <!-- Warning if near limit -->
        <div x-show="usage.usage_percentage > 80" 
             class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                <p class="text-sm text-yellow-800">
                    Your storage is almost full. Consider upgrading your plan or deleting old files.
                </p>
            </div>
        </div>
    </div>

    <!-- Files List -->
    <div class="bg-white rounded-xl shadow">
        <div class="p-6 border-b">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900">Your Files</h2>
                <div class="flex space-x-3">
                    <select x-model="filterType" class="border rounded-lg px-3 py-2 text-sm">
                        <option value="all">All Files</option>
                        <option value="annexes">Annexes Only</option>
                        <option value="figures">Figures Only</option>
                    </select>
                    <input type="text" 
                           x-model="searchQuery"
                           placeholder="Search files..."
                           class="border rounded-lg px-3 py-2 text-sm w-64">
                </div>
            </div>
        </div>

        <div class="p-6">
            <div x-show="loading" class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
                <p class="text-gray-500 mt-2">Loading files...</p>
            </div>

            <div x-show="!loading && filteredFiles.length === 0" class="text-center py-8">
                <i class="fas fa-folder-open text-3xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">No files found</p>
            </div>

            <div x-show="!loading && filteredFiles.length > 0" class="space-y-3">
                <template x-for="file in filteredFiles" :key="file.path">
                    <div class="border rounded-lg p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div :class="file.type === 'annex' ? 'bg-blue-100' : 'bg-green-100'"
                                     class="w-10 h-10 rounded-lg flex items-center justify-center">
                                    <i :class="file.type === 'annex' ? 'fas fa-file-pdf text-blue-600' : 'fas fa-image text-green-600'"></i>
                                </div>
                                <div>
                                    <p class="font-medium" x-text="file.name"></p>
                                    <div class="flex items-center space-x-3 text-sm text-gray-500">
                                        <span x-text="file.type.toUpperCase()"></span>
                                        <span>•</span>
                                        <span x-text="formatFileSize(file.size)"></span>
                                        <span>•</span>
                                        <span x-text="file.journal_id ? 'Journal #' + file.journal_id : 'Unknown Journal'"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex space-x-3">
                                <a :href="file.url" target="_blank"
                                   class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button @click="downloadFile(file)"
                                        class="text-green-600 hover:text-green-800">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button @click="deleteFile(file)"
                                        class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
    function storageManager() {
        return {
            loading: true,
            usage: {
                total_size_mb: 0,
                file_count: 0,
                limit_mb: 100,
                usage_percentage: 0
            },
            files: [],
            filterType: 'all',
            searchQuery: '',
            
            get filteredFiles() {
                return this.files.filter(file => {
                    // Filter by type
                    if (this.filterType !== 'all' && file.type !== this.filterType) {
                        return false;
                    }
                    
                    // Filter by search query
                    if (this.searchQuery) {
                        const query = this.searchQuery.toLowerCase();
                        return file.name.toLowerCase().includes(query) ||
                               file.type.toLowerCase().includes(query);
                    }
                    
                    return true;
                });
            },
            
            formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            },
            
            loadUsage() {
                fetch('/api/upload/disk-usage')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.usage = data.usage;
                        }
                    });
            },
            
            loadFiles() {
                this.loading = true;
                fetch('/api/upload/list')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.files = data.files.map(file => ({
                                ...file,
                                name: this.extractFileName(file.path)
                            }));
                        }
                        this.loading = false;
                    });
            },
            
            extractFileName(path) {
                const parts = path.split('/');
                return parts[parts.length - 1];
            },
            
            downloadFile(file) {
                window.open(file.url, '_blank');
            },
            
            deleteFile(file) {
                if (!confirm('Are you sure you want to delete this file?')) {
                    return;
                }
                
                fetch('/api/upload/delete', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        path: file.path,
                        disk: file.type === 'annex' ? 'annexes' : 'figures'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.files = this.files.filter(f => f.path !== file.path);
                        this.loadUsage(); // Refresh usage stats
                        alert('File deleted successfully');
                    } else {
                        alert('Delete failed: ' + data.message);
                    }
                });
            },
            
            init() {
                this.loadUsage();
                this.loadFiles();
            }
        }
    }
</script>
@endsection