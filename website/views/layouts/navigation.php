<?php
?>
<header class="header">
    <div class="container">
        <div class="header__content">
            <div class="logo">
                <a href="/dashboard">InvestTracker</a>
            </div>
            
            <form class="search-bar" action="/search" method="GET">
                <input type="text" name="q" class="search-bar__input" placeholder="Search stocks..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button type="submit" class="btn">🔍</button>
            </form>
            
            <nav class="nav-buttons">
                <?php if (isAdmin()): ?>
                    <a href="/users" class="btn <?= isCurrentPage('/users') ? 'btn--primary' : '' ?>">
                        <span class="nav-icon">👥</span>
                        <span class="nav-text">Users</span>
                    </a>
                <?php endif; ?>
                <a href="/favorites" class="btn <?= isCurrentPage('/favorites') ? 'btn--primary' : '' ?>">
                    <span class="nav-icon">❤️</span>
                    <span class="nav-text">Favorites</span>
                </a>
                <a href="/settings" class="btn <?= isCurrentPage('/settings') ? 'btn--primary' : '' ?>">
                    <span class="nav-icon">⚙️</span>
                    <span class="nav-text">Settings</span>
                </a>
                <a href="/logout" class="btn btn--danger">
                    <span class="nav-icon">🚪</span>
                    <span class="nav-text">Logout</span>
                </a>
            </nav>
        </div>
    </div>
</header>