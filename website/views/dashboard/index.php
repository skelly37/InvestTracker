<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navigation.php';
?>

<div class="dashboard-layout">
    <div class="main-content">
        <div class="container">
            <?php if ($flashMessage): ?>
                <div class="alert alert--success mb-2">
                    <?= htmlspecialchars($flashMessage) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert--danger mb-2">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <div class="dashboard-columns">
                <!-- Recently Viewed -->
                <div class="dashboard-column">
                    <div class="dashboard-column__title">Recently Viewed</div>
                    <div class="divider"></div>
                    
                    <div id="recently-viewed-container">
                        <?php if (!empty($recentlyViewed)): ?>
                            <?php foreach ($recentlyViewed as $item): ?>
                                <div class="stock-entry" data-symbol="<?= htmlspecialchars($item['symbol']) ?>">
                                    <div class="stock__name">
                                        <a href="/stock?symbol=<?= urlencode($item['symbol']) ?>">
                                            <span class="stock-name-text"><?= htmlspecialchars($item['symbol']) ?></span>
                                        </a>
                                    </div>
                                    <div class="stock__price stock-price">Loading...</div>
                                    <div class="stock__change stock-change">Loading...</div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center">No recently viewed stocks</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Popular Stocks -->
                <div class="dashboard-column">
                    <div class="dashboard-column__title">Popular Stocks</div>
                    <div class="divider"></div>
                    
                    <div id="popular-stocks-container">
                        <?php if (!empty($popularStocks)): ?>
                            <?php foreach ($popularStocks as $symbol): ?>
                                <div class="stock-entry" data-symbol="<?= htmlspecialchars($symbol) ?>">
                                    <div class="stock__name">
                                        <a href="/stock?symbol=<?= urlencode($symbol) ?>">
                                            <span class="stock-name-text"><?= htmlspecialchars($symbol) ?></span>
                                        </a>
                                    </div>
                                    <div class="stock__price stock-price">Loading...</div>
                                    <div class="stock__change stock-change">Loading...</div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center">Unable to load popular stocks</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Market Indices -->
                <div class="dashboard-column">
                    <div class="dashboard-column__title">Market Indices</div>
                    <div class="divider"></div>
                    
                    <div id="market-indices-container">
                        <?php if (!empty($indices)): ?>
                            <?php foreach ($indices as $symbol): ?>
                                <div class="stock-entry" data-symbol="<?= htmlspecialchars($symbol) ?>">
                                    <div class="stock__name">
                                        <span class="stock-name-text"><?= htmlspecialchars($symbol) ?></span>
                                    </div>
                                    <div class="stock__price stock-price">Loading...</div>
                                    <div class="stock__change stock-change">Loading...</div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center">Unable to load market indices</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard loading...');
    
    // Load data for all sections
    loadRecentlyViewed();
    loadPopularStocks();
    loadMarketIndices();
    
    function loadRecentlyViewed() {
        const recentlyViewed = <?= json_encode($recentlyViewed ?? []) ?>;
        console.log('Recently viewed data:', recentlyViewed);
        
        if (recentlyViewed && recentlyViewed.length > 0) {
            const promises = recentlyViewed.map(stock => {
                const symbol = stock.symbol || stock.ticker || stock.name;
                console.log('Loading recently viewed stock:', symbol, stock);
                
                if (!symbol) {
                    console.error('No symbol found for recently viewed stock:', stock);
                    return Promise.resolve(null);
                }
                
                return fetch(`http://localhost:5000/quote?q=${encodeURIComponent(symbol)}`)
                    .then(response => {
                        console.log('Response for recently viewed', symbol, ':', response.status);
                        return response.json();
                    })
                    .then(quote => {
                        console.log('Quote data for recently viewed', symbol, ':', quote);
                        return {
                            symbol: symbol,
                            name: quote.name || stock.name || 'N/A',
                            currentPrice: quote.currentPrice || 0,
                            previousClose: quote.previousClose || 0,
                            currency: quote.currency || 'USD'
                        };
                    })
                    .catch(error => {
                        console.error('Error loading recently viewed stock', symbol, ':', error);
                        return {
                            symbol: symbol,
                            name: stock.name || 'Error',
                            currentPrice: 0,
                            previousClose: 0,
                            currency: 'USD'
                        };
                    });
            });
            
            Promise.all(promises).then(results => {
                const validResults = results.filter(r => r !== null);
                console.log('Recently viewed results:', validResults);
                updateRecentlyViewedSection(validResults);
            });
        } else {
            console.log('No recently viewed stocks');
        }
    }
    
    function loadPopularStocks() {
        const popularStocks = <?= json_encode($popularStocks ?? []) ?>;
        console.log('Popular stocks data:', popularStocks);
        
        if (popularStocks && popularStocks.length > 0) {
            const promises = popularStocks.map(symbol => {
                // symbol is just a string, not an object
                console.log('Loading popular stock:', symbol);
                
                if (!symbol || typeof symbol !== 'string') {
                    console.error('Invalid symbol for popular stock:', symbol);
                    return Promise.resolve(null);
                }
                
                return fetch(`http://localhost:5000/quote?q=${encodeURIComponent(symbol)}`)
                    .then(response => {
                        console.log('Response for popular stock', symbol, ':', response.status);
                        return response.json();
                    })
                    .then(quote => {
                        console.log('Quote data for popular stock', symbol, ':', quote);
                        return {
                            symbol: symbol,
                            name: quote.name || 'N/A',
                            currentPrice: quote.currentPrice || 0,
                            previousClose: quote.previousClose || 0,
                            currency: quote.currency || 'USD'
                        };
                    })
                    .catch(error => {
                        console.error('Error loading popular stock', symbol, ':', error);
                        return {
                            symbol: symbol,
                            name: 'Error',
                            currentPrice: 0,
                            previousClose: 0,
                            currency: 'USD'
                        };
                    });
            });
            
            Promise.all(promises).then(results => {
                const validResults = results.filter(r => r !== null);
                console.log('Popular stocks results:', validResults);
                updatePopularStocksSection(validResults);
            });
        } else {
            console.log('No popular stocks');
        }
    }
    
    function loadMarketIndices() {
        const indices = <?= json_encode($indices ?? []) ?>;
        console.log('Market indices data:', indices);
        
        if (indices && indices.length > 0) {
            const promises = indices.map(symbol => {
                // symbol is just a string, not an object
                console.log('Loading market index:', symbol);
                
                if (!symbol || typeof symbol !== 'string') {
                    console.error('Invalid symbol for market index:', symbol);
                    return Promise.resolve(null);
                }
                
                return fetch(`http://localhost:5000/quote?q=${encodeURIComponent(symbol)}`)
                    .then(response => {
                        console.log('Response for market index', symbol, ':', response.status);
                        return response.json();
                    })
                    .then(quote => {
                        console.log('Quote data for market index', symbol, ':', quote);
                        return {
                            symbol: symbol,
                            name: quote.name || 'N/A',
                            currentPrice: quote.currentPrice || 0,
                            previousClose: quote.previousClose || 0,
                            currency: quote.currency || 'USD'
                        };
                    })
                    .catch(error => {
                        console.error('Error loading market index', symbol, ':', error);
                        return {
                            symbol: symbol,
                            name: 'Error',
                            currentPrice: 0,
                            previousClose: 0,
                            currency: 'USD'
                        };
                    });
            });
            
            Promise.all(promises).then(results => {
                const validResults = results.filter(r => r !== null);
                console.log('Market indices results:', validResults);
                updateMarketIndicesSection(validResults);
            });
        } else {
            console.log('No market indices');
        }
    }
    
    function updateRecentlyViewedSection(stocks) {
        const container = document.querySelector('#recently-viewed-container');
        if (!container) {
            console.log('Recently viewed container not found');
            return;
        }
        
        container.innerHTML = '';
        
        stocks.forEach(stock => {
            const change = stock.currentPrice - stock.previousClose;
            const changePercent = stock.previousClose ? ((change / stock.previousClose) * 100) : 0;
            const changeColor = change >= 0 ? '#22c55e' : '#ef4444';
            const changeText = change >= 0 ? `+${change.toFixed(2)}` : change.toFixed(2);
            const changePercentText = change >= 0 ? `+${changePercent.toFixed(2)}%` : `${changePercent.toFixed(2)}%`;
            
            const stockElement = document.createElement('div');
            stockElement.style.cssText = `
                background: #FFF8DC;
                border: 1px solid #4A4A4A;
                padding: 15px;
                margin-bottom: 15px;
                text-align: center;
            `;
            
            stockElement.innerHTML = `
                <div style="font-weight: bold; margin-bottom: 5px;">
                    <a href="/stock?symbol=${encodeURIComponent(stock.symbol)}" style="color: #4A4A4A; text-decoration: none;">${stock.symbol}</a>
                </div>
                <div style="font-size: 18px; font-weight: bold; margin-bottom: 5px;">
                    ${stock.currentPrice.toFixed(2)} ${stock.currency}
                </div>
                <div style="font-size: 12px; color: #666; margin-bottom: 5px;">
                    ${stock.name}
                </div>
                <div style="color: ${changeColor}; font-weight: bold;">
                    ${changeText} (${changePercentText})
                </div>
            `;
            
            container.appendChild(stockElement);
        });
    }
    
    function updatePopularStocksSection(stocks) {
        const container = document.querySelector('#popular-stocks-container');
        if (!container) {
            console.log('Popular stocks container not found');
            return;
        }
        
        container.innerHTML = '';
        
        stocks.forEach(stock => {
            const change = stock.currentPrice - stock.previousClose;
            const changePercent = stock.previousClose ? ((change / stock.previousClose) * 100) : 0;
            const changeColor = change >= 0 ? '#22c55e' : '#ef4444';
            const changeText = change >= 0 ? `+${change.toFixed(2)}` : change.toFixed(2);
            const changePercentText = change >= 0 ? `+${changePercent.toFixed(2)}%` : `${changePercent.toFixed(2)}%`;
            
            const stockElement = document.createElement('div');
            stockElement.style.cssText = `
                background: #FFF8DC;
                border: 1px solid #4A4A4A;
                padding: 15px;
                margin-bottom: 15px;
                text-align: center;
            `;
            
            stockElement.innerHTML = `
                <div style="font-weight: bold; margin-bottom: 5px;">
                    <a href="/stock?symbol=${encodeURIComponent(stock.symbol)}" style="color: #4A4A4A; text-decoration: none;">${stock.symbol}</a>
                </div>
                <div style="font-size: 18px; font-weight: bold; margin-bottom: 5px;">
                    ${stock.currentPrice.toFixed(2)} ${stock.currency}
                </div>
                <div style="font-size: 12px; color: #666; margin-bottom: 5px;">
                    ${stock.name}
                </div>
                <div style="color: ${changeColor}; font-weight: bold;">
                    ${changeText} (${changePercentText})
                </div>
            `;
            
            container.appendChild(stockElement);
        });
    }
    
    function updateMarketIndicesSection(indices) {
        const container = document.querySelector('#market-indices-container');
        if (!container) {
            console.log('Market indices container not found');
            return;
        }
        
        container.innerHTML = '';
        
        indices.forEach(index => {
            const change = index.currentPrice - index.previousClose;
            const changePercent = index.previousClose ? ((change / index.previousClose) * 100) : 0;
            const changeColor = change >= 0 ? '#22c55e' : '#ef4444';
            const changeText = change >= 0 ? `+${change.toFixed(2)}` : change.toFixed(2);
            const changePercentText = change >= 0 ? `+${changePercent.toFixed(2)}%` : `${changePercent.toFixed(2)}%`;
            
            const indexElement = document.createElement('div');
            indexElement.style.cssText = `
                background: #FFF8DC;
                border: 1px solid #4A4A4A;
                padding: 15px;
                margin-bottom: 15px;
                text-align: center;
            `;
            
            indexElement.innerHTML = `
                <div style="font-weight: bold; margin-bottom: 5px;">
                    <a href="/stock?symbol=${encodeURIComponent(index.symbol)}" style="color: #4A4A4A; text-decoration: none;">${index.symbol}</a>
                </div>
                <div style="font-size: 18px; font-weight: bold; margin-bottom: 5px;">
                    ${index.currentPrice.toFixed(2)} ${index.currency}
                </div>
                <div style="font-size: 12px; color: #666; margin-bottom: 5px;">
                    ${index.name}
                </div>
                <div style="color: ${changeColor}; font-weight: bold;">
                    ${changeText} (${changePercentText})
                </div>
            `;
            
            container.appendChild(indexElement);
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>