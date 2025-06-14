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
                
                // ZMIANA: Używamy naszego controllera zamiast bezpośredniego API
                return fetch(`/stock/quote?symbol=${encodeURIComponent(symbol)}`)
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
                console.log('Loading popular stock:', symbol);
                
                if (!symbol || typeof symbol !== 'string') {
                    console.error('Invalid symbol for popular stock:', symbol);
                    return Promise.resolve(null);
                }
                
                // ZMIANA: Używamy naszego controllera zamiast bezpośredniego API
                return fetch(`/stock/quote?symbol=${encodeURIComponent(symbol)}`)
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
                console.log('Loading market index:', symbol);
                
                if (!symbol || typeof symbol !== 'string') {
                    console.error('Invalid symbol for market index:', symbol);
                    return Promise.resolve(null);
                }
                
                // ZMIANA: Używamy naszego controllera zamiast bezpośredniego API
                return fetch(`/stock/quote?symbol=${encodeURIComponent(symbol)}`)
                    .then(response => {
                        console.log('Response for market index', symbol, ':', response.status);
                        return response.json();
                    })
                    .then(quote => {
                        console.log('Quote data for market index', symbol, ':', quote);
                        return {
                            symbol: symbol,
                            name: quote.name || symbol,
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
        console.log('Updating recently viewed section with:', stocks);
        
        stocks.forEach(stock => {
            const stockElement = document.querySelector(`#recently-viewed-container .stock-entry[data-symbol="${stock.symbol}"]`);
            if (stockElement) {
                updateStockElement(stockElement, stock);
            }
        });
    }
    
    function updatePopularStocksSection(stocks) {
        console.log('Updating popular stocks section with:', stocks);
        
        stocks.forEach(stock => {
            const stockElement = document.querySelector(`#popular-stocks-container .stock-entry[data-symbol="${stock.symbol}"]`);
            if (stockElement) {
                updateStockElement(stockElement, stock);
            }
        });
    }
    
    function updateMarketIndicesSection(stocks) {
        console.log('Updating market indices section with:', stocks);
        
        stocks.forEach(stock => {
            const stockElement = document.querySelector(`#market-indices-container .stock-entry[data-symbol="${stock.symbol}"]`);
            if (stockElement) {
                updateStockElement(stockElement, stock);
            }
        });
    }
    
    function updateStockElement(element, stock) {
        const priceElement = element.querySelector('.stock-price');
        const changeElement = element.querySelector('.stock-change');
        
        if (stock.currentPrice > 0) {
            priceElement.textContent = `${stock.currency} ${stock.currentPrice.toFixed(2)}`;
            
            if (stock.previousClose > 0) {
                const change = stock.currentPrice - stock.previousClose;
                const changePercent = ((change / stock.previousClose) * 100);
                
                const changeText = change >= 0 ? `+${change.toFixed(2)}` : change.toFixed(2);
                const changePercentText = change >= 0 ? `+${changePercent.toFixed(2)}%` : `${changePercent.toFixed(2)}%`;
                const changeClass = change > 0 ? 'text--success' : (change < 0 ? 'text--danger' : 'text--neutral');
                
                changeElement.innerHTML = `<span class="${changeClass}">${changeText} (${changePercentText})</span>`;
            } else {
                changeElement.textContent = 'N/A';
            }
        } else {
            priceElement.textContent = 'N/A';
            changeElement.textContent = 'N/A';
        }
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>