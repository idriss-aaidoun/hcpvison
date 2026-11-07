<?php
/**
 * Base model class with common database operations
 */

abstract class BaseModel {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Execute a SELECT query and return results
     */
    protected function select($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database error in " . get_class($this) . ": " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des données");
        }
    }
    
    /**
     * Execute a SELECT query and return single row
     */
    protected function selectOne($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Database error in " . get_class($this) . ": " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des données");
        }
    }
    
    /**
     * Execute an INSERT, UPDATE, or DELETE query
     */
    protected function execute($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Database error in " . get_class($this) . ": " . $e->getMessage());
            throw new Exception("Erreur lors de l'exécution de la requête");
        }
    }
    
    /**
     * Get last inserted ID
     */
    protected function lastInsertId() {
        return $this->db->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    protected function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    protected function commit() {
        return $this->db->commit();
    }
    
    /**
     * Rollback transaction
     */
    protected function rollback() {
        return $this->db->rollback();
    }
}
