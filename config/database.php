<?php
/**
 * Database Configuration for HCPVision
 * PDO connection settings for PostgreSQL database
 */

// Database configuration from environment variables
define('DB_HOST', $_ENV['PGHOST'] ?? (getenv('PGHOST') !== false ? getenv('PGHOST') : 'localhost'));
define('DB_NAME', $_ENV['PGDATABASE'] ?? (getenv('PGDATABASE') !== false ? getenv('PGDATABASE') : 'ensi_men_stage'));
define('DB_USER', $_ENV['PGUSER'] ?? (getenv('PGUSER') !== false ? getenv('PGUSER') : 'postgres'));
define('DB_PASS', $_ENV['PGPASSWORD'] ?? (getenv('PGPASSWORD') !== false ? getenv('PGPASSWORD') : ''));
define('DB_PORT', $_ENV['PGPORT'] ?? (getenv('PGPORT') !== false ? getenv('PGPORT') : '5432'));

// PDO options for better security and error handling
$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

try {
    // Create PDO connection for PostgreSQL
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdoOptions);
    
    // Store connection globally for models
    $GLOBALS['pdo'] = $pdo;
    
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database connection failed: " . $e->getMessage());
    die("Erreur de connexion à la base de données. Veuillez contacter l'administrateur.");
}

/**
 * Get global PDO instance
 */
function getDB() {
    return $GLOBALS['pdo'];
}
?>
