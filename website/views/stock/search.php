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
                                                <?php if ($result['isFavorite']): ?>
                                                    <button class="btn btn--small btn--primary remove-favorite-btn"
                                                            data-symbol="<?= htmlspecialchars($result['symbol']) ?>"
                                                            data-csrf="<?= htmlspecialchars(csrf_token()) ?>">
                                                        ✅ Added
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn--small btn--secondary add-favorite-btn"
                                                            data-symbol="<?= htmlspecialchars($result['symbol']) ?>"
                                                            data-csrf="<?= htmlspecialchars(csrf_token()) ?>">
                                                        ❤️ Add
                                                    </button>
                                                <?php endif; ?>
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
    const removeButtons = document.querySelectorAll('.remove-favorite-btn');
    
    // Add to favorites
    addButtons.forEach(button => {
        button.addEventListener('click', handleAddFavorite);
    });
    
    // Remove from favorites
    removeButtons.forEach(button => {
        button.addEventListener('click', handleRemoveFavorite);
    });
    
    function handleAddFavorite(e) {
        const button = e.target;
        const symbol = button.dataset.symbol;
        const csrf = button.dataset.csrf;
        
        console.log('Adding to favorites:', symbol);
        
        // POPRAWKA: Użyj naszego endpointu /stock/quote zamiast bezpośredniego wywołania
        fetch(`/stock/quote?symbol=${encodeURIComponent(symbol)}`)
            .then(response => {
                console.log('Quote response status:', response.status);
                return response.json();
            })
            .then(stockData => {
                console.log('Quote data received:', stockData);
                
                // Potem dodaj do ulubionych
                return fetch('/dashboard/add-favorite', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `symbol=${encodeURIComponent(symbol)}&csrf_token=${encodeURIComponent(csrf)}`
                });
            })
            .then(response => {
                console.log('Add favorite response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Add favorite response:', data);
                if (data.success) {
                    switchToRemoveButton(button);
                } else {
                    alert(data.message || 'Failed to add to favorites');
                }
            })
            .catch(error => {
                console.error('Error adding to favorites:', error);
                alert('Failed to add to favorites');
            });
    }
    
    function handleRemoveFavorite(e) {
        const button = e.target;
        const symbol = button.dataset.symbol;
        const csrf = button.dataset.csrf;
        
        console.log('Removing from favorites:', symbol);
        
        fetch('/dashboard/remove-favorite', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `symbol=${encodeURIComponent(symbol)}&csrf_token=${encodeURIComponent(csrf)}`
        })
        .then(response => {
            console.log('Remove favorite response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Remove favorite response:', data);
            if (data.success) {
                switchToAddButton(button);
            } else {
                alert(data.message || 'Failed to remove from favorites');
            }
        })
        .catch(error => {
            console.error('Error removing from favorites:', error);
            alert('Failed to remove from favorites');
        });
    }
    
    function switchToRemoveButton(button) {
        // Usuń poprzedni event listener
        button.removeEventListener('click', handleAddFavorite);
        
        // Zmień wygląd i tekst
        button.textContent = '✅ Added';
        button.classList.remove('btn--secondary', 'add-favorite-btn');
        button.classList.add('btn--primary', 'remove-favorite-btn');
        
        // Dodaj nowy event listener
        button.addEventListener('click', handleRemoveFavorite);
    }
    
    function switchToAddButton(button) {
        // Usuń poprzedni event listener
        button.removeEventListener('click', handleRemoveFavorite);
        
        // Zmień wygląd i tekst
        button.textContent = '❤️ Add';
        button.classList.remove('btn--primary', 'remove-favorite-btn');
        button.classList.add('btn--secondary', 'add-favorite-btn');
        
        // Dodaj nowy event listener
        button.addEventListener('click', handleAddFavorite);
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>