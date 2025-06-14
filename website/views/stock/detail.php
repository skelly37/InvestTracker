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
            
            <!-- Stock Header -->
            <div class="stock-header">
                <div class="stock-header__ticker"><?= htmlspecialchars($symbol) ?></div>
                <div class="stock-header__name" id="stockName">Loading...</div>
                <div class="stock-header__price" id="stockPrice">Loading...</div>
                <div class="stock-header__change" id="stockChange">Loading...</div>
                <div class="stock-header__time" id="stockTime">
                    Last updated: <span id="lastUpdated">Loading...</span>
                </div>
                
                <div class="mt-2">
                    <?php if ($isInFavorites): ?>
                        <button class="btn btn--danger favorite-btn" 
                                data-action="remove" 
                                data-symbol="<?= htmlspecialchars($symbol) ?>"
                                data-csrf="<?= htmlspecialchars($csrf_token) ?>">
                            💔 Remove from Favorites
                        </button>
                    <?php else: ?>
                        <button class="btn btn--primary favorite-btn" 
                                data-action="add" 
                                data-symbol="<?= htmlspecialchars($symbol) ?>"
                                data-csrf="<?= htmlspecialchars($csrf_token) ?>">
                            ❤️ Add to Favorites
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Chart Section -->
            <div class="chart-section">
                <div class="chart-container">
                    <canvas id="priceChart" width="800" height="400"></canvas>
                    <div id="chartLoading" class="text-center mt-3">
                        <div class="spinner"></div>
                        <p>Loading chart data...</p>
                    </div>
                    <div id="chartError" class="text-center mt-3 hidden">
                        <p class="text--danger">Unable to load chart data</p>
                    </div>
                </div>
            </div>
            
            <!-- Analysis Sections -->
            <div class="analysis-grid">
                <div class="analysis-section">
                    <div class="analysis-section__title">Price Information</div>
                    <div class="metric">
        <span class="metric__label">Previous Close Price:</span>
        <span class="metric__value" id="previousClose">Loading...</span>
    </div>
    <div class="metric">
        <span class="metric__label">Open Price:</span>
        <span class="metric__value" id="openPrice">Loading...</span>
    </div>
    <div class="metric">
        <span class="metric__label">Exchange:</span>
        <span class="metric__value" id="exchange">Loading...</span>
    </div>
    <div class="metric">
        <span class="metric__label">Currency:</span>
        <span class="metric__value" id="currency">Loading...</span>
    </div>
</div>
                
                <div class="analysis-section">
                    <div class="analysis-section__title">Financial Metrics</div>
                    <div class="metric">
                        <span class="metric__label">P/B Ratio:</span>
                        <span class="metric__value" id="priceToBook">Loading...</span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">ROA:</span>
                        <span class="metric__value" id="returnOnAssets">Loading...</span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">ROE:</span>
                        <span class="metric__value" id="returnOnEquity">Loading...</span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">Enterprise/EBITDA:</span>
                        <span class="metric__value" id="enterpriseToEbitda">Loading...</span>
                    </div>
                </div>
                
                <div class="analysis-section">
                    <div class="analysis-section__title">Company Information</div>
                    <div class="metric">
                        <span class="metric__label">Market Cap:</span>
                        <span class="metric__value" id="marketCap">Loading...</span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">Shares Outstanding:</span>
                        <span class="metric__value" id="sharesOutstanding">Loading...</span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">Total Revenue:</span>
                        <span class="metric__value" id="totalRevenue">Loading...</span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">Financial Currency:</span>
                        <span class="metric__value" id="financialCurrency">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const symbol = '<?= htmlspecialchars($symbol) ?>';
    const favoriteBtn = document.querySelector('.favorite-btn');
    
    // Load stock data
    loadStockData(symbol);
    
    // Favorite button functionality
    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', function() {
            const action = this.dataset.action;
            const csrf = this.dataset.csrf;
            
            const url = action === 'add' ? '/dashboard/add-favorite' : '/dashboard/remove-favorite';
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `symbol=${encodeURIComponent(symbol)}&csrf_token=${encodeURIComponent(csrf)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (action === 'add') {
                        this.textContent = '💔 Remove from Favorites';
                        this.dataset.action = 'remove';
                        this.classList.remove('btn--primary');
                        this.classList.add('btn--danger');
                    } else {
                        this.textContent = '❤️ Add to Favorites';
                        this.dataset.action = 'add';
                        this.classList.remove('btn--danger');
                        this.classList.add('btn--primary');
                    }
                } else {
                    alert(data.message || 'Operation failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Operation failed');
            });
        });
    }
    
function loadStockData(symbol) {
    // Load both quote and history data from your Flask server
    Promise.all([
        fetch(`http://localhost:5000/quote?q=${encodeURIComponent(symbol)}`).then(response => response.json()),
        fetch(`http://localhost:5000/history?q=${encodeURIComponent(symbol)}&interval=1mo`).then(response => response.json())
    ])
    .then(([quoteData, historyData]) => {
        updateStockInfo(quoteData, historyData);
        loadChart({ info: quoteData, history: historyData });
        document.getElementById('chartLoading').classList.add('hidden');
    })
    .catch(error => {
        console.error('Error loading stock data:', error);
        document.getElementById('chartLoading').classList.add('hidden');
        document.getElementById('chartError').classList.remove('hidden');
        
        // Update UI with error state
        document.getElementById('stockName').textContent = 'Error loading data';
        document.getElementById('stockPrice').textContent = 'N/A';
        document.getElementById('stockChange').textContent = 'N/A';
    });
}

function updateStockInfo(info, history) {
    document.getElementById('stockName').textContent = info.name || 'N/A';
    document.getElementById('stockPrice').textContent = info.currentPrice?.toFixed(2) || 'N/A';
    document.getElementById('lastUpdated').textContent = new Date().toLocaleString();
    
    // Calculate price change from currentPrice to previousClose
    if (info.currentPrice && info.previousClose) {
        const change = info.currentPrice - info.previousClose;
        const changePercent = ((change / info.previousClose) * 100);
        
        const changeText = change >= 0 ? `+${change.toFixed(2)}` : change.toFixed(2);
        const changePercentText = change >= 0 ? `+${changePercent.toFixed(2)}%` : `${changePercent.toFixed(2)}%`;
        
        document.getElementById('stockChange').innerHTML = 
            `<span style="color: ${change >= 0 ? 'green' : 'red'}">${changeText} (${changePercentText})</span>`;
    }
    
    // Update Price Information section
    document.getElementById('previousClose').textContent = info.previousClose?.toFixed(2) || 'N/A';
    document.getElementById('openPrice').textContent = info.openPrice?.toFixed(2) || 'N/A';
    document.getElementById('exchange').textContent = info.exchange || 'N/A';
    document.getElementById('currency').textContent = info.currency || 'N/A';
    
    // Update other sections
    document.getElementById('priceToBook').textContent = info.priceToBook?.toFixed(2) || 'N/A';
    document.getElementById('returnOnAssets').textContent = info.returnOnAssets ? (info.returnOnAssets * 100).toFixed(2) + '%' : 'N/A';
    document.getElementById('returnOnEquity').textContent = info.returnOnEquity ? (info.returnOnEquity * 100).toFixed(2) + '%' : 'N/A';
    document.getElementById('enterpriseToEbitda').textContent = info.enterpriseToEbitda?.toFixed(2) || 'N/A';
    
    document.getElementById('marketCap').textContent = info.marketCap ? formatLargeNumber(info.marketCap) : 'N/A';
    document.getElementById('sharesOutstanding').textContent = info.sharesOutstanding ? formatLargeNumber(info.sharesOutstanding) : 'N/A';
    document.getElementById('totalRevenue').textContent = info.totalRevenue ? formatLargeNumber(info.totalRevenue) : 'N/A';
    document.getElementById('financialCurrency').textContent = info.financialCurrency || 'N/A';
}
    
    function formatLargeNumber(num) {
        if (num >= 1e12) return (num / 1e12).toFixed(2) + 'T';
        if (num >= 1e9) return (num / 1e9).toFixed(2) + 'B';
        if (num >= 1e6) return (num / 1e6).toFixed(2) + 'M';
        if (num >= 1e3) return (num / 1e3).toFixed(2) + 'K';
        return num.toFixed(2);
    }
    
function loadChart(data) {
    const ctx = document.getElementById('priceChart').getContext('2d');
    
    const timestamps = Object.keys(data.history);
    const prices = Object.values(data.history);
    
    // Convert timestamps to DD/MM format with leading zeros
    const labels = timestamps.map(timestamp => {
        const date = new Date(parseInt(timestamp) * 1000);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        return `${day}/${month}`;
    });
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                data: prices,
                borderWidth: 2,
                borderColor: '#4A90E2',
                backgroundColor: 'rgba(74, 144, 226, 0.1)',
                fill: true,
                tension: 0.1,
                pointRadius: 2,
                pointHoverRadius: 6,
                pointBackgroundColor: '#4A90E2',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: `Price (${data.info?.currency || 'USD'})`
                    },
                    beginAtZero: false,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        // Show all labels (daily) without limit
                        maxTicksLimit: false,
                        autoSkip: false
                    }
                }
            },
            plugins: {
                legend: {
                        display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#4A90E2',
                    borderWidth: 1,
                    callbacks: {
                        title: function(context) {
                            // Show full date in tooltip
                            const timestamp = timestamps[context[0].dataIndex];
                            const date = new Date(parseInt(timestamp) * 1000);
                            return date.toLocaleDateString('en-US', {
                                weekday: 'short',
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });
                        },
                        label: function(context) {
                            const price = context.parsed.y;
                            const currency = data.info?.currency || 'USD';
                            return `${currency} ${price.toFixed(2)}`;
                        }
                    }
                }
            }
        }
    });
}
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>