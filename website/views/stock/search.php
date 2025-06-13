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
                <?php if (!empty($query)): ?>
                    <h1 class="search-results__title">Search Results for "<?= htmlspecialchars($query) ?>"</h1>
                    
                    <?php if ($totalResults > 0): ?>
                        <div class="search-results__count">
                            Found <?= $totalResults ?> result<?= $totalResults !== 1 ? 's' : '' ?>
                        </div>
                        
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Symbol</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Exchange</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $result): ?>
                                    <tr>
                                        <td>
                                            <a href="/stock?symbol=<?= urlencode($result['symbol']) ?>" class="stock__name">
                                                <?= htmlspecialchars($result['symbol']) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($result['name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($result['type'] ?? 'Stock') ?></td>
                                        <td><?= htmlspecialchars($result['exchange'] ?? 'N/A') ?></td>
                                        <td>
                                            <div class="flex flex--gap">
                                                <a href="/stock?symbol=<?= urlencode($result['symbol']) ?>" 
                                                   class="btn btn--small btn--primary">View</a>
                                                <button class="btn btn--small btn--secondary add-favorite-btn" 
                                                        data-symbol="<?= htmlspecialchars($result['symbol']) ?>"
                                                        data-csrf="<?= htmlspecialchars(csrf_token()) ?>">
                                                    ❤️ Add
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="card text-center">
                            <div class="card__body">
                                <h3>No results found</h3>
                                <p>Try searching for a different stock symbol or company name.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <h1 class="search-results__title">Search Stocks</h1>
                    <div class="card text-center">
                        <div class="card__body">
                            <h3>Enter a search term</h3>
                            <p>Use the search bar above to find stocks, ETFs, and other financial instruments.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addButtons = document.querySelectorAll('.add-favorite-btn');
    
    addButtons.forEach(button => {
        button.addEventListener('click', function() {
            const symbol = this.dataset.symbol;
            const csrf = this.dataset.csrf;
            
            fetch('/dashboard/add-favorite', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `symbol=${encodeURIComponent(symbol)}&csrf_token=${encodeURIComponent(csrf)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.textContent = '✅ Added';
                    this.disabled = true;
                    this.classList.remove('btn--secondary');
                    this.classList.add('btn--primary');
                } else {
                    alert(data.message || 'Failed to add to favorites');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to add to favorites');
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>