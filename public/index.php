<?php
/**
 * HCPVision - Survey Data Visualization System
 * Entry point for the application
 */

// Start session for authentication
session_start();

// Define application root path
define('ROOT_PATH', dirname(__DIR__));

// Autoload classes
spl_autoload_register(function ($class) {
    $file = ROOT_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Include configuration and routes
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/routes.php';

// Get the requested URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/public', '', $uri);
$uri = trim($uri, '/');

// Debug logging
error_log("Requested URI: " . $_SERVER['REQUEST_URI']);
error_log("Processed URI: " . $uri);

// Handle routing
handleRoute($uri);
?>
