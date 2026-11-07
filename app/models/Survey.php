<?php
namespace app\models;

/**
 * Survey Model - Handles survey data operations
 */
class Survey extends Database {
    
    /**
     * Get all surveys from vue_enquetes_finales
     */
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT * FROM vue_enquetes_finales ORDER BY dd DESC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            return $this->select($sql, [$limit, $offset]);
        }
        
        return $this->select($sql);
    }
    
    /**
     * Get survey by ID
     */
    public function getById($id) {
        return $this->selectOne(
            "SELECT * FROM vue_enquetes_finales WHERE `c99-id` = ?",
            [$id]
        );
    }
    
    /**
     * Get surveys count
     */
    public function getTotalCount() {
        $result = $this->selectOne("SELECT COUNT(*) as count FROM vue_enquetes_finales");
        return $result['count'];
    }
    
    /**
     * Get surveys by enqueteur
     */
    public function getByEnqueteur($ninv) {
        return $this->select(
            "SELECT * FROM vue_enquetes_finales WHERE ninv = ? ORDER BY dd DESC",
            [$ninv]
        );
    }
    
    /**
     * Get surveys by region
     */
    public function getByRegion($region) {
        return $this->select(
            "SELECT * FROM vue_enquetes_finales WHERE reg = ? ORDER BY dd DESC",
            [$region]
        );
    }
    
    /**
     * Get surveys by status
     */
    public function getByStatus($status) {
        return $this->select(
            "SELECT * FROM vue_enquetes_finales WHERE statut_libelle = ? ORDER BY dd DESC",
            [$status]
        );
    }
    
    /**
     * Search surveys
     */
    public function search($query) {
        $searchTerm = '%' . $this->escapeLike($query) . '%';
        
        return $this->select(
            "SELECT * FROM vue_enquetes_finales 
             WHERE ninv LIKE ? 
                OR enq_nom LIKE ? 
                OR case_key LIKE ?
             ORDER BY dd DESC",
            [$searchTerm, $searchTerm, $searchTerm]
        );
    }
    
    /**
     * Get surveys with filters
     */
    public function getFiltered($filters = []) {
        $sql = "SELECT * FROM vue_enquetes_finales WHERE 1=1";
        $params = [];
        
        if (!empty($filters['enqueteur'])) {
            $sql .= " AND ninv = ?";
            $params[] = $filters['enqueteur'];
        }
        
        if (!empty($filters['region'])) {
            $sql .= " AND reg = ?";
            $params[] = $filters['region'];
        }
        
        if (!empty($filters['statut'])) {
            $sql .= " AND statut_libelle = ?";
            $params[] = $filters['statut'];
        }
        
        if (!empty($filters['date_debut'])) {
            $sql .= " AND DATE(dd) >= ?";
            $params[] = $filters['date_debut'];
        }
        
        if (!empty($filters['date_fin'])) {
            $sql .= " AND DATE(dd) <= ?";
            $params[] = $filters['date_fin'];
        }
        
        $sql .= " ORDER BY dd DESC";
        
        return $this->select($sql, $params);
    }
    
    /**
     * Get survey statistics
     */
    public function getStatistics() {
        return $this->selectOne("SELECT * FROM vue_resume_global");
    }
    
    /**
     * Get daily collection statistics
     */
    public function getDailyStats() {
        return $this->select("SELECT * FROM vue_collecte_par_jour ORDER BY jour");
    }
    
    /**
     * Get status distribution
     */
    public function getStatusDistribution() {
        return $this->select("SELECT * FROM vue_statut_detaille");
    }
    
    /**
     * Update survey comment (for admin)
     */
    public function updateComment($id, $comment) {
        return $this->execute(
            "UPDATE c99 SET commentaire = ? WHERE `c99-id` = ?",
            [$comment, $id]
        );
    }
}
?>
