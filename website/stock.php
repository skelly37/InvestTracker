<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InvestTracker - Stock Detail</title>
    <link rel="stylesheet" href="styles.css">
    <script src="scripts.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="chart.js" defer></script>
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="logo">InvestTracker</div>
            <div class="search-bar">
                <input type="text" value="NVDA" placeholder="Search">
                <button>🔍</button>
            </div>
            <div class="nav-buttons">
                <button>Users</button>
                <button>Favorites</button>
                <button>Settings</button>
            </div>
        </header>

        <section class="stock-info-header">
            <div class="stock-name">NVIDIA Corporation</div>
            <div class="stock-ticker">NVDA: NASDAQ</div>
            <div class="stock-price">
                <span>$111.43</span>
                <span class="minus">(-2.11% DTD)</span>
            </div>
            <div class="stock-time">09:43 UTC</div>
        </section>

        <section class="stock-chart-section">
            <div class="chart-controls">
                <select>
                    <option>1D (1 Day)</option>
                    <option>1W</option>
                    <option>1M</option>
                    <option>1Y</option>
                </select>
            </div>
            <div class="chart-placeholder">
                <canvas id="priceChart" style="width: 100%; height: 400px;"></canvas>
            </div>
        </section>

        <section class="stock-analysis-columns">
            <div class="analysis-column">
                <h3>Technical analysis</h3>
                <p>RSI(14): <span class="minus">38.41</span></p>
                <p>STS(14,3): <span class="minus">42.60</span></p>
                <p>MACD(12,26,9): <span class="plus">-15.1966</span></p>
                <p>TRIX(14,9): <span class="neutral">-0.0052</span></p>
                <p>Williams %R(10): <span class="minus">-92.79</span></p>
                <p>CCI(14): <span class="minus">-117.37</span></p>
                <p>ROC(15): <span class="plus">1.4554</span></p>
                <p>ULT(7,14,28): <span class="neutral">48.39</span></p>
            </div>
            <div class="analysis-column">
                <h3>Financial analysis</h3>
                <p>P/B: 39.48</p>
                <p>P/E: 42.97</p>
                <p>PEG: 65.45</p>
                <p>Dividend per share: $0.01</p>
                <p>ROA: 69.44</p>
                <p>ROE: 125.66</p>
                <p>ROI: 79.19</p>
                <p>EV/EBITDA: 37.61</p>
            </div>
            <div class="analysis-column">
                <h3>General</h3>
                <p>Daily change: <span class="plus">$11.43 (+10%)</span></p>
                <p>Monthly change: <span class="plus">$6.00 (+5%)</span></p>
                <p>Quarterly change: <span class="minus">-20.30 (-15.30%)</span></p>
                <p>Yearly change: <span class="plus">$4.34 (+3.40%)</span></p>
                <p>Market cap: 2.80T</p>
                <p>Shares outstanding: 24.4B</p>
                <p>Revenue: <span class="plus">130.5B</span></p>
                <p>Net Income: <span class="plus">72.88K</span></p>
            </div>
        </section>
    </div>
</body>
</html>
