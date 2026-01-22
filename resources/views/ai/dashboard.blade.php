@extends('layouts.network')

@section('title', 'AI Dashboard - Querentia')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">AI Assistant Dashboard</h1>
        <p class="text-gray-600">Monitor your AI usage and manage AI settings</p>
    </div>

    <!-- Current Plan Banner -->
    <div class="mb-8 p-6 bg-gradient-to-r from-purple-600 to-blue-500 rounded-2xl text-white">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="mb-4 md:mb-0">
                <h2 class="text-2xl font-bold mb-2">AI Credits Status</h2>
                <p class="opacity-90">
                    @if(auth()->user()->isPro())
                        You have unlimited AI credits with your Pro subscription
                    @elseif(auth()->user()->subscription_tier === 'basic')
                        You have 10,000 tokens available this month
                    @else
                        Upgrade to Basic or Pro plan to access AI features
                    @endif
                </p>
            </div>
            @if(!auth()->user()->isSubscribed())
            <a href="{{ route('payment.pricing') }}" 
               class="px-6 py-3 bg-white text-purple-600 font-bold rounded-lg hover:bg-gray-100 transition">
                Upgrade for AI Access
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Usage Stats -->
        <div class="lg:col-span-2">
            <!-- Usage Statistics Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">AI Usage Statistics</h2>
                
                <!-- Loading State -->
                <div x-show="loading" class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
                    <p class="text-gray-500 mt-2">Loading statistics...</p>
                </div>

                <!-- Stats Grid -->
                <div x-show="!loading" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-500">Tokens Used This Month</p>
                        <p class="text-2xl font-bold text-gray-900" x-text="stats.monthly.tokens.toLocaleString()"></p>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-blue-600 h-2 rounded-full" 
                                 x-bind:style="'width: ' + Math.min((stats.monthly.tokens / (stats.limits.monthly_tokens === 'unlimited' ? stats.monthly.tokens + 1 : stats.limits.monthly_tokens)) * 100, 100) + '%'"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            <span x-text="stats.limits.monthly_tokens === 'unlimited' ? 'Unlimited' : stats.limits.monthly_tokens.toLocaleString() + ' limit'"></span>
                            <span x-show="stats.limits.remaining_tokens !== 'unlimited'">
                                • <span x-text="stats.limits.remaining_tokens.toLocaleString()"></span> remaining
                            </span>
                        </p>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-500">AI Requests This Month</p>
                        <p class="text-2xl font-bold text-gray-900" x-text="stats.monthly.requests"></p>
                        <p class="text-xs text-gray-500 mt-1">Total Requests: <span x-text="stats.total.requests"></span></p>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-500">Estimated Cost</p>
                        <p class="text-2xl font-bold text-gray-900">$<span x-text="stats.total.estimated_cost.toFixed(2)"></span></p>
                        <p class="text-xs text-gray-500 mt-1">Covered by your subscription</p>
                    </div>
                </div>

                <!-- Provider Distribution -->
                <div x-show="!loading">
                    <h3 class="font-medium text-gray-900 mb-4">Provider Usage Distribution</h3>
                    <div class="space-y-3">
                        <template x-for="provider in stats.providers" :key="provider.provider">
                            <div class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                                         :class="{
                                            'bg-blue-100': provider.provider === 'deepseek',
                                            'bg-green-100': provider.provider === 'openai',
                                            'bg-purple-100': provider.provider === 'gemini'
                                         }">
                                        <i :class="{
                                            'fas fa-robot text-blue-600': provider.provider === 'deepseek',
                                            'fab fa-openai text-green-600': provider.provider === 'openai',
                                            'fas fa-gem text-purple-600': provider.provider === 'gemini'
                                        }"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium" x-text="provider.provider.toUpperCase()"></p>
                                        <p class="text-sm text-gray-500" x-text="provider.requests + ' requests'"></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-lg font-bold" x-text="provider.tokens.toLocaleString()"></span>
                                    <p class="text-sm text-gray-500">tokens</p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Recent AI Activity -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Recent AI Activity</h2>
                    <button @click="refreshStats()"
                            class="text-purple-600 hover:text-purple-800">
                        <i class="fas fa-redo"></i>
                    </button>
                </div>
                
                <div x-show="loading" class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-gray-400"></i>
                </div>
                
                <div x-show="!loading && recentActivity.length === 0" class="text-center py-8">
                    <i class="fas fa-robot text-3xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No AI activity yet</p>
                    <a href="{{ route('journal.create') }}" 
                       class="mt-4 inline-block text-purple-600 hover:text-purple-800">
                       Start using AI in Journal Editor →
                    </a>
                </div>
                
                <div x-show="!loading && recentActivity.length > 0" class="space-y-4">
                    <template x-for="activity in recentActivity" :key="activity.id">
                        <div class="border rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-medium" x-text="activity.task_type"></p>
                                    <p class="text-sm text-gray-500">
                                        <span x-text="activity.provider.toUpperCase()"></span>
                                        • 
                                        <span x-text="new Date(activity.created_at).toLocaleDateString()"></span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold" x-text="activity.tokens_used"></span>
                                    <span class="text-sm text-gray-500"> tokens</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-700" x-text="activity.journal_title || 'General editing'"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Right Column: AI Providers & Settings -->
        <div class="space-y-6">
            <!-- AI Providers Status -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">AI Providers Status</h2>
                
                <div class="space-y-3">
                    <template x-for="(status, name) in providers" :key="name">
                        <div class="flex items-center justify-between p-3 border rounded-lg"
                             :class="status.enabled ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                            <div class="flex items-center space-x-3">
                                <div class="relative">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                                         :class="{
                                            'bg-blue-100': name === 'deepseek',
                                            'bg-green-100': name === 'openai',
                                            'bg-purple-100': name === 'gemini'
                                         }">
                                        <i :class="{
                                            'fas fa-robot text-blue-600': name === 'deepseek',
                                            'fab fa-openai text-green-600': name === 'openai',
                                            'fas fa-gem text-purple-600': name === 'gemini'
                                        }"></i>
                                    </div>
                                    <div class="absolute -top-1 -right-1 w-3 h-3 rounded-full border-2 border-white"
                                         :class="status.enabled ? 'bg-green-500' : 'bg-red-500'"></div>
                                </div>
                                <div>
                                    <p class="font-medium" x-text="status.name"></p>
                                    <p class="text-xs text-gray-500" x-text="status.model"></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs px-2 py-1 rounded"
                                      :class="status.enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    <span x-text="status.enabled ? 'Active' : 'Inactive'"></span>
                                </span>
                                <p class="text-xs text-gray-500 mt-1" x-text="'Priority: ' + status.priority"></p>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Provider Test -->
                <div class="mt-6 pt-6 border-t">
                    <h3 class="font-medium text-gray-900 mb-3">Test Provider Connectivity</h3>
                    <div class="flex space-x-2">
                        <select x-model="testProvider" class="flex-1 border rounded-lg px-3 py-2">
                            <option value="deepseek">DeepSeek</option>
                            <option value="openai">OpenAI</option>
                            <option value="gemini">Gemini</option>
                        </select>
                        <button @click="testProviderConnectivity()"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Test
                        </button>
                    </div>
                    <div x-show="testResult" class="mt-3 p-3 rounded-lg"
                         :class="testResult.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                        <p x-text="testResult.message"></p>
                    </div>
                </div>
            </div>

            <!-- AI Settings -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">AI Settings</h2>
                
                <div class="space-y-4">
                    <!-- Default Provider -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Default AI Provider</label>
                        <select x-model="defaultProvider" @change="updateDefaultProvider()"
                                class="w-full border rounded-lg px-3 py-2">
                            <option value="deepseek">DeepSeek (Recommended)</option>
                            <option value="openai">OpenAI ChatGPT</option>
                            <option value="gemini">Google Gemini</option>
                        </select>
                    </div>
                    
                    <!-- Fallback Setting -->
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">Enable Fallback</p>
                            <p class="text-sm text-gray-500">Automatically switch providers if one fails</p>
                        </div>
                        <div class="relative inline-block w-12">
                            <input type="checkbox" x-model="fallbackEnabled" @change="updateFallbackSetting()"
                                   class="sr-only peer" id="fallback-toggle">
                            <label for="fallback-toggle" 
                                   class="block w-12 h-6 bg-gray-200 rounded-full peer-checked:bg-green-500 cursor-pointer after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-6"></label>
                        </div>
                    </div>
                    
                    <!-- AI Assist Auto-enable -->
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">Auto-enable AI Assist</p>
                            <p class="text-sm text-gray-500">Enable AI Assist automatically in editor</p>
                        </div>
                        <div class="relative inline-block w-12">
                            <input type="checkbox" x-model="autoEnableAI" @change="updateAISetting('auto_enable_ai', $event.target.checked)"
                                   class="sr-only peer" id="auto-ai-toggle">
                            <label for="auto-ai-toggle" 
                                   class="block w-12 h-6 bg-gray-200 rounded-full peer-checked:bg-purple-500 cursor-pointer after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-6"></label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
                
                <div class="space-y-3">
                    <a href="{{ route('journal.create') }}" 
                       class="flex items-center space-x-3 p-3 border rounded-lg hover:bg-gray-50">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-robot text-purple-600"></i>
                        </div>
                        <div>
                            <p class="font-medium">AI Journal Studio</p>
                            <p class="text-sm text-gray-500">Create journals with AI assistance</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('payment.pricing') }}" 
                       class="flex items-center space-x-3 p-3 border rounded-lg hover:bg-gray-50">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-crown text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-medium">Upgrade Plan</p>
                            <p class="text-sm text-gray-500">Get more AI credits and features</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('subscriptions') }}" 
                       class="flex items-center space-x-3 p-3 border rounded-lg hover:bg-gray-50">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-history text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium">Usage History</p>
                            <p class="text-sm text-gray-500">View detailed AI usage history</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function aiDashboard() {
        return {
            loading: true,
            stats: {
                total: { tokens: 0, requests: 0, estimated_cost: 0 },
                monthly: { tokens: 0, requests: 0 },
                providers: [],
                limits: { monthly_tokens: 0, remaining_tokens: 0, remaining_percentage: 0 }
            },
            providers: {},
            recentActivity: [],
            defaultProvider: '{{ config("ai.default_provider", "deepseek") }}',
            fallbackEnabled: {{ config("ai.fallback_enabled", true) ? 'true' : 'false' }},
            autoEnableAI: localStorage.getItem('auto_enable_ai') === 'true',
            testProvider: 'deepseek',
            testResult: null,
            
            async init() {
                await this.loadStats();
                await this.loadProviders();
                await this.loadRecentActivity();
                this.loading = false;
            },
            
            async loadStats() {
                try {
                    const response = await fetch('/api/ai/usage-stats');
                    const data = await response.json();
                    if (data.success) {
                        this.stats = data.stats;
                    }
                } catch (error) {
                    console.error('Failed to load stats:', error);
                }
            },
            
            async loadProviders() {
                try {
                    const response = await fetch('/api/ai/providers-status');
                    const data = await response.json();
                    if (data.success) {
                        this.providers = data.providers;
                        this.defaultProvider = data.default_provider;
                        this.fallbackEnabled = data.fallback_enabled;
                    }
                } catch (error) {
                    console.error('Failed to load providers:', error);
                }
            },
            
            async loadRecentActivity() {
                try {
                    // This would be a separate endpoint in real app
                    // For now, we'll use the existing logs
                    const response = await fetch('/api/ai/usage-stats');
                    const data = await response.json();
                    if (data.success) {
                        // Simulate recent activity from stats
                        this.recentActivity = data.stats.providers.map(provider => ({
                            id: Math.random(),
                            provider: provider.provider,
                            task_type: 'Journal Enhancement',
                            tokens_used: provider.tokens,
                            created_at: new Date().toISOString(),
                            journal_title: 'Recent Journal'
                        }));
                    }
                } catch (error) {
                    console.error('Failed to load activity:', error);
                }
            },
            
            async refreshStats() {
                this.loading = true;
                await this.loadStats();
                await this.loadRecentActivity();
                this.loading = false;
            },
            
            async updateDefaultProvider() {
                try {
                    const response = await fetch('/api/ai/update-default-provider', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ provider: this.defaultProvider })
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        alert('Default provider updated successfully');
                    }
                } catch (error) {
                    console.error('Failed to update provider:', error);
                    alert('Failed to update provider');
                }
            },
            
            async updateFallbackSetting() {
                try {
                    const response = await fetch('/api/ai/update-fallback-setting', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ enabled: this.fallbackEnabled })
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        alert('Fallback setting updated');
                    }
                } catch (error) {
                    console.error('Failed to update setting:', error);
                }
            },
            
            updateAISetting(key, value) {
                localStorage.setItem(key, value);
                alert('Setting saved');
            },
            
            async testProviderConnectivity() {
                this.testResult = null;
                
                try {
                    const response = await fetch('/api/ai/test-provider', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ provider: this.testProvider })
                    });
                    
                    const data = await response.json();
                    this.testResult = {
                        success: data.success,
                        message: data.success ? 
                            `${this.testProvider.toUpperCase()} is connected and working` :
                            `Connection failed: ${data.error || data.message}`
                    };
                } catch (error) {
                    this.testResult = {
                        success: false,
                        message: `Test failed: ${error.message}`
                    };
                }
            }
        }
    }
</script>
@endsection