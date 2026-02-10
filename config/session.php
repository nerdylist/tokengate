<?php

/**
 * Session Configuration
 *
 * Configures PHP session to persist for 1 year (31536000 seconds)
 * to keep users logged in long-term without re-authentication.
 */

// Set session cookie parameters for 1 year persistence
session_set_cookie_params([
    'lifetime' => 31536000,  // 1 year in seconds
    'path' => '/',
    'domain' => '',          // Current domain
    'secure' => false,       // Set to true when using HTTPS
    'httponly' => true,      // Prevent JavaScript access (XSS protection)
    'samesite' => 'Lax'      // CSRF protection
]);

// Configure session garbage collection
ini_set('session.gc_maxlifetime', 31536000); // 1 year in seconds
ini_set('session.cookie_lifetime', 31536000); // 1 year in seconds
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
