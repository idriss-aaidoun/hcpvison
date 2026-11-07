<?php
namespace app\models;

/**
 * Dashboard Model - Handles dashboard KPI and statistics
 */
class Dashboard extends Database {
    
    /**
     * Get main KPI data
     */
    public function getKPIData() {
        return $this->selectOne("SELECT * FROM vue_kpi_dashboard");
    }
    
    /**
     * Get global statistics
     */
    public function getGlobalStats() {
        return $this->selectOne("SELECT * FROM vue_resume_global");
    }
    
    /**
     * Get recent surveys (last 10)
     */
    public function getRecentSurveys() {
        return $this->select(
            "SELECT ninv, enq_nom, dd, statut as statut_libelle, enq_reg as reg 
             FROM c99 
             ORDER BY dd DESC 
             LIMIT 10"
        );
    }
    
    /**
     * Get top performing enqueteurs
     */
    public function getTopEnqueteurs($limit = 5) {
        return $this->select(
            "SELECT enq_nom, COUNT(*) as nbr_enquetes, 
                    ROUND(AVG(dureeintervw), 2) as duree_moyenne
             FROM c99 
             WHERE enq_nom IS NOT NULL 
             GROUP BY enq_nom 
             ORDER BY nbr_enquetes DESC 
             LIMIT ?",
            [$limit]
        );
    }
    
    /**
     * Get completion rate by region
     */
    public function getRegionCompletion() {
        return $this->select("SELECT * FROM vue_avancement_region ORDER BY taux_completion DESC");
    }
    
    /**
     * Get daily collection trends (last 30 days)
     */
    public function getCollectionTrends() {
        return $this->select(
            "SELECT DATE(dd::date) as jour, COUNT(*) as enquetes_jour
             FROM c99 
             WHERE dd::date >= CURRENT_DATE - INTERVAL '30 days'
             GROUP BY DATE(dd::date)
             ORDER BY jour"
        );
    }
    
    /**
     * Get status distribution for charts
     */
    public function getStatusDistribution() {
        return $this->select("SELECT * FROM vue_statut_detaille");
    }
    
    /**
     * Get anomalies count
     */
    public function getAnomaliesCount() {
        $result = $this->selectOne(
            "SELECT COUNT(*) as count FROM vue_c99_anomalie"
        );
        return $result['count'];
    }
    
    /**
     * Get surveys by hour of day (for activity analysis)
     */
    public function getSurveysByHour() {
        return $this->select(
            "SELECT HOUR(dd) as hour, COUNT(*) as count 
             FROM vue_enquetes_finales 
             WHERE dd IS NOT NULL 
             GROUP BY HOUR(dd) 
             ORDER BY hour"
        );
    }
    
    /**
     * Get average duration by enqueteur (top 10)
     */
    public function getTopDurationEnqueteurs() {
        return $this->select(
            "SELECT * FROM vue_duree_par_enqueteur 
             WHERE total_enquetes >= 5
             ORDER BY duree_moyenne DESC 
             LIMIT 10"
        );
    }
    
    /**
     * Get completion rate trend over time
     */
    public function getCompletionTrend() {
        return $this->select(
            "SELECT 
                DATE(dd) as date,
                COUNT(*) as total,
                SUM(CASE WHEN statut_libelle = 'Terminé' THEN 1 ELSE 0 END) as completed,
                ROUND(SUM(CASE WHEN statut_libelle = 'Terminé' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as completion_rate
             FROM vue_enquetes_finales 
             WHERE dd >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
             GROUP BY DATE(dd)
             ORDER BY date"
        );
    }
    
    /**
     * Get productivity metrics
     */
    public function getProductivityMetrics() {
        $stats = $this->selectOne(
            "SELECT 
                COUNT(DISTINCT enq_nom) as total_enqueteurs,
                COUNT(*) as total_enquetes,
                ROUND(COUNT(*) / NULLIF(COUNT(DISTINCT enq_nom), 0), 1) as enquetes_par_enqueteur,
                ROUND(AVG(dureeintervw), 1) as duree_moyenne
             FROM c99
             WHERE enq_nom IS NOT NULL"
        );
        
        return $stats;
    }
    
    /**
     * Get data quality indicators
     */
    public function getDataQualityIndicators() {
        // Get suppression rate
        $suppression = $this->selectOne("SELECT * FROM vue_taux_suppression");
        
        // Get anomalies count
        $anomalies = $this->selectOne("SELECT COUNT(*) as count FROM vue_c99_anomalie");
        
        // Get completion distribution
        $completion = $this->select("SELECT * FROM vue_statut_detaille");
        
        return [
            'suppression_rate' => $suppression['taux_suppression'] ?? 0,
            'anomalies_count' => $anomalies['count'] ?? 0,
            'completion_distribution' => $completion
        ];
    }
}
?>
