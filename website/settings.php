<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InvestTracker - Settings</title>
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
                <button class="active">Settings</button>
            </div>
        </header>

        <main class="settings-form">
            <form method="post" action="#">
                <label>Currency
                    <select>
                        <option>USD – U.S. Dollar</option>
                        <option>EUR – Euro</option>
                    </select>
                </label>

                <label>Default chart time interval
                    <select>
                        <option>1Y – 1 Year</option>
                        <option>6M – 6 Months</option>
                    </select>
                </label>

                <label>Default time interval for price difference
                    <select>
                        <option>DTD – Day-To-Day</option>
                        <option>MTD – Month-To-Date</option>
                    </select>
                </label>

                <label>Color mode
                    <select>
                        <option>Light</option>
                        <option>Dark</option>
                    </select>
                </label>

                <label>Change my e-mail
                    <input type="email" placeholder="new e-mail address">
                </label>

                <label>Change my password
                    <input type="password" placeholder="new password">
                </label>

                <button type="button" class="apply-button">Apply</button>

                <div class="danger-zone">
                    <button type="button" class="danger">Reset to defaults</button>
                    <button type="button" class="danger">Delete all favorites</button>
                    <button type="button" class="danger">Delete my account</button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
