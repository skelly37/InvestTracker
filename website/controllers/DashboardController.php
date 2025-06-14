<?php
class DashboardController extends BaseController {
    
    public function index(): void {
        $this->requireAuth();
        
        $userId = Session::getUserId();
        
        try {
            $recentlyViewed = $this->stock->getRecentlyViewed($userId, 4);
            $popularStocks = $this->stock->getPopularStocks();
            $indices = $this->stock->getMarketIndices();
            
            $this->view('dashboard/index', [
                'title' => 'Dashboard - InvestTracker',
                'recentlyViewed' => $recentlyViewed,
                'popularStocks' => array_slice($popularStocks, 0, 4),
                'indices' => $indices
            ]);
            
        } catch (Exception $e) {
            $this->view('dashboard/index', [
                'title' => 'Dashboard - InvestTracker',
                'recentlyViewed' => [],
                'popularStocks' => [],
                'indices' => [],
                'error' => 'Unable to load market data. Please try again later.'
            ]);
        }
    }
    
    public function favorites(): void {
        $this->requireAuth();
        
        $userId = Session::getUserId();
        
        try {
            $favorites = $this->stock->getUserFavorites($userId);
            
            $this->view('dashboard/favorites', [
                'title' => 'Favorites - InvestTracker',
                'favorites' => $favorites
            ]);
            
        } catch (Exception $e) {
            $this->view('dashboard/favorites', [
                'title' => 'Favorites - InvestTracker',
                'favorites' => [],
                'error' => 'Unable to load favorites. Please try again later.'
            ]);
        }
    }
    
    public function addFavorite(): void {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        if (!$this->validateCSRF()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
        }
        
        $input = $this->sanitizeInput($_POST);
        $symbol = $input['symbol'] ?? '';
        
        if (empty($symbol)) {
            $this->json(['success' => false, 'message' => 'Symbol is required'], 400);
        }
        
        $userId = Session::getUserId();
        
        if ($this->stock->addToFavorites($userId, $symbol)) {
            $this->json(['success' => true, 'message' => 'Added to favorites']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to add to favorites'], 500);
        }
    }
    
    public function removeFavorite(): void {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        if (!$this->validateCSRF()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
        }
        
        $input = $this->sanitizeInput($_POST);
        $symbol = $input['symbol'] ?? '';
        
        if (empty($symbol)) {
            $this->json(['success' => false, 'message' => 'Symbol is required'], 400);
        }
        
        $userId = Session::getUserId();
        
        if ($this->stock->removeFromFavorites($userId, $symbol)) {
            $this->json(['success' => true, 'message' => 'Removed from favorites']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to remove from favorites'], 500);
        }
    }
    
    public function settings(): void {
        $this->requireAuth();
        
        $userId = Session::getUserId();
        $user = $this->user->findById($userId);
        
        $chartTimeInterval = $this->user->getChartTimeInterval($userId);

        $flashMessage = Session::get('flash_message');
        if ($flashMessage) {
            Session::remove('flash_message');
        }
        
        $this->view('dashboard/settings', [
            'title' => 'Settings - InvestTracker',
            'user' => $user,
            'chartTimeInterval' => $chartTimeInterval,
            'csrf_token' => $this->generateCSRF(),
            'flashMessage' => $flashMessage
        ]);
    }

    public function updatePreferences(): void {
        $this->requireAuth();
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        if (!$this->validateCSRF()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
        }
        
        $input = $this->sanitizeInput($_POST);
        $chartInterval = $input['chart_time_interval'] ?? '';
        
        $validIntervals = ['1d', '5d', '1mo', '3mo', '1y', '5y', 'max'];
        
        if (!in_array($chartInterval, $validIntervals)) {
            $this->json(['success' => false, 'message' => 'Invalid chart interval'], 400);
        }
        
        $userId = Session::getUserId();
        
        if ($this->user->updateChartTimeInterval($userId, $chartInterval)) {
            $this->json(['success' => true, 'message' => 'Preferences updated successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update preferences'], 500);
        }
    }

    public function clearRecentHistory(): void {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        if (!$this->validateCSRF()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
        }

        $userId = Session::getUserId();

        if ($this->stock->clearRecentHistory($userId)) {
            $this->json(['success' => true, 'message' => 'Recent history cleared successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to clear recent history'], 500);
        }
    }
}