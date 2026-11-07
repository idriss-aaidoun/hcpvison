<?php
namespace app\models;

/**
 * Enqueteur Model - Handles surveyor data and statistics
 */
class Enqueteur extends Database {
    
    /**
     * Get all enqueteurs with their statistics
     */
    public function getAll() {
        return $this->select(
            "SELECT e.*, 
                    COALESCE(r.nbr_enquetes, 0) as nbr_enquetes,
                    COALESCE(t.taux_completion, 0) as taux_completion,
                    COALESCE(d.duree_moyenne, 0) as duree_moyenne
             FROM enqueteurs e
             LEFT JOIN vue_rendement_enqueteur r ON e.ninv = r.ninv
             LEFT JOIN vue_taux_completion_par_enqueteur t ON e.ninv = t.ninv
             LEFT JOIN vue_duree_par_enqueteur d ON e.ninv = d.ninv
             ORDER BY r.nbr_enquetes DESC"
        );
    }
    
    /**
     * Get enqueteur by ninv
     */
    public function getByNinv($ninv) {
        return $this->selectOne(
            "SELECT e.*, 
                    COALESCE(r.nbr_enquetes, 0) as nbr_enquetes,
                    COALESCE(t.taux_completion, 0) as taux_completion,
                    COALESCE(d.duree_moyenne, 0) as duree_moyenne,
                    COALESCE(d.duree_min, 0) as duree_min,
                    COALESCE(d.duree_max, 0) as duree_max,
                    COALESCE(s.taux_suppression, 0) as taux_suppression
             FROM enqueteurs e
             LEFT JOIN vue_rendement_enqueteur r ON e.ninv = r.ninv
             LEFT JOIN vue_taux_completion_par_enqueteur t ON e.ninv = t.ninv
             LEFT JOIN vue_duree_par_enqueteur d ON e.ninv = d.ninv
             LEFT JOIN vue_cas_supprime_enqueteur s ON e.ninv = s.ninv
             WHERE e.ninv = ?",
            [$ninv]
        );
    }
    
    /**
     * Get performance statistics for all enqueteurs
     */
    public function getPerformanceStats() {
        return $this->select("SELECT * FROM vue_rendement_enqueteur ORDER BY nbr_enquetes DESC");
    }
    
    /**
     * Get completion rate statistics
     */
    public function getCompletionStats() {
        return $this->select("SELECT * FROM vue_taux_completion_par_enqueteur ORDER BY taux_completion DESC");
    }
    
    /**
     * Get duration statistics
     */
    public function getDurationStats() {
        return $this->select("SELECT * FROM vue_duree_par_enqueteur ORDER BY duree_moyenne DESC");
    }
    
    /**
     * Get detailed status breakdown for an enqueteur
     */
    public function getStatusBreakdown($ninv) {
        return $this->select(
            "SELECT * FROM vue_detail_statuts_par_enqueteur 
             WHERE ninv = ? 
             ORDER BY nb DESC",
            [$ninv]
        );
    }
    
    /**
     * Get enqueteur's surveys by date
     */
    public function getSurveysByDate($ninv) {
        return $this->select(
            "SELECT DATE(dd) as date, COUNT(*) as count
             FROM vue_enquetes_finales 
             WHERE ninv = ?
             GROUP BY DATE(dd)
             ORDER BY date",
            [$ninv]
        );
    }
    
    /**
     * Get enqueteur's regional distribution
     */
    public function getRegionalDistribution($ninv) {
        return $this->select(
            "SELECT reg as region, COUNT(*) as count
             FROM vue_enquetes_finales 
             WHERE ninv = ?
             GROUP BY reg
             ORDER BY count DESC",
            [$ninv]
        );
    }
    
    /**
     * Update enqueteur comment (admin only)
     */
    public function updateComment($ninv, $comment) {
        return $this->execute(
            "UPDATE enqueteurs SET commentaire = ? WHERE ninv = ?",
            [$comment, $ninv]
        );
    }
    
    /**
     * Update enqueteur status
     */
    public function updateStatus($ninv, $status) {
        return $this->execute(
            "UPDATE enqueteurs SET statut = ? WHERE ninv = ?",
            [$status, $ninv]
        );
    }
    
    /**
     * Get enqueteurs by region
     */
    public function getByRegion($region) {
        return $this->select(
            "SELECT DISTINCT e.*, r.nbr_enquetes, r.taux_completion
             FROM enqueteurs e
             JOIN vue_enqueteurs_region r ON e.ninv = r.ninv
             WHERE r.region = ?
             ORDER BY r.taux_completion DESC",
            [$region]
        );
    }
    
    /**
     * Get performance comparison data
     */
    public function getPerformanceComparison() {
        return $this->select(
            "SELECT 
                ninv,
                enq_nom,
                nbr_enquetes,
                taux_completion,
                duree_moyenne,
                RANK() OVER (ORDER BY nbr_enquetes DESC) as rank_enquetes,
                RANK() OVER (ORDER BY taux_completion DESC) as rank_completion,
                RANK() OVER (ORDER BY duree_moyenne ASC) as rank_duree
             FROM (
                SELECT 
                    e.ninv,
                    e.enq_nom,
                    COALESCE(r.nbr_enquetes, 0) as nbr_enquetes,
                    COALESCE(t.taux_completion, 0) as taux_completion,
                    COALESCE(d.duree_moyenne, 0) as duree_moyenne
                FROM enqueteurs e
                LEFT JOIN vue_rendement_enqueteur r ON e.ninv = r.ninv
                LEFT JOIN vue_taux_completion_par_enqueteur t ON e.ninv = t.ninv
                LEFT JOIN vue_duree_par_enqueteur d ON e.ninv = d.ninv
                WHERE r.nbr_enquetes > 0
             ) sub
             ORDER BY nbr_enquetes DESC"
        );
    }
    
    /**
     * Get anomalies for enqueteur
     */
    public function getAnomalies($ninv) {
        return $this->select(
            "SELECT * FROM vue_c99_anomalie WHERE ninv = ? ORDER BY dd DESC",
            [$ninv]
        );
    }
    
    /**
     * Get top performers (for dashboard)
     */
    public function getTopPerformers($limit = 5) {
        return $this->select(
            "SELECT ninv, enq_nom, nbr_enquetes, taux_completion
             FROM vue_taux_completion_par_enqueteur
             WHERE nbr_enquetes >= 5
             ORDER BY taux_completion DESC, nbr_enquetes DESC
             LIMIT ?",
            [$limit]
        );
    }
}
?>
