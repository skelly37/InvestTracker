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
                    <?php if ($isFavorite): ?>
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
                <!-- Time Interval Selector -->
                <div class="chart-controls mb-3">
                    <label class="label">Chart Time Interval:</label>
                    <select class="input" id="chartTimeInterval" style="width: 200px; display: inline-block;">
                        <option value="1d">1 Day</option>
                        <option value="5d">5 Days</option>
                        <option value="1mo">1 Month</option>
                        <option value="3mo">3 Months</option>
                        <option value="1y">1 Year</option>
                        <option value="5y">5 Years</option>
                        <option value="max">Maximum</option>
                    </select>
                </div>
                
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

let currentSymbol = '<?= htmlspecialchars($symbol) ?>';
let currentStockInfo = null;

document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
        return;
    }

    const symbol = '<?= htmlspecialchars($symbol) ?>';
    const favoriteBtn = document.querySelector('.favorite-btn');
    const intervalSelector = document.getElementById('chartTimeInterval');
    const defaultInterval = '<?= htmlspecialchars($chartTimeInterval ?? "1mo") ?>';
    
    let currentChart = null;
    let currentInterval = defaultInterval;
    
    intervalSelector.value = defaultInterval;
    
    loadStockData(symbol, currentInterval);
    
    intervalSelector.addEventListener('change', function() {
        const newInterval = this.value;
        if (newInterval !== currentInterval) {
            currentInterval = newInterval;
            loadChartData(symbol, newInterval);
        }
    });
    
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
                alert('Operation failed');
            });
        });
    }
    
    function loadStockData(symbol, interval) {
        showChartLoading(true);

        Promise.all([
            fetch(`/stock/quote?symbol=${encodeURIComponent(symbol)}`).then(response => response.json()),
            fetch(`/stock/history?symbol=${encodeURIComponent(symbol)}&interval=${encodeURIComponent(interval)}`).then(response => response.json())
        ])
        .then(([quoteData, historyData]) => {
            updateStockInfo(quoteData);
            loadChart(historyData, quoteData);
            showChartLoading(false);
        })
        .catch(error => {
            showChartLoading(false);
            showChartError(true);
            
            document.getElementById('stockName').textContent = 'Error loading data';
            document.getElementById('stockPrice').textContent = 'N/A';
            document.getElementById('stockChange').textContent = 'N/A';
        });
    }
    
    function loadChartData(symbol, interval) {
        showChartLoading(true);

        fetch(`/stock/history?symbol=${encodeURIComponent(symbol)}&interval=${encodeURIComponent(currentInterval)}`)
            .then(response => response.json())
            .then(historyData => {
                const currentInfo = { currency: document.getElementById('currency').textContent || 'USD' };
                loadChart(historyData, currentInfo);
                showChartLoading(false);
            })
            .catch(error => {
                showChartLoading(false);
                showChartError(true);
            });
    }
    
    function showChartLoading(show) {
        const loadingEl = document.getElementById('chartLoading');
        const errorEl = document.getElementById('chartError');
        
        if (show) {
            loadingEl.classList.remove('hidden');
            errorEl.classList.add('hidden');
        } else {
            loadingEl.classList.add('hidden');
        }
    }
    
    function showChartError(show) {
        const errorEl = document.getElementById('chartError');
        if (show) {
            errorEl.classList.remove('hidden');
        } else {
            errorEl.classList.add('hidden');
        }
    }

    function updateStockInfo(info) {
        document.getElementById('stockName').textContent = info.name || 'N/A';
        document.getElementById('stockPrice').textContent = info.currentPrice?.toFixed(2) || 'N/A';
        document.getElementById('lastUpdated').textContent = new Date().toLocaleString();
        
        if (info.currentPrice && info.previousClose) {
            const change = info.currentPrice - info.previousClose;
            const changePercent = ((change / info.previousClose) * 100);
            
            const changeText = change >= 0 ? `+${change.toFixed(2)}` : change.toFixed(2);
            const changePercentText = change >= 0 ? `+${changePercent.toFixed(2)}%` : `${changePercent.toFixed(2)}%`;
            
            document.getElementById('stockChange').innerHTML = 
                `<span style="color: ${change >= 0 ? 'green' : 'red'}">${changeText} (${changePercentText})</span>`;
        }
        
        document.getElementById('previousClose').textContent = info.previousClose?.toFixed(2) || 'N/A';
        document.getElementById('openPrice').textContent = info.openPrice?.toFixed(2) || 'N/A';
        document.getElementById('exchange').textContent = info.exchange || 'N/A';
        document.getElementById('currency').textContent = info.currency || 'N/A';
        
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
        
    function loadChart(historyData, stockInfo) {
        const ctx = document.getElementById('priceChart').getContext('2d');

        if (currentChart) {
            currentChart.destroy();
        }

        let actualData = historyData;
        if (historyData && historyData.success && historyData.data) {
            actualData = historyData.data;
        }

        const timestamps = Object.keys(actualData);
        const prices = Object.values(actualData);


        if (timestamps.length === 0 || prices.length === 0) {
            showChartError(true);
            return;
        }

        const labels = timestamps.map(timestamp => {
            const date = new Date(parseInt(timestamp) * 1000);

            let formattedLabel = '';

            if (currentInterval === '1d') {
                // 14:30
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                formattedLabel = `${hours}:${minutes}`;
            } else if (currentInterval === '5d') {
                // 13/01 14:30
                const day5d = String(date.getDate()).padStart(2, '0');
                const month5d = String(date.getMonth() + 1).padStart(2, '0');
                const hours5d = String(date.getHours()).padStart(2, '0');
                const minutes5d = String(date.getMinutes()).padStart(2, '0');
                formattedLabel = `${day5d}/${month5d} ${hours5d}:${minutes5d}`;
            } else if (currentInterval === '1mo') {
                // 13/01
                const day1mo = String(date.getDate()).padStart(2, '0');
                const month1mo = String(date.getMonth() + 1).padStart(2, '0');
                formattedLabel = `${day1mo}/${month1mo}`;
            } else if (currentInterval === '3mo') {
                // 13/01 2024
                const day3mo = String(date.getDate()).padStart(2, '0');
                const month3mo = String(date.getMonth() + 1).padStart(2, '0');
                const year3mo = date.getFullYear();
                formattedLabel = `${day3mo}/${month3mo} ${year3mo}`;
            } else if (currentInterval === '1y' || currentInterval === '5y' || currentInterval === 'max') {
                // 01 2024
                const monthLong = String(date.getMonth() + 1).padStart(2, '0');
                const yearLong = date.getFullYear();
                formattedLabel = `${monthLong} ${yearLong}`;
            } else {
                const dayDefault = String(date.getDate()).padStart(2, '0');
                const monthDefault = String(date.getMonth() + 1).padStart(2, '0');
                formattedLabel = `${dayDefault}/${monthDefault}`;
            }

            return formattedLabel;
        });

        currentChart = new Chart(ctx, {
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
                            text: `Price (${stockInfo?.currency || 'USD'})`
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
                            maxTicksLimit: currentInterval === '1d' ? 8 : 12,
                            autoSkip: true,
                            font: {
                                size: 11
                            }
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
                                const timestamp = timestamps[context[0].dataIndex];
                                const date = new Date(parseInt(timestamp) * 1000);
                                return date.toLocaleDateString('en-US', {
                                    weekday: 'short',
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric',
                                    hour: currentInterval === '1d' || currentInterval === '5d' ? '2-digit' : undefined,
                                    minute: currentInterval === '1d' || currentInterval === '5d' ? '2-digit' : undefined
                                });
                            },
                            label: function(context) {
                                const price = context.parsed.y;
                                const currency = stockInfo?.currency || 'USD';
                                return `${currency} ${price.toFixed(2)}`;
                            }
                        }
                    }
                }
            }
        });
    }
})
</script>