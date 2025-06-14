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
            
            <div class="search-results">
                <h1 class="search-results__title">Your Favorites</h1>
                
                <?php if (!empty($favorites)): ?>
                    <div class="search-results__count">
                        <?= count($favorites) ?> favorite<?= count($favorites) !== 1 ? 's' : '' ?>
                    </div>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Symbol</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Exchange</th>
                                <th>Price</th>
                                <th>Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($favorites as $favorite): ?>
                                <tr data-symbol="<?= htmlspecialchars($favorite['symbol']) ?>">
                                    <td>
                                        <a href="/stock?symbol=<?= urlencode($favorite['symbol']) ?>" class="stock__name">
                                            <?= htmlspecialchars($favorite['symbol']) ?>
                                        </a>
                                    </td>
                                    <td class="stock-name">Loading...</td>
                                    <td class="stock-type">Stock</td>
                                    <td class="stock-exchange">Loading...</td>
                                    <td>
                                        <div class="stock__price stock-price">Loading...</div>
                                        <div class="stock__change stock-change">Loading...</div>
                                    </td>
                                    <td>
                                        <button class="btn btn--danger btn--small remove-favorite-btn" 
                                                data-symbol="<?= htmlspecialchars($favorite['symbol']) ?>"
                                                data-csrf="<?= htmlspecialchars(csrf_token()) ?>">
                                            🗑️
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="card text-center">
                        <div class="card__body">
                            <h3>No favorites yet</h3>
                            <p>Search for stocks and add them to your favorites to see them here.</p>
                            <a href="/search" class="btn btn--primary">Search Stocks</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const removeButtons = document.querySelectorAll('.remove-favorite-btn');
    const favoriteRows = document.querySelectorAll('tr[data-symbol]');
    
    // Load current data for each favorite
    favoriteRows.forEach(row => {
        const symbol = row.dataset.symbol;
        loadStockData(symbol, row);
    });
    
    // Remove favorite functionality
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const symbol = this.dataset.symbol;
            const csrf = this.dataset.csrf;
            
            if (confirm(`Remove ${symbol} from favorites?`)) {
                fetch('/dashboard/remove-favorite', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `symbol=${encodeURIComponent(symbol)}&csrf_token=${encodeURIComponent(csrf)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to remove from favorites');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to remove from favorites');
                });
            }
        });
    });
    
    function loadStockData(symbol, row) {
        fetch(`http://localhost:5000/quote?q=${encodeURIComponent(symbol)}`)
            .then(response => response.json())
            .then(data => {
                updateRowData(row, data);
            })
            .catch(error => {
                console.error('Error loading stock data for', symbol, ':', error);
                updateRowError(row);
            });
    }
    
    function updateRowData(row, info) {
        row.querySelector('.stock-name').textContent = info.name || 'N/A';
        row.querySelector('.stock-exchange').textContent = info.exchange || 'N/A';
        
        const priceElement = row.querySelector('.stock-price');
        const changeElement = row.querySelector('.stock-change');
        
        if (info.currentPrice) {
            priceElement.textContent = `${info.currentPrice.toFixed(2)}`;
            
            // Calculate change from currentPrice to previousClose
            if (info.previousClose) {
                const change = info.currentPrice - info.previousClose;
                const changePercent = ((change / info.previousClose) * 100);
                
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
    
    function updateRowError(row) {
        row.querySelector('.stock-name').textContent = 'Error';
        row.querySelector('.stock-exchange').textContent = 'N/A';
        row.querySelector('.stock-price').textContent = 'N/A';
        row.querySelector('.stock-change').textContent = 'N/A';
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>