<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InvestTracker - Favorites</title>
    <link rel="stylesheet" href="styles.css">
    <script src="scripts.js" defer></script>
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="logo">InvestTracker</div>
            <div class="search-bar">
                <input type="text" placeholder="Search" value="">
                <button>🔍</button>
            </div>
            <div class="nav-buttons">
                <button>Users</button>
                <button class="active">Favorites</button>
                <button>Settings</button>
            </div>
        </header>

        <main class="search-results-table">
            <table>
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
                    <tr>
                        <td>NVDA</td>
                        <td>NVIDIA Corporation</td>
                        <td>Stock</td>
                        <td>NASDAQ</td>
                        <td><span class="minus">$111.43 (−2.11% DTD)</span></td>
                        <td><button class="trash-button">🗑️</button></td>
                    </tr>
                    <tr>
                        <td>NVDQ</td>
                        <td>T-Rex 2X Inverse NVIDIA Daily Target</td>
                        <td>ETF</td>
                        <td>NYSE</td>
                        <td><span class="plus">$3.59 (+4.06% DTD)</span></td>
                        <td><button class="trash-button">🗑️</button></td>
                    </tr>
                    <tr>
                        <td>NVDX</td>
                        <td>T-Rex 2X Long NVIDIA Daily Target ETF</td>
                        <td>ETF</td>
                        <td>NYSE</td>
                        <td><span class="minus">$8.21 (−4.09% DTD)</span></td>
                        <td><button class="trash-button">🗑️</button></td>
                    </tr>
                    <tr>
                        <td>NVDA/USD</td>
                        <td>NVIDIA US Dollar</td>
                        <td>Currency</td>
                        <td>Poloniex</td>
                        <td><span class="plus">$0.000429 (+0.43% DTD)</span></td>
                        <td><button class="trash-button">🗑️</button></td>
                    </tr>
                    <tr>
                        <td>NVDC34</td>
                        <td>Nvidia Corp</td>
                        <td>Stock</td>
                        <td>B3</td>
                        <td><span class="plus">$15.43 (+1.11% DTD)</span></td>
                        <td><button class="trash-button">🗑️</button></td>
                    </tr>
                    <tr>
                        <td>NVDA</td>
                        <td>NVIDIA Corporation</td>
                        <td>Stock</td>
                        <td>Warsaw</td>
                        <td><span class="plus">$120.76 (+0.32% DTD)</span></td>
                        <td><button class="trash-button">🗑️</button></td>
                    </tr>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
