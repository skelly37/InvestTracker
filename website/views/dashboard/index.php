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
            
            <div class="dashboard-columns">
                <!-- Recently Viewed -->
                <div class="dashboard-column">
                    <div class="dashboard-column__title">Recently Viewed</div>
                    <div class="divider"></div>
                    
                    <?php if (!empty($recentlyViewed)): ?>
                        <?php foreach ($recentlyViewed as $item): ?>
                            <div class="stock-entry">
                                <div class="stock__name">
                                    <a href="/stock?symbol=<?= urlencode($item['symbol']) ?>">
                                        <?= htmlspecialchars($item['data']['name'] ?? $item['symbol']) ?>
                                    </a>
                                </div>
                                <div class="stock__price">
                                    <?= formatPrice($item['data']['price'] ?? null) ?>
                                </div>
                                <div class="stock__change">
                                    <?= formatChange($item['data']['change'] ?? null, $item['data']['change_percent'] ?? null) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center">No recently viewed stocks</p>
                    <?php endif; ?>
                </div>
                
                <!-- Popular Stocks -->
                <div class="dashboard-column">
                    <div class="dashboard-column__title">Popular Stocks</div>
                    <div class="divider"></div>
                    
                    <?php if (!empty($popularStocks)): ?>
                        <?php foreach ($popularStocks as $stock): ?>
                            <div class="stock-entry">
                                <div class="stock__name">
                                    <a href="/stock?symbol=<?= urlencode($stock['symbol']) ?>">
                                        <?= htmlspecialchars($stock['data']['name'] ?? $stock['symbol']) ?>
                                    </a>
                                </div>
                                <div class="stock__price">
                                    <?= formatPrice($stock['data']['price'] ?? null) ?>
                                </div>
                                <div class="stock__change">
                                    <?= formatChange($stock['data']['change'] ?? null, $stock['data']['change_percent'] ?? null) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center">Unable to load popular stocks</p>
                    <?php endif; ?>
                </div>
                
                <!-- Biggest Changes -->
                <div class="dashboard-column">
                    <div class="dashboard-column__title">Biggest Changes</div>
                    <div class="divider"></div>
                    
                    <?php if (!empty($biggestChanges)): ?>
                        <?php foreach ($biggestChanges as $stock): ?>
                            <div class="stock-entry">
                                <div class="stock__name">
                                    <a href="/stock?symbol=<?= urlencode($stock['symbol']) ?>">
                                        <?= htmlspecialchars($stock['data']['name'] ?? $stock['symbol']) ?>
                                    </a>
                                </div>
                                <div class="stock__price">
                                    <?= formatPrice($stock['data']['price'] ?? null) ?>
                                </div>
                                <div class="stock__change">
                                    <?= formatChange($stock['data']['change'] ?? null, $stock['data']['change_percent'] ?? null) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center">Unable to load market changes</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Market Indices -->
            <?php if (!empty($indices)): ?>
                <div class="card mt-3">
                    <div class="card__header">
                        <h2 class="card__title">Market Indices</h2>
                    </div>
                    <div class="card__body">
                        <div class="grid grid--cols-auto">
                            <?php foreach ($indices as $index): ?>
                                <div class="stock-entry">
                                    <div class="stock__name">
                                        <?= htmlspecialchars($index['data']['name'] ?? $index['symbol']) ?>
                                    </div>
                                    <div class="stock__price">
                                        <?= formatPrice($index['data']['price'] ?? null) ?>
                                    </div>
                                    <div class="stock__change">
                                        <?= formatChange($index['data']['change'] ?? null, $index['data']['change_percent'] ?? null) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>