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
                        <?php foreach ($popularStocks as $symbol): ?>
                            <div class="stock-entry">
                                <div class="stock__name">
                                    <a href="/stock?symbol=<?= urlencode($symbol) ?>">
                                        <?= htmlspecialchars($symbol) ?>
                                    </a>
                                </div>
                                <div class="stock__price">
                                    N/A
                                </div>
                                <div class="stock__change">
                                    N/A
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center">Unable to load popular stocks</p>
                    <?php endif; ?>
                </div>
                
                <!-- Market Indices -->
                <div class="dashboard-column">
                    <div class="dashboard-column__title">Market Indices</div>
                    <div class="divider"></div>
                    
                    <?php if (!empty($indices)): ?>
                        <?php foreach ($indices as $symbol): ?>
                            <div class="stock-entry">
                                <div class="stock__name">
                                    <?= htmlspecialchars($symbol) ?>
                                </div>
                                <div class="stock__price">
                                    N/A
                                </div>
                                <div class="stock__change">
                                    N/A
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center">Unable to load market indices</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>