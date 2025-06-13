<?php

/**
 * Global utility functions for the InvestTracker application
 */

/**
 * Sanitize string for HTML output
 */
function sanitize_output(string $string): string {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a secure random token
 */
function generate_token(int $length = 32): string {
    return bin2hex(random_bytes($length));
}

/**
 * Format currency value
 */
function format_currency(?float $amount, string $currency = 'USD', int $decimals = 2): string {
    if ($amount === null) {
        return 'N/A';
    }
    
    $symbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'JPY' => '¥'
    ];
    
    $symbol = $symbols[$currency] ?? $currency . ' ';
    return $symbol . number_format($amount, $decimals);
}

/**
 * Format percentage change with color coding
 */
function format_percentage_change(?float $change, bool $includeHtml = true): string {
    if ($change === null) {
        return 'N/A';
    }
    
    $sign = $change >= 0 ? '+' : '';
    $formatted = $sign . number_format($change, 2) . '%';
    
    if (!$includeHtml) {
        return $formatted;
    }
    
    $class = 'text--neutral';
    if ($change > 0) {
        $class = 'text--success';
    } elseif ($change < 0) {
        $class = 'text--danger';
    }
    
    return "<span class=\"{$class}\">{$formatted}</span>";
}

/**
 * Format large numbers (millions, billions, etc.)
 */
function format_large_number(?float $number): string {
    if ($number === null) {
        return 'N/A';
    }
    
    $abs = abs($number);
    
    if ($abs >= 1000000000000) {
        return number_format($number / 1000000000000, 2) . 'T';
    } elseif ($abs >= 1000000000) {
        return number_format($number / 1000000000, 2) . 'B';
    } elseif ($abs >= 1000000) {
        return number_format($number / 1000000, 2) . 'M';
    } elseif ($abs >= 1000) {
        return number_format($number / 1000, 1) . 'K';
    }
    
    return number_format($number);
}

/**
 * Get time ago string
 */
function time_ago(?string $datetime): string {
    if (!$datetime) {
        return 'Never';
    }
    
    $time = time() - strtotime($datetime);
    
    if ($time < 60) {
        return 'Just now';
    } elseif ($time < 3600) {
        $minutes = floor($time / 60);
        return $minutes . ' minute' . ($minutes != 1 ? 's' : '') . ' ago';
    } elseif ($time < 86400) {
        $hours = floor($time / 3600);
        return $hours . ' hour' . ($hours != 1 ? 's' : '') . ' ago';
    } elseif ($time < 2592000) {
        $days = floor($time / 86400);
        return $days . ' day' . ($days != 1 ? 's' : '') . ' ago';
    } elseif ($time < 31104000) {
        $months = floor($time / 2592000);
        return $months . ' month' . ($months != 1 ? 's' : '') . ' ago';
    } else {
        $years = floor($time / 31104000);
        return $years . ' year' . ($years != 1 ? 's' : '') . ' ago';
    }
}

/**
 * Debug helper function
 */
function debug_log($data, string $label = 'DEBUG'): void {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        error_log($label . ': ' . (is_array($data) || is_object($data) ? json_encode($data) : $data));
    }
}

/**
 * Generate breadcrumb navigation
 */
function generate_breadcrumbs(array $breadcrumbs): string {
    if (empty($breadcrumbs)) {
        return '';
    }
    
    $html = '<nav class="breadcrumbs"><ol class="breadcrumb-list">';
    
    $total = count($breadcrumbs);
    foreach ($breadcrumbs as $index => $crumb) {
        $isLast = ($index === $total - 1);
        
        $html .= '<li class="breadcrumb-item' . ($isLast ? ' breadcrumb-item--active' : '') . '">';
        
        if (!$isLast && isset($crumb['url'])) {
            $html .= '<a href="' . sanitize_output($crumb['url']) . '">';
        }
        
        $html .= sanitize_output($crumb['title']);
        
        if (!$isLast && isset($crumb['url'])) {
            $html .= '</a>';
        }
        
        $html .= '</li>';
        
        if (!$isLast) {
            $html .= '<li class="breadcrumb-separator"> › </li>';
        }
    }
    
    $html .= '</ol></nav>';
    return $html;
}

/**
 * Validate stock symbol format
 */
function is_valid_stock_symbol(string $symbol): bool {
    // Allow letters, numbers, dots, hyphens, and forward slashes
    return preg_match('/^[A-Z0-9.\-\/\^]+$/i', $symbol) && strlen($symbol) <= 20;
}

/**
 * Get market status based on current time
 */
function get_market_status(): array {
    $now = new DateTime('now', new DateTimeZone('America/New_York'));
    $time = $now->format('H:i');
    $dayOfWeek = $now->format('N'); // 1 = Monday, 7 = Sunday
    
    $isWeekend = $dayOfWeek >= 6;
    $isMarketHours = $time >= '09:30' && $time <= '16:00';
    
    if ($isWeekend) {
        return [
            'status' => 'closed',
            'message' => 'Market is closed (weekend)',
            'next_open' => 'Monday 9:30 AM ET'
        ];
    } elseif (!$isMarketHours) {
        if ($time < '09:30') {
            return [
                'status' => 'pre-market',
                'message' => 'Pre-market trading',
                'next_open' => 'Today 9:30 AM ET'
            ];
        } else {
            return [
                'status' => 'after-hours',
                'message' => 'After-hours trading',
                'next_open' => 'Tomorrow 9:30 AM ET'
            ];
        }
    } else {
        return [
            'status' => 'open',
            'message' => 'Market is open',
            'next_close' => 'Today 4:00 PM ET'
        ];
    }
}

/**
 * Generate pagination HTML
 */
function generate_pagination(int $currentPage, int $totalPages, string $baseUrl): string {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav class="pagination">';
    $html .= '<ul class="pagination-list">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<li><a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" class="pagination-link">‹ Previous</a></li>';
    } else {
        $html .= '<li><span class="pagination-link pagination-link--disabled">‹ Previous</span></li>';
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    if ($start > 1) {
        $html .= '<li><a href="' . $baseUrl . '?page=1" class="pagination-link">1</a></li>';
        if ($start > 2) {
            $html .= '<li><span class="pagination-ellipsis">…</span></li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i === $currentPage) {
            $html .= '<li><span class="pagination-link pagination-link--active">' . $i . '</span></li>';
        } else {
            $html .= '<li><a href="' . $baseUrl . '?page=' . $i . '" class="pagination-link">' . $i . '</a></li>';
        }
    }
    
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $html .= '<li><span class="pagination-ellipsis">…</span></li>';
        }
        $html .= '<li><a href="' . $baseUrl . '?page=' . $totalPages . '" class="pagination-link">' . $totalPages . '</a></li>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<li><a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" class="pagination-link">Next ›</a></li>';
    } else {
        $html .= '<li><span class="pagination-link pagination-link--disabled">Next ›</span></li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Rate limiting helper
 */
function check_rate_limit(string $key, int $maxRequests = 60, int $timeWindow = 3600): bool {
    $cacheFile = sys_get_temp_dir() . '/rate_limit_' . md5($key);
    
    $data = [];
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true) ?: [];
    }
    
    $now = time();
    $windowStart = $now - $timeWindow;
    
    // Remove old entries
    $data = array_filter($data, function($timestamp) use ($windowStart) {
        return $timestamp > $windowStart;
    });
    
    // Check if limit exceeded
    if (count($data) >= $maxRequests) {
        return false;
    }
    
    // Add current request
    $data[] = $now;
    file_put_contents($cacheFile, json_encode($data));
    
    return true;
}

/**
 * Simple template rendering
 */
function render_template(string $template, array $variables = []): string {
    extract($variables);
    ob_start();
    include $template;
    return ob_get_clean();
}

/**
 * Get client IP address
 */
function get_client_ip(): string {
    $headers = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'REMOTE_ADDR'
    ];
    
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ips = explode(',', $_SERVER[$header]);
            $ip = trim($ips[0]);
            
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}