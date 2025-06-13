
// Global app functionality
(function() {
    'use strict';
    
    // Initialize app when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        initializeApp();
    });
    
    function initializeApp() {
        // Initialize search autocomplete
        initSearchAutocomplete();
        
        // Initialize auto-refresh if enabled
        initAutoRefresh();
        
        // Initialize flash message auto-hide
        initFlashMessages();
        
        // Initialize tooltips
        initTooltips();
        
        // Initialize keyboard shortcuts
        initKeyboardShortcuts();
    }
    
    function initSearchAutocomplete() {
        const searchInput = document.querySelector('.search-bar__input');
        if (!searchInput) return;
        
        let timeout;
        let autocompleteContainer;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                hideAutocomplete();
                return;
            }
            
            timeout = setTimeout(() => {
                fetchSuggestions(query);
            }, 300);
        });
        
        searchInput.addEventListener('blur', function() {
            // Hide autocomplete after a small delay to allow clicks
            setTimeout(hideAutocomplete, 150);
        });
        
        function fetchSuggestions(query) {
            fetch(`/stock/autocomplete?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.suggestions && data.suggestions.length > 0) {
                        showAutocomplete(data.suggestions);
                    } else {
                        hideAutocomplete();
                    }
                })
                .catch(error => {
                    console.error('Autocomplete error:', error);
                    hideAutocomplete();
                });
        }
        
        function showAutocomplete(suggestions) {
            hideAutocomplete();
            
            autocompleteContainer = document.createElement('div');
            autocompleteContainer.className = 'autocomplete-dropdown';
            autocompleteContainer.style.cssText = `
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #FFF8DC;
                border: 1px solid #4A4A4A;
                border-top: none;
                max-height: 300px;
                overflow-y: auto;
                z-index: 1000;
            `;
            
            suggestions.forEach(suggestion => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item';
                item.style.cssText = `
                    padding: 10px;
                    cursor: pointer;
                    border-bottom: 1px solid #E0E0E0;
                `;
                item.innerHTML = `
                    <strong>${suggestion.symbol}</strong> - ${suggestion.name}<br>
                    <small style="color: #666;">${suggestion.type} • ${suggestion.exchange}</small>
                `;
                
                item.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#F5F5DC';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
                
                item.addEventListener('click', function() {
                    window.location.href = `/stock?symbol=${encodeURIComponent(suggestion.symbol)}`;
                });
                
                autocompleteContainer.appendChild(item);
            });
            
            const searchBar = searchInput.closest('.search-bar');
            searchBar.style.position = 'relative';
            searchBar.appendChild(autocompleteContainer);
        }
        
        function hideAutocomplete() {
            if (autocompleteContainer) {
                autocompleteContainer.remove();
                autocompleteContainer = null;
            }
        }
    }
    
    function initAutoRefresh() {
        const refreshInterval = localStorage.getItem('autoRefresh');
        if (!refreshInterval || refreshInterval === '0') return;
        
        const intervalMs = parseInt(refreshInterval) * 1000;
        
        setInterval(() => {
            // Only refresh if we're on a page that shows live data
            const path = window.location.pathname;
            if (path === '/dashboard' || path === '/favorites' || path.startsWith('/stock')) {
                refreshPageData();
            }
        }, intervalMs);
    }
    
    function refreshPageData() {
        // This would refresh specific data without full page reload
        // For now, we'll just indicate that data is being refreshed
        const indicator = document.createElement('div');
        indicator.textContent = 'Refreshing data...';
        indicator.style.cssText = `
            position: fixed;
            top: 10px;
            right: 10px;
            background: #4A4A4A;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            z-index: 1000;
        `;
        
        document.body.appendChild(indicator);
        
        setTimeout(() => {
            indicator.remove();
        }, 2000);
    }
    
    function initFlashMessages() {
        const flashMessages = document.querySelectorAll('.alert');
        flashMessages.forEach(message => {
            // Auto-hide success messages after 5 seconds
            if (message.classList.contains('alert--success')) {
                setTimeout(() => {
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 300);
                }, 5000);
            }
        });
    }
    
    function initTooltips() {
        // Simple tooltip implementation
        const elementsWithTooltips = document.querySelectorAll('[data-tooltip]');
        
        elementsWithTooltips.forEach(element => {
            element.addEventListener('mouseenter', showTooltip);
            element.addEventListener('mouseleave', hideTooltip);
        });
        
        function showTooltip(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.dataset.tooltip;
            tooltip.style.cssText = `
                position: absolute;
                background: #4A4A4A;
                color: white;
                padding: 5px 8px;
                border-radius: 3px;
                font-size: 12px;
                z-index: 1000;
                pointer-events: none;
                white-space: nowrap;
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
            
            this._tooltip = tooltip;
        }
        
        function hideTooltip() {
            if (this._tooltip) {
                this._tooltip.remove();
                this._tooltip = null;
            }
        }
    }
    
    function initKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K: Focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.querySelector('.search-bar__input');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
            
            // Escape: Close modals
            if (e.key === 'Escape') {
                const modals = document.querySelectorAll('.modal:not(.hidden)');
                modals.forEach(modal => {
                    modal.classList.add('hidden');
                });
            }
        });
    }
    
    // Global utility functions
    window.InvestTracker = {
        formatPrice: function(price) {
            return price ? '$' + parseFloat(price).toFixed(2) : 'N/A';
        },
        
        formatChange: function(change, changePercent) {
            if (change === null || changePercent === null) return 'N/A';
            
            const sign = change >= 0 ? '+' : '';
            const className = change > 0 ? 'text--success' : (change < 0 ? 'text--danger' : 'text--neutral');
            
            return `<span class="${className}">${sign}${parseFloat(change).toFixed(2)} (${sign}${parseFloat(changePercent).toFixed(2)}%)</span>`;
        },
        
        showNotification: function(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert--${type}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
                min-width: 250px;
                opacity: 0;
                transition: opacity 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            
            // Trigger animation
            setTimeout(() => notification.style.opacity = '1', 10);
            
            // Auto-remove
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }
    };
})();