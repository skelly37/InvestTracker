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
                
                <!-- Popular Stocks -->
                <div class="dashboard-column">
                    <div class="dashboard-column__title">Popular Stocks</div>
                    <div class="divider"></div>
                    
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
                
                <!-- Market Indices -->
                <div class="dashboard-column">
                    <div class="dashboard-column__title">Market Indices</div>
                    <div class="divider"></div>
                    
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stockEntries = document.querySelectorAll('.stock-entry[data-symbol]');
    
    // Load current data for each stock
    stockEntries.forEach(entry => {
        const symbol = entry.dataset.symbol;
        loadStockData(symbol, entry);
    });
    
    function loadStockData(symbol, entry) {
        fetch(`/stock/quote?symbol=${encodeURIComponent(symbol)}`)
            .then(response => response.json())
            .then(data => {
                if (data.info) {
                    updateStockEntry(entry, data.info, data.history);
                } else {
                    updateStockError(entry);
                }
            })
            .catch(error => {
                console.error('Error loading stock data for', symbol, ':', error);
                updateStockError(entry);
            });
    }
    
    function updateStockEntry(entry, info, history) {
        // Update name if we got a full name
        const nameElement = entry.querySelector('.stock-name-text');
        if (info.name && nameElement) {
            nameElement.textContent = info.name;
        }
        
        const priceElement = entry.querySelector('.stock-price');
        const changeElement = entry.querySelector('.stock-change');
        
        if (info.currentPrice) {
            priceElement.textContent = `${info.currency || 'USD'} ${info.currentPrice.toFixed(2)}`;
            
            // Calculate change from history if available
            if (history && Object.keys(history).length > 1) {
                const timestamps = Object.keys(history).sort((a, b) => parseInt(a) - parseInt(b));
                const prices = timestamps.map(t => history[t]);
                
                if (prices.length >= 2) {
                    const currentPrice = info.currentPrice;
                    const previousPrice = prices[0];
                    
                    const change = currentPrice - previousPrice;
                    const changePercent = ((change / previousPrice) * 100);
                    
                    const changeText = change >= 0 ? `+${change.toFixed(2)}` : change.toFixed(2);
                    const changePercentText = change >= 0 ? `+${changePercent.toFixed(2)}%` : `${changePercent.toFixed(2)}%`;
                    const changeClass = change > 0 ? 'text--success' : (change < 0 ? 'text--danger' : 'text--neutral');
                    
                    changeElement.innerHTML = `<span class="${changeClass}">${changeText} (${changePercentText})</span>`;
                } else {
                    changeElement.textContent = 'N/A';
                }
            } else {
                changeElement.textContent = 'N/A';
            }
        } else {
            priceElement.textContent = 'N/A';
            changeElement.textContent = 'N/A';
        }
    }
    
    function updateStockError(entry) {
        entry.querySelector('.stock-price').textContent = 'Error';
        entry.querySelector('.stock-change').textContent = 'N/A';
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>