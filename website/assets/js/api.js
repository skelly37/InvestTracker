/**
 * InvestTracker API Client
 * 
 * Handles all AJAX requests and API interactions for the frontend
 */

class InvestTrackerAPI {
    constructor() {
        this.baseUrl = window.location.origin;
        this.cache = new Map();
        this.cacheTimeout = 5 * 60 * 1000; // 5 minutes
        this.requestQueue = new Map();
        this.retryAttempts = 3;
        this.retryDelay = 1000; // 1 second
        
        // Bind context
        this.get = this.get.bind(this);
        this.post = this.post.bind(this);
        this.request = this.request.bind(this);
    }
    
    /**
     * Get CSRF token from meta tag or session
     */
    getCSRFToken() {
        // Try to get from meta tag first
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        if (metaToken) {
            return metaToken.getAttribute('content');
        }
        
        // Fallback to global variable
        if (window.csrfToken) {
            return window.csrfToken;
        }
        
        // Last resort - try to find in form
        const tokenInput = document.querySelector('input[name="csrf_token"]');
        return tokenInput ? tokenInput.value : '';
    }
    
    /**
     * Make a GET request
     */
    async get(endpoint, params = {}, options = {}) {
        const url = new URL(endpoint, this.baseUrl);
        
        // Add query parameters
        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== undefined) {
                url.searchParams.set(key, params[key]);
            }
        });
        
        const cacheKey = url.toString();
        
        // Check cache first
        if (!options.noCache && this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            if (Date.now() - cached.timestamp < this.cacheTimeout) {
                return cached.data;
            }
        }
        
        // Check if request is already in progress
        if (this.requestQueue.has(cacheKey)) {
            return this.requestQueue.get(cacheKey);
        }
        
        const requestPromise = this.request('GET', url.toString(), null, options);
        this.requestQueue.set(cacheKey, requestPromise);
        
        try {
            const result = await requestPromise;
            
            // Cache successful responses
            if (!options.noCache && result.success !== false) {
                this.cache.set(cacheKey, {
                    data: result,
                    timestamp: Date.now()
                });
            }
            
            return result;
        } finally {
            this.requestQueue.delete(cacheKey);
        }
    }
    
    /**
     * Make a POST request
     */
    async post(endpoint, data = {}, options = {}) {
        const url = new URL(endpoint, this.baseUrl);
        
        // Add CSRF token to POST data
        if (typeof data === 'object' && data instanceof FormData) {
            data.append('csrf_token', this.getCSRFToken());
        } else if (typeof data === 'object') {
            data.csrf_token = this.getCSRFToken();
        }
        
        return this.request('POST', url.toString(), data, options);
    }
    
    /**
     * Make a raw HTTP request with retry logic
     */
    async request(method, url, data = null, options = {}) {
        const requestOptions = {
            method: method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            credentials: 'same-origin'
        };
        
        // Handle request body
        if (data !== null) {
            if (data instanceof FormData) {
                requestOptions.body = data;
            } else if (typeof data === 'object') {
                if (options.json) {
                    requestOptions.headers['Content-Type'] = 'application/json';
                    requestOptions.body = JSON.stringify(data);
                } else {
                    requestOptions.headers['Content-Type'] = 'application/x-www-form-urlencoded';
                    requestOptions.body = new URLSearchParams(data);
                }
            } else {
                requestOptions.body = data;
            }
        }
        
        let lastError;
        
        // Retry logic
        for (let attempt = 0; attempt <= this.retryAttempts; attempt++) {
            try {
                const response = await fetch(url, requestOptions);
                
                // Handle HTTP errors
                if (!response.ok) {
                    if (response.status === 401) {
                        this.handleAuthError();
                        throw new Error('Authentication required');
                    } else if (response.status === 403) {
                        throw new Error('Access forbidden');
                    } else if (response.status === 404) {
                        throw new Error('Resource not found');
                    } else if (response.status === 429) {
                        throw new Error('Too many requests - please slow down');
                    } else if (response.status >= 500) {
                        throw new Error('Server error - please try again later');
                    } else {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                }
                
                // Parse response
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return await response.json();
                } else {
                    return await response.text();
                }
                
            } catch (error) {
                lastError = error;
                
                // Don't retry on client errors or auth errors
                if (error.message.includes('Authentication') || 
                    error.message.includes('forbidden') || 
                    error.message.includes('not found')) {
                    throw error;
                }
                
                // Don't retry on the last attempt
                if (attempt === this.retryAttempts) {
                    break;
                }
                
                // Wait before retrying
                await this.delay(this.retryDelay * Math.pow(2, attempt));
            }
        }
        
        throw lastError;
    }
    
    /**
     * Handle authentication errors
     */
    handleAuthError() {
        if (window.InvestTracker && window.InvestTracker.showNotification) {
            window.InvestTracker.showNotification('Session expired. Please log in again.', 'warning');
        }
        
        // Redirect to login after a delay
        setTimeout(() => {
            window.location.href = '/login';
        }, 2000);
    }
    
    /**
     * Utility function to create delays
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    /**
     * Clear cache
     */
    clearCache() {
        this.cache.clear();
    }
    
    /**
     * Stock-specific API methods
     */
    
    async searchStocks(query) {
        return this.get('/stock/autocomplete', { q: query });
    }
    
    async getStockQuote(symbol) {
        return this.get('/stock/quote', { symbol });
    }
    
    async getHistoricalData(symbol, period = '1d') {
        return this.get('/stock/historical', { symbol, period });
    }
    
    async addToFavorites(symbol) {
        return this.post('/dashboard/add-favorite', { symbol });
    }
    
    async removeFromFavorites(symbol) {
        return this.post('/dashboard/remove-favorite', { symbol });
    }
    
    /**
     * User management API methods
     */
    
    async updateUserRole(userId, role) {
        return this.post('/users/update-role', { user_id: userId, role });
    }
    
    async toggleUserActive(userId) {
        return this.post('/users/toggle-active', { user_id: userId });
    }
    
    async deleteUser(userId) {
        return this.post('/users/delete', { user_id: userId });
    }
    
    async createUser(userData) {
        return this.post('/users/create', userData);
    }
    
    /**
     * WebSocket connection for real-time data (if implemented)
     */
    
    initWebSocket() {
        if (!window.WebSocket) {
            console.warn('WebSocket not supported');
            return;
        }
        
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const wsUrl = `${protocol}//${window.location.host}/ws`;
        
        try {
            this.ws = new WebSocket(wsUrl);
            
            this.ws.onopen = () => {
                console.log('WebSocket connected');
                this.subscribeToUpdates();
            };
            
            this.ws.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    this.handleWebSocketMessage(data);
                } catch (error) {
                    console.error('Failed to parse WebSocket message:', error);
                }
            };
            
            this.ws.onclose = () => {
                console.log('WebSocket disconnected');
                // Attempt to reconnect after 5 seconds
                setTimeout(() => this.initWebSocket(), 5000);
            };
            
            this.ws.onerror = (error) => {
                console.error('WebSocket error:', error);
            };
            
        } catch (error) {
            console.error('Failed to initialize WebSocket:', error);
        }
    }
    
    subscribeToUpdates() {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            // Subscribe to stock updates for current page
            const path = window.location.pathname;
            
            if (path.startsWith('/stock')) {
                const urlParams = new URLSearchParams(window.location.search);
                const symbol = urlParams.get('symbol');
                if (symbol) {
                    this.ws.send(JSON.stringify({
                        action: 'subscribe',
                        symbol: symbol
                    }));
                }
            } else if (path === '/dashboard' || path === '/favorites') {
                this.ws.send(JSON.stringify({
                    action: 'subscribe',
                    type: 'portfolio'
                }));
            }
        }
    }
    
    handleWebSocketMessage(data) {
        switch (data.type) {
            case 'stock_update':
                this.updateStockDisplay(data.symbol, data.data);
                break;
            case 'market_status':
                this.updateMarketStatus(data.status);
                break;
            default:
                console.log('Unknown WebSocket message type:', data.type);
        }
    }
    
    updateStockDisplay(symbol, stockData) {
        // Update stock displays on the page
        const stockElements = document.querySelectorAll(`[data-symbol="${symbol}"]`);
        
        stockElements.forEach(element => {
            const priceElement = element.querySelector('.stock__price');
            const changeElement = element.querySelector('.stock__change');
            
            if (priceElement && stockData.price) {
                priceElement.textContent = window.InvestTracker.formatPrice(stockData.price);
            }
            
            if (changeElement && stockData.change !== undefined && stockData.change_percent !== undefined) {
                changeElement.innerHTML = window.InvestTracker.formatChange(stockData.change, stockData.change_percent);
            }
        });
    }
    
    updateMarketStatus(status) {
        const statusElement = document.querySelector('.market-status');
        if (statusElement) {
            statusElement.textContent = status.message;
            statusElement.className = `market-status market-status--${status.status}`;
        }
    }
}

// Create global API instance
window.API = new InvestTrackerAPI();

// Initialize WebSocket on page load
document.addEventListener('DOMContentLoaded', () => {
    // Only initialize WebSocket if we're logged in
    if (document.querySelector('.dashboard-layout')) {
        window.API.initWebSocket();
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = InvestTrackerAPI;
}