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
            $this->redirect('/dashboard', 'Stock symbol is required.');
        }
        
        $symbol = strtoupper(trim($symbol));
        $userId = Session::getUserId();
        
        try {
            // Get stock data
            $stockData = $this->stock->getQuote($symbol);
            
            if (!$stockData) {
                $this->redirect('/dashboard', 'Stock not found or data unavailable.');
            }
            
            // Add to recently viewed
            $this->stock->addToRecentlyViewed($userId, $symbol);
            
            // Check if in favorites
            $isInFavorites = $this->stock->isFavorite($userId, $symbol);
            
            // Get historical data for chart
            $period = $_GET['period'] ?? '1d';
            $validPeriods = ['1d', '5d', '1mo', '3mo', '6mo', '1y', '2y', '5y', '10y', 'ytd', 'max'];
            if (!in_array($period, $validPeriods)) {
                $period = '1d';
            }

            $this->view('stock/detail', [
                'title' => ($stockData['name'] ?? $symbol) . ' - InvestTracker',
                'symbol' => $symbol,
                'stockData' => $stockData,
                'isInFavorites' => $isInFavorites,
                'currentPeriod' => $period,
                'validPeriods' => $validPeriods,
                'csrf_token' => $this->generateCSRF()
            ]);
            
        } catch (Exception $e) {
            error_log("Stock detail error: " . $e->getMessage());
            $this->redirect('/dashboard', 'Unable to load stock data. Please try again later.');
        }
    }
    
    public function getHistoricalData(): void {
        $this->requireAuth();
        
        $symbol = $_GET['symbol'] ?? '';
        $period = $_GET['period'] ?? '1d';
        
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
            // Get data from Yahoo API wrapper
            $data = $this->stock->getQuoteFromAPI($symbol);
            
            if ($data) {
                $this->json($data); // Return raw data from Yahoo API
            } else {
                $this->json(['success' => false, 'message' => 'Stock not found'], 404);
            }
            
        } catch (Exception $e) {
            error_log("Quote error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to fetch quote'], 500);
        }
    }
}