// Version: 2.0 - Fixed URL length issue with POST requests
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
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // For large content, use POST request with fetch for streaming
            // This avoids URL length limits with EventSource
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'text/event-stream',
                },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            // Handle streaming response
            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                buffer += decoder.decode(value, { stream: true });
                const lines = buffer.split('\n');
                buffer = lines.pop(); // Keep incomplete line in buffer

                for (const line of lines) {
                    if (line.startsWith('event: ')) {
                        this.currentEvent = line.substring(7);
                    } else if (line.startsWith('data: ')) {
                        const data = JSON.parse(line.substring(6));
                        this.handleEvent(this.currentEvent, data);
                    }
                }
            }

        } catch (error) {
            this.stop();
            this.options.onError(error.message);
        }
    }

    handleEvent(eventType, data) {
        switch (eventType) {
            case 'start':
                this.options.onStart(data);
                break;
            case 'chunk':
                if (data.content) {
                    this.options.onChunk(data.content);
                }
                break;
            case 'complete':
                this.stop();
                this.options.onComplete(data);
                break;
            case 'server-error':
                const message = data.message || 'An error occurred during generation.';
                this.stop();
                this.options.onError(message);
                break;
            default:
                console.log('Unknown event type:', eventType, data);
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
            ? `/ai/stream/${journalId}` 
            : '/ai/stream';             
            
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