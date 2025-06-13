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
                <div class="stock-header__name"><?= htmlspecialchars($stockData['name'] ?? $symbol) ?></div>
                <div class="stock-header__price">
                    <?= formatPrice($stockData['price'] ?? null) ?>
                </div>
                <div class="stock-header__change">
                    <?= formatChange($stockData['change'] ?? null, $stockData['change_percent'] ?? null) ?>
                </div>
                <div class="stock-header__time">
                    Last updated: <?= formatDate($stockData['last_updated'] ?? null) ?>
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
                <div class="chart-controls">
                    <label for="period">Time Period:</label>
                    <select id="period" class="input" style="width: auto; display: inline-block; margin-left: 10px;">
                        <?php foreach ($validPeriods as $period): ?>
                            <option value="<?= htmlspecialchars($period) ?>" 
                                    <?= $period === $currentPeriod ? 'selected' : '' ?>>
                                <?= strtoupper($period) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="chart-container">
                    <canvas id="stockChart" width="800" height="400"></canvas>
                    <div id="chartLoading" class="text-center mt-3 hidden">
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
                        <span class="metric__label">Current Price:</span>
                        <span class="metric__value"><?= formatPrice($stockData['price'] ?? null) ?></span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">Day's Change:</span>
                        <span class="metric__value"><?= formatChange($stockData['change'] ?? null, $stockData['change_percent'] ?? null) ?></span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">Day's Range:</span>
                        <span class="metric__value">
                            <?= formatPrice($stockData['day_low'] ?? null) ?> - <?= formatPrice($stockData['day_high'] ?? null) ?>
                        </span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">Previous Close:</span>
                        <span class="metric__value"><?= formatPrice($stockData['previous_close'] ?? null) ?></span>
                    </div>
                </div>
                
                <div class="analysis-section">
                    <div class="analysis-section__title">Volume & Market Cap</div>
                    <div class="metric">
                        <span class="metric__label">Volume:</span>
                        <span class="metric__value"><?= isset($stockData['volume']) ? number_format($stockData['volume']) : 'N/A' ?></span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">Avg Volume:</span>
                        <span class="metric__value"><?= isset($stockData['avg_volume']) ? number_format($stockData['avg_volume']) : 'N/A' ?></span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">Market Cap:</span>
                        <span class="metric__value"><?= isset($stockData['market_cap']) ? '$' . number_format($stockData['market_cap'] / 1000000000, 2) . 'B' : 'N/A' ?></span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">Exchange:</span>
                        <span class="metric__value"><?= htmlspecialchars($stockData['exchange'] ?? 'N/A') ?></span>
                    </div>
                </div>
                
                <div class="analysis-section">
                    <div class="analysis-section__title">52-Week Range</div>
                    <div class="metric">
                        <span class="metric__label">52-Week Low:</span>
                        <span class="metric__value"><?= formatPrice($stockData['fifty_two_week_low'] ?? null) ?></span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">52-Week High:</span>
                        <span class="metric__value"><?= formatPrice($stockData['fifty_two_week_high'] ?? null) ?></span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">P/E Ratio:</span>
                        <span class="metric__value"><?= isset($stockData['pe_ratio']) ? number_format($stockData['pe_ratio'], 2) : 'N/A' ?></span>
                    </div>
                    <div class="metric">
                        <span class="metric__label">Dividend Yield:</span>
                        <span class="metric__value"><?= isset($stockData['dividend_yield']) ? number_format($stockData['dividend_yield'], 2) . '%' : 'N/A' ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const favoriteBtn = document.querySelector('.favorite-btn');
    const periodSelect = document.getElementById('period');
    const chartCanvas = document.getElementById('stockChart');
    const chartLoading = document.getElementById('chartLoading');
    const chartError = document.getElementById('chartError');
    
    // Favorite button functionality
    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', function() {
            const action = this.dataset.action;
            const symbol = this.dataset.symbol;
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
    
    // Chart functionality
    if (periodSelect && chartCanvas) {
        periodSelect.addEventListener('change', function() {
            loadChart(this.value);
        });
        
        // Load initial chart
        loadChart(periodSelect.value);
    }
    
    function loadChart(period) {
        chartLoading.classList.remove('hidden');
        chartError.classList.add('hidden');
        
        const symbol = favoriteBtn.dataset.symbol;
        
        fetch(`/stock/historical?symbol=${encodeURIComponent(symbol)}&period=${encodeURIComponent(period)}`)
            .then(response => response.json())
            .then(data => {
                chartLoading.classList.add('hidden');
                
                if (data.success && data.data.length > 0) {
                    renderChart(data.data);
                } else {
                    chartError.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Chart error:', error);
                chartLoading.classList.add('hidden');
                chartError.classList.remove('hidden');
            });
    }
    
    function renderChart(data) {
        const ctx = chartCanvas.getContext('2d');
        ctx.clearRect(0, 0, chartCanvas.width, chartCanvas.height);
        
        if (data.length === 0) return;
        
        const padding = 40;
        const chartWidth = chartCanvas.width - 2 * padding;
        const chartHeight = chartCanvas.height - 2 * padding;
        
        // Find min/max values
        const prices = data.map(d => d.close);
        const minPrice = Math.min(...prices);
        const maxPrice = Math.max(...prices);
        const priceRange = maxPrice - minPrice;
        
        // Draw axes
        ctx.strokeStyle = '#4A4A4A';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(padding, padding);
        ctx.lineTo(padding, chartCanvas.height - padding);
        ctx.lineTo(chartCanvas.width - padding, chartCanvas.height - padding);
        ctx.stroke();
        
        // Draw price line
        ctx.strokeStyle = '#0066CC';
        ctx.lineWidth = 2;
        ctx.beginPath();
        
        data.forEach((point, index) => {
            const x = padding + (index / (data.length - 1)) * chartWidth;
            const y = padding + ((maxPrice - point.close) / priceRange) * chartHeight;
            
            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });
        
        ctx.stroke();
        
        // Draw labels
        ctx.fillStyle = '#4A4A4A';
        ctx.font = '12px Arial';
        ctx.textAlign = 'right';
        ctx.fillText('$' + maxPrice.toFixed(2), padding - 5, padding + 5);
        ctx.fillText('$' + minPrice.toFixed(2), padding - 5, chartCanvas.height - padding + 5);
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>