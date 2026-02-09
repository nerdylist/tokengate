<?php
/**
 * Configuration System
 * Loads environment variables and provides helper functions
 */

// Load .env file
function loadEnv($path = __DIR__ . '/.env') {
    if (!file_exists($path)) {
        die('Error: .env file not found. Please create one based on .env.example');
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse key=value pairs
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            // Set as environment variable and constant
            putenv("$key=$value");
            if (!defined($key)) {
                define($key, $value);
            }
        }
    }
}

// Load the environment
loadEnv();

// Define derived constants
if (!defined('APP_NAME')) {
    define('APP_NAME', 'rentahuman.ai');
}

if (!defined('APP_ENV')) {
    define('APP_ENV', 'dev');
}

define('IS_DEV', APP_ENV === 'dev');
define('IS_PROD', APP_ENV === 'prod');

/**
 * Generate URL based on environment
 * 
 * @param string $route The route name (e.g., 'browse', 'bounty')
 * @param array $params Optional parameters
 * @return string The generated URL
 */
function url($route, $params = []) {
    if (IS_DEV) {
        // Development mode: use query strings
        switch ($route) {
            case 'browse':
                return 'browse.php';
            case 'bounties':
            case 'index':
                return 'index.php';
            case 'hire':
                return 'hire.php';
            case 'connect':
                return 'connect.php';
            case 'bounty':
                $id = isset($params['id']) ? $params['id'] : '';
                return 'detail.php?id=' . urlencode($id);
            default:
                // Generic handling
                if (!empty($params)) {
                    $query = http_build_query($params);
                    return $route . '.php?' . $query;
                }
                return $route . '.php';
        }
    } else {
        // Production mode: clean URLs
        switch ($route) {
            case 'browse':
                return '/browse';
            case 'bounties':
            case 'index':
                return '/bounties';
            case 'hire':
                return '/hire';
            case 'connect':
                return '/connect';
            case 'bounty':
                $id = isset($params['id']) ? $params['id'] : '';
                return '/bounty/' . urlencode($id);
            default:
                // Generic handling
                $url = '/' . $route;
                if (!empty($params)) {
                    $query = http_build_query($params);
                    $url .= '?' . $query;
                }
                return $url;
        }
    }
}

/**
 * Generate asset URL for CSS/JS files
 * 
 * @param string $path Path to asset (e.g., 'styles.css', 'app.js')
 * @return string The asset URL
 */
function asset($path) {
    // Remove leading slash if present
    $path = ltrim($path, '/');
    
    if (IS_DEV) {
        // In dev mode, use relative paths
        return $path;
    } else {
        // In production, could add CDN or versioning here
        return '/' . $path;
    }
}
