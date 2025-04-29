<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InvestTracker - Search</title>
    <link rel="stylesheet" href="styles.css">
    <script src="scripts.js" defer></script>
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="logo">InvestTracker</div>
            <div class="search-bar">
                <input type="text" value="Nvidia" placeholder="Search">
                <button>🔍</button>
            </div>
            <div class="nav-buttons">
                <button>Users</button>
                <button>Favorites</button>
                <button>Settings</button>
            </div>
        </header>

        <main class="search-results-table">
            <h2>Search results for: <em>“Nvidia”</em></h2>
            <table>
                <thead>
                    <tr>
                        <th>Symbol</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Exchange</th>
                        <th>Price</th>
                        <th>Favorite</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>NVDA</td>
                        <td>NVIDIA Corporation</td>
                        <td>Stock</td>
                        <td>NASDAQ</td>
                        <td><span class="minus">$111.43 (−2.11% DTD)</span></td>
                        <td>🧡</td>
                    </tr>
                    <tr>
                        <td>NVDQ</td>
                        <td>T-Rex 2X Inverse NVIDIA Daily Target</td>
                        <td>ETF</td>
                        <td>NYSE</td>
                        <td><span class="plus">$3.59 (+4.06% DTD)</span></td>
                        <td>🧡</td>
                    </tr>
                    <tr>
                        <td>NVDX</td>
                        <td>T-Rex 2X Long NVIDIA Daily Target ETF</td>
                        <td>ETF</td>
                        <td>NYSE</td>
                        <td><span class="minus">$8.21 (−4.09% DTD)</span></td>
                        <td>🧡</td>
                    </tr>
                    <tr>
                        <td>NVDA</td>
                        <td>NVIDIA Corporation</td>
                        <td>Stock</td>
                        <td>Xetra</td>
                        <td><span class="neutral">$110.40 (0.00% DTD)</span></td>
                        <td>🧡</td>
                    </tr>
                    <tr>
                        <td>NVDA/USD</td>
                        <td>NVIDIA US Dollar</td>
                        <td>Currency</td>
                        <td>Poloniex</td>
                        <td><span class="plus">$0.000429 (+0.43% DTD)</span></td>
                        <td>🧡</td>
                    </tr>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
