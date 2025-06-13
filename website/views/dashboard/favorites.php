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
                                <tr>
                                    <td>
                                        <a href="/stock?symbol=<?= urlencode($favorite['symbol']) ?>" class="stock__name">
                                            <?= htmlspecialchars($favorite['symbol']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($favorite['data']['name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($favorite['data']['type'] ?? 'Stock') ?></td>
                                    <td><?= htmlspecialchars($favorite['data']['exchange'] ?? 'N/A') ?></td>
                                    <td>
                                        <div class="stock__price">
                                            <?= formatPrice($favorite['data']['price'] ?? null) ?>
                                        </div>
                                        <div class="stock__change">
                                            <?= formatChange($favorite['data']['change'] ?? null, $favorite['data']['change_percent'] ?? null) ?>
                                        </div>
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
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>