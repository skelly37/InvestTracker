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
                
                <div class="dashboard-column">
                    <div class="dashboard-column__title">Market Indices</div>
                    <div class="divider"></div>
                    
                    <div id="market-indices-container">
                        <?php if (!empty($indices)): ?>
                            <?php foreach ($indices as $symbol): ?>
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
    loadRecentlyViewed();
    loadPopularStocks();
    loadMarketIndices();
    
    function loadRecentlyViewed() {
        const recentlyViewed = <?= json_encode($recentlyViewed ?? []) ?>;
        if (recentlyViewed && recentlyViewed.length > 0) {
            const promises = recentlyViewed.map(stock => {
                const symbol = stock.symbol || stock.ticker || stock.name;
                if (!symbol) {
                    return Promise.resolve(null);
                }
                
                return fetch(`/stock/quote?symbol=${encodeURIComponent(symbol)}`)
                    .then(response => {
                        return response.json();
                    })
                    .then(quote => {
                        return {
                            symbol: symbol,
                            name: quote.name || stock.name || 'N/A',
                            currentPrice: quote.currentPrice || 0,
                            previousClose: quote.previousClose || 0,
                            currency: quote.currency || 'USD'
                        };
                    })
                    .catch(error => {
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
                updateRecentlyViewedSection(validResults);
            });
        }
    }
    
    function loadPopularStocks() {
        const popularStocks = <?= json_encode($popularStocks ?? []) ?>;

        if (popularStocks && popularStocks.length > 0) {
            const promises = popularStocks.map(symbol => {

                if (!symbol || typeof symbol !== 'string') {
                    return Promise.resolve(null);
                }
                
                return fetch(`/stock/quote?symbol=${encodeURIComponent(symbol)}`)
                    .then(response => {
                        return response.json();
                    })
                    .then(quote => {
                        return {
                            symbol: symbol,
                            name: quote.name || 'N/A',
                            currentPrice: quote.currentPrice || 0,
                            previousClose: quote.previousClose || 0,
                            currency: quote.currency || 'USD'
                        };
                    })
                    .catch(error => {
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
                updatePopularStocksSection(validResults);
            });
        }
    }
    
    function loadMarketIndices() {
        const indices = <?= json_encode($indices ?? []) ?>;

        if (indices && indices.length > 0) {
            const promises = indices.map(symbol => {

                if (!symbol || typeof symbol !== 'string') {
                    return Promise.resolve(null);
                }
                
                return fetch(`/stock/quote?symbol=${encodeURIComponent(symbol)}`)
                    .then(response => {
                        return response.json();
                    })
                    .then(quote => {
                        return {
                            symbol: symbol,
                            name: quote.name || symbol,
                            currentPrice: quote.currentPrice || 0,
                            previousClose: quote.previousClose || 0,
                            currency: quote.currency || 'USD'
                        };
                    })
                    .catch(error => {
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
                updateMarketIndicesSection(validResults);
            });
        }
    }
    
    function updateRecentlyViewedSection(stocks) {
        stocks.forEach(stock => {
            const stockElement = document.querySelector(`#recently-viewed-container .stock-entry[data-symbol="${stock.symbol}"]`);
            if (stockElement) {
                updateStockElement(stockElement, stock);
            }
        });
    }
    
    function updatePopularStocksSection(stocks) {
        stocks.forEach(stock => {
            const stockElement = document.querySelector(`#popular-stocks-container .stock-entry[data-symbol="${stock.symbol}"]`);
            if (stockElement) {
                updateStockElement(stockElement, stock);
            }
        });
    }
    
    function updateMarketIndicesSection(stocks) {
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