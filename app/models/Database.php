<?php
namespace app\models;

use PDO;
use PDOException;

/**
 * Database Model - Base class for database operations
 */
class Database {
    protected $pdo;
    
    public function __construct() {
        $this->pdo = getDB();
    }
    
    /**
     * Execute a SELECT query and return all results
     */
    protected function select($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database select error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute a SELECT query and return single row
     */
    protected function selectOne($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Database selectOne error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute INSERT, UPDATE, DELETE queries
     */
    protected function execute($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Database execute error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get last inserted ID
     */
    protected function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    protected function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    protected function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    protected function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * Escape string for LIKE queries
     */
    protected function escapeLike($string) {
        return str_replace(['%', '_'], ['\%', '\_'], $string);
    }
}
?>
