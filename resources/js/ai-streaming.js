/**
 * Querentia AI - Streaming Module
 * Handles real-time SSE communication with the AI generation backend.
 */

class AIStreaming {
    constructor(options = {}) {
        this.options = {
            onStart: () => {},
            onChunk: () => {},
            onComplete: () => {},
            onError: () => {},
            ...options
        };
        
        this.eventSource = null;
        this.isStreaming = false;
    }

    /**
     * Initialize the stream connection
     * @param {string} url - The endpoint URL
     * @param {object} payload - The data to send (sections, provider)
     */
    async start(url, payload) {
        if (this.isStreaming) return;
        this.isStreaming = true;

        try {
            // Because EventSource only supports GET by default, 
            // we use a standard fetch POST to trigger the stream if needed, 
            // OR we append the data as a query string for simple SSE.
            // However, since journal sections are large, we use a URL reference.
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Note: We are using EventSource. Since it's GET based, 
            // the Controller is set up to handle the incoming request.
            // For large section data, we typically pass it through a session-based 
            // trigger or encoded params. Here we assume the standard SSE route.
            
            const queryString = new URLSearchParams({
                sections: JSON.stringify(payload.sections),
                provider: payload.provider || 'deepseek'
            }).toString();

            this.eventSource = new EventSource(`${url}?${queryString}`);

            // 1. Listen for Start
            this.eventSource.addEventListener('start', (e) => {
                const data = JSON.parse(e.data);
                this.options.onStart(data);
            });

            // 2. Listen for Chunks (The "Typing" effect)
            this.eventSource.addEventListener('chunk', (e) => {
                const data = JSON.parse(e.data);
                if (data.content) {
                    this.options.onChunk(data.content);
                }
            });

            // 3. Listen for Completion
            this.eventSource.addEventListener('complete', (e) => {
                const data = JSON.parse(e.data);
                this.stop();
                this.options.onComplete(data);
            });

            // 4. Handle Errors
            this.eventSource.addEventListener('error', (e) => {
                let message = 'An error occurred during generation.';
                try {
                    const data = JSON.parse(e.data);
                    message = data.message || message;
                } catch(err) { /* use default message */ }
                
                this.stop();
                this.options.onError(message);
            });

        } catch (error) {
            this.stop();
            this.options.onError(error.message);
        }
    }

    stop() {
        if (this.eventSource) {
            this.eventSource.close();
            this.eventSource = null;
        }
        this.isStreaming = false;
    }
}

// Global initialization for the Querentia UI
window.QuerentiaAI = {
    instance: null,

    initStreaming: function(config) {
        this.instance = new AIStreaming(config);
        return this.instance;
    },

    generate: async function(journalId, sections, provider) {
        const url = journalId 
            ? `/journal/stream/${journalId}` 
            : '/journal/stream';
            
        if (!this.instance) {
            console.error('AI Streaming not initialized. Call initStreaming first.');
            return;
        }

        await this.instance.start(url, { sections, provider });
    }
};

// Auto-init on load
document.addEventListener('DOMContentLoaded', () => {
    console.log('Querentia AI Streaming initialized.');
});