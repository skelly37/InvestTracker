<?php
class StockController extends BaseController {
    
    public function search(): void {
        $this->requireAuth();
        
        $query = $_GET['q'] ?? '';
        $query = trim($query);
        
        $results = [];
        $totalResults = 0;
        
        if (!empty($query)) {
            try {
                $results = $this->stock->searchStocks($query);
                $totalResults = count($results);
                
                // Check which results are already in favorites
                $userId = Session::getUserId();
                foreach ($results as &$result) {
                    $result['isFavorite'] = $this->stock->isFavorite($userId, $result['symbol']);
                }
                
            } catch (Exception $e) {
                error_log("Search error: " . $e->getMessage());
                $error = 'Search temporarily unavailable. Please try again later.';
            }
        }
        
        $this->view('stock/search', [
            'title' => 'Search Results - InvestTracker',
            'query' => $query,
            'results' => $results,
            'totalResults' => $totalResults,
            'error' => $error ?? null
        ]);
    }
    
    public function detail(): void {
        $this->requireAuth();
        
        $symbol = $_GET['symbol'] ?? '';
        
        if (empty($symbol)) {
            $this->redirect('/dashboard');
        }
        
        $userId = Session::getUserId();
        
        // Get user's chart time interval preference
        $chartTimeInterval = $this->user->getChartTimeInterval($userId);
        
        // Add to recently viewed
        $this->stock->addToRecentlyViewed($userId, $symbol);
        
        // Check if in favorites
        $isFavorite = $this->stock->isFavorite($userId, $symbol);
        
        $this->view('stock/detail', [
            'title' => $symbol . ' - Stock Detail - InvestTracker',
            'symbol' => $symbol,
            'isFavorite' => $isFavorite,
            'chartTimeInterval' => $chartTimeInterval,
            'csrf_token' => $this->generateCSRF(),
            'flashMessage' => Session::getFlash('message')
        ]);
    }
    
    public function getHistoricalData(): void {
        $this->requireAuth();
        
        $symbol = $_GET['symbol'] ?? '';
        $period = $_GET['interval'] ?? '1d';
        
        if (empty($symbol)) {
            $this->json(['success' => false, 'message' => 'Symbol is required'], 400);
        }
        
        $validPeriods = ['1d', '5d', '1mo', '3mo', '6mo', '1y', '2y', '5y', '10y', 'ytd', 'max'];
        if (!in_array($period, $validPeriods)) {
            $this->json(['success' => false, 'message' => 'Invalid period'], 400);
        }
        
        try {
            $data = $this->stock->getHistoricalData($symbol, $period);
            $this->json(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            error_log("Historical data error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to fetch historical data'], 500);
        }
    }
    
    public function autocomplete(): void {
        $this->requireAuth();
        
        $query = $_GET['q'] ?? '';
        $query = trim($query);
        
        if (strlen($query) < 2) {
            $this->json(['suggestions' => []]);
        }
        
        try {
            $results = $this->stock->searchStocks($query);
            
            // Format for autocomplete
            $suggestions = array_map(function($item) {
                return [
                    'symbol' => $item['symbol'] ?? '',
                    'name' => $item['name'] ?? '',
                    'type' => $item['type'] ?? '',
                    'exchange' => $item['exchange'] ?? '',
                    'label' => ($item['symbol'] ?? '') . ' - ' . ($item['name'] ?? '')
                ];
            }, array_slice($results, 0, 10));
            
            $this->json(['suggestions' => $suggestions]);
            
        } catch (Exception $e) {
            error_log("Autocomplete error: " . $e->getMessage());
            $this->json(['suggestions' => []]);
        }
    }
    
    public function quote(): void {
        $this->requireAuth();
        
        $symbol = $_GET['symbol'] ?? $_GET['q'] ?? '';
        
        if (empty($symbol)) {
            $this->json(['success' => false, 'message' => 'Symbol is required'], 400);
        }
        
        try {
            // ZMIANA: Używamy cache'owanej metody getQuote() zamiast getQuoteFromAPI()
            $data = $this->stock->getQuote($symbol);
            
            if ($data) {
                $this->json($data); // Return cached or fresh data
            } else {
                $this->json(['success' => false, 'message' => 'Stock not found'], 404);
            }
            
        } catch (Exception $e) {
            error_log("Quote error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to fetch quote'], 500);
        }
    }
}