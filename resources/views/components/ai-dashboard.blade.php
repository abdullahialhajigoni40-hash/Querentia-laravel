<div class="bg-white rounded-xl shadow-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-900">AI Usage Dashboard</h2>
        <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-medium">
            {{ ucfirst(auth()->user()->subscription_tier) }} Plan
        </span>
    </div>

    <!-- Usage Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-sm text-gray-500">Tokens Used This Month</p>
            <p class="text-2xl font-bold text-gray-900" x-text="stats.monthly.tokens.toLocaleString()"></p>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                <div class="bg-blue-600 h-2 rounded-full" 
                     :style="'width: ' + Math.min((stats.monthly.tokens / stats.limits.monthly_tokens) * 100, 100) + '%'"></div>
            </div>
            <p class="text-xs text-gray-500 mt-1" x-text="'Limit: ' + (stats.limits.monthly_tokens === 'unlimited' ? 'Unlimited' : stats.limits.monthly_tokens.toLocaleString())"></p>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-sm text-gray-500">AI Requests This Month</p>
            <p class="text-2xl font-bold text-gray-900" x-text="stats.monthly.requests"></p>
            <p class="text-xs text-gray-500 mt-1">Total Requests: <span x-text="stats.total.requests"></span></p>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-sm text-gray-500">Estimated Cost</p>
            <p class="text-2xl font-bold text-gray-900">$<span x-text="stats.total.estimated_cost.toFixed(2)"></span></p>
            <p class="text-xs text-gray-500 mt-1">Your subscription covers this cost</p>
        </div>
    </div>

    <!-- Provider Usage -->
    <div class="mb-6">
        <h3 class="font-medium text-gray-900 mb-3">Provider Usage Distribution</h3>
        <div class="space-y-2">
            <template x-for="provider in stats.providers" :key="provider.provider">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 rounded-full"
                             :class="{
                                'bg-blue-500': provider.provider === 'deepseek',
                                'bg-green-500': provider.provider === 'openai',
                                'bg-purple-500': provider.provider === 'gemini'
                             }"></div>
                        <span class="text-sm font-medium" x-text="provider.provider.toUpperCase()"></span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-medium" x-text="provider.tokens.toLocaleString()"></span>
                        <span class="text-xs text-gray-500 ml-1">tokens</span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Provider Status -->
    <div>
        <h3 class="font-medium text-gray-900 mb-3">AI Provider Status</h3>
        <div class="space-y-2">
            <template x-for="(status, name) in providers" :key="name">
                <div class="flex items-center justify-between p-3 border rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 rounded-full"
                             :class="status.enabled ? 'bg-green-500' : 'bg-red-500'"></div>
                        <div>
                            <p class="font-medium" x-text="status.name"></p>
                            <p class="text-xs text-gray-500" x-text="status.model"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-sm px-2 py-1 rounded"
                              :class="status.enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                            <span x-text="status.enabled ? 'Enabled' : 'Disabled'"></span>
                        </span>
                        <p class="text-xs text-gray-500 mt-1" x-text="'Priority: ' + status.priority"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Actions -->
    <div class="mt-6 pt-6 border-t">
        <div class="flex space-x-3">
            <button @click="refreshStats()"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-redo mr-2"></i>Refresh
            </button>
            <a href="{{ route('payment.pricing') }}"
               x-show="stats.limits.remaining_percentage > 80"
               class="px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg hover:opacity-90">
                <i class="fas fa-crown mr-2"></i>Upgrade Plan
            </a>
        </div>
    </div>
</div>

<script>
    function aiDashboard() {
        return {
            stats: {
                total: { tokens: 0, requests: 0, estimated_cost: 0 },
                monthly: { tokens: 0, requests: 0 },
                providers: [],
                limits: { monthly_tokens: 0, remaining_tokens: 0, remaining_percentage: 0 }
            },
            providers: {},
            loading: false,
            
            init() {
                this.loadStats();
                this.loadProviders();
            },
            
            loadStats() {
                this.loading = true;
                fetch('/api/ai/usage-stats')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.stats = data.stats;
                        }
                        this.loading = false;
                    });
            },
            
            loadProviders() {
                fetch('/api/ai/providers-status')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.providers = data.providers;
                        }
                    });
            },
            
            refreshStats() {
                this.loadStats();
                this.loadProviders();
            }
        }
    }
</script>