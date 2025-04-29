<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InvestTracker - Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <script src="scripts.js" defer></script>
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="logo">InvestTracker</div>
            <div class="search-bar">
                <input type="text" placeholder="Search">
                <button>🔍</button>
            </div>
            <div class="nav-buttons">
                <button>Users</button>
                <button>Favorites</button>
                <button>Settings</button>
            </div>
        </header>

        <main class="dashboard-columns">
            <div class="dashboard-column">
                <h2>Recently viewed</h2>
                <div class="divider"></div>

                <div class="stock-entry">
                    <div class="stock-name">Nvidia (WSE: NVDA)</div>
                    <div class="stock-price">$102.60</div>
                    <div class="stock-change minus">-4.56% (DTD)</div>
                </div>
                <div class="stock-entry">
                    <div class="stock-name">Tesla (WSE: TSLA)</div>
                    <div class="stock-price">$220.54</div>
                    <div class="stock-change plus">+3.40% (DTD)</div>
                </div>
                <div class="stock-entry">
                    <div class="stock-name">Apple (WSE: AAPL)</div>
                    <div class="stock-price">$88.88</div>
                    <div class="stock-change neutral">0.00% (DTD)</div>
                </div>
                <div class="stock-entry">
                    <div class="stock-name">Meta (NASDAQ: META)</div>
                    <div class="stock-price">$600.00</div>
                    <div class="stock-change minus">-2.33% (DTD)</div>
                </div>
                <div class="stock-entry">
                    <div class="stock-name">Berkshire Hathaway (NYSE: BRK.B)</div>
                    <div class="stock-price">$102.60</div>
                    <div class="stock-change plus">+10.43% (DTD)</div>
                </div>
            </div>

            <div class="dashboard-column">
                <h2>Biggest changes</h2>
                <div class="divider"></div>

                <div class="stock-entry">
                    <div class="stock-name">Berkshire Hathaway (NYSE: BRK.B)</div>
                    <div class="stock-price">$102.60</div>
                    <div class="stock-change plus">+10.43% (DTD)</div>
                </div>
                <div class="stock-entry">
                    <div class="stock-name">Nvidia (WSE: NVDA)</div>
                    <div class="stock-price">$102.60</div>
                    <div class="stock-change minus">-4.56% (DTD)</div>
                </div>
                <div class="stock-entry">
                    <div class="stock-name">Tesla (WSE: TSLA)</div>
                    <div class="stock-price">$220.54</div>
                    <div class="stock-change plus">+3.40% (DTD)</div>
                </div>
                <div class="stock-entry">
                    <div class="stock-name">Meta (NASDAQ: META)</div>
                    <div class="stock-price">$600.00</div>
                    <div class="stock-change minus">-2.33% (DTD)</div>
                </div>
                <div class="stock-entry">
                    <div class="stock-name">Apple (WSE: AAPL)</div>
                    <div class="stock-price">$88.88</div>
                    <div class="stock-change neutral">0.00% (DTD)</div>
                </div>
            </div>

            <div class="dashboard-column">
                <h2>Indices</h2>
                <div class="divider"></div>

                <div class="stock-entry">
                    <div class="stock-name">S&P 500</div>
                    <div class="stock-price">$1999.99</div>
                    <div class="stock-change plus">+1.23% (DTD)</div>
                </div>
                <div class="stock-entry">
                    <div class="stock-name">NASDAQ Composite</div>
                    <div class="stock-price">$5432.10</div>
                    <div class="stock-change minus">-3.21% (DTD)</div>
                </div>
                <div class="stock-entry">
                    <div class="stock-name">Dow Jones Industrial Average</div>
                    <div class="stock-price">$10987.64</div>
                    <div class="stock-change plus">+3.33% (DTD)</div>
                </div>
                <div class="stock-entry">
                    <div class="stock-name">Russell 2000</div>
                    <div class="stock-price">$555.44</div>
                    <div class="stock-change minus">-2.00% (DTD)</div>
                </div>
                <div class="stock-entry">
                    <div class="stock-name">NYSE Composite</div>
                    <div class="stock-price">$4342.64</div>
                    <div class="stock-change neutral">0.00% (DTD)</div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
