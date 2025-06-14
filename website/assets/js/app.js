(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        initializeApp();
    });
    
    function initializeApp() {
        initSearchAutocomplete();

        initFlashMessages();
        
        initTooltips();
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

    function initFlashMessages() {
        const flashMessages = document.querySelectorAll('.alert');
        flashMessages.forEach(message => {
            if (message.classList.contains('alert--success')) {
                setTimeout(() => {
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 300);
                }, 5000);
            }
        });
    }
    
    function initTooltips() {
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
})();