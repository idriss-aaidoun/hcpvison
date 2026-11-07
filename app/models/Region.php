<?php
namespace app\models;

/**
 * Region Model - Handles regional data and statistics
 */
class Region extends Database {
    
    /**
     * Get all regions with statistics
     */
    public function getAll() {
        return $this->select("SELECT * FROM vue_avancement_region ORDER BY region");
    }
    
    /**
     * Get region statistics by region code
     */
    public function getByCode($regionCode) {
        return $this->selectOne(
            "SELECT * FROM vue_avancement_region WHERE region = ?",
            [$regionCode]
        );
    }
    
    /**
     * Get detailed region data including enqueteurs
     */
    public function getDetailedStats($regionCode) {
        $stats = $this->selectOne(
            "SELECT * FROM vue_avancement_region WHERE region = ?",
            [$regionCode]
        );
        
        if ($stats) {
            // Get enqueteurs in this region
            $stats['enqueteurs'] = $this->select(
                "SELECT * FROM vue_enqueteurs_region WHERE region = ? ORDER BY taux_completion DESC",
                [$regionCode]
            );
            
            // Get milieu distribution
            $stats['milieu_distribution'] = $this->select(
                "SELECT * FROM vue_milieu_par_region WHERE region = ?",
                [$regionCode]
            );
        }
        
        return $stats;
    }
    
    /**
     * Get enqueteurs count by region
     */
    public function getEnqueteursCount() {
        return $this->select("SELECT * FROM vue_enqueteurs_par_region ORDER BY region");
    }
    
    /**
     * Get milieu distribution for all regions
     */
    public function getMilieuDistribution() {
        return $this->select("SELECT * FROM vue_milieu_par_region ORDER BY region, milieu");
    }
    
    /**
     * Get provinces by region
     */
    public function getProvinces($regionCode = null) {
        $sql = "SELECT c.enq_reg as region, g.pro as province, COUNT(*) as total_enquetes
                FROM c99 c
                LEFT JOIN cgm g ON c.ninv = g.\"level-1-id\"::text
                WHERE c.enq_reg IS NOT NULL AND g.pro IS NOT NULL";
        $params = [];
        
        if ($regionCode) {
            $sql .= " AND c.enq_reg = ?";
            $params[] = $regionCode;
        }
        
        $sql .= " GROUP BY c.enq_reg, g.pro ORDER BY c.enq_reg, g.pro";
        
        return $this->select($sql, $params);
    }
    
    /**
     * Get communes by province
     */
    public function getCommunes($provinceCode = null) {
        $sql = "SELECT reg as region, pro as province, com as commune, COUNT(*) as total_enquetes
                FROM vue_enquetes_finales 
                WHERE reg IS NOT NULL AND pro IS NOT NULL AND com IS NOT NULL";
        $params = [];
        
        if ($provinceCode) {
            $sql .= " AND pro = ?";
            $params[] = $provinceCode;
        }
        
        $sql .= " GROUP BY reg, pro, com ORDER BY reg, pro, com";
        
        return $this->select($sql, $params);
    }
    
    /**
     * Get daily progress by region
     */
    public function getDailyProgress($regionCode) {
        return $this->select(
            "SELECT 
                DATE(dd) as date,
                COUNT(*) as total,
                SUM(CASE WHEN statut_libelle = 'Terminé' THEN 1 ELSE 0 END) as completed
             FROM vue_enquetes_finales 
             WHERE reg = ?
             GROUP BY DATE(dd)
             ORDER BY date",
            [$regionCode]
        );
    }
    
    /**
     * Get duration statistics by region
     */
    public function getDurationStats($regionCode) {
        return $this->selectOne(
            "SELECT 
                reg as region,
                COUNT(*) as total_enquetes,
                ROUND(AVG(dureeintervw), 1) as duree_moyenne,
                MIN(dureeintervw) as duree_min,
                MAX(dureeintervw) as duree_max,
                ROUND(STDDEV(dureeintervw), 1) as duree_stddev
             FROM vue_enquetes_finales 
             WHERE reg = ? AND dureeintervw IS NOT NULL
             GROUP BY reg",
            [$regionCode]
        );
    }
    
    /**
     * Get comparison between regions
     */
    public function getRegionComparison() {
        return $this->select(
            "SELECT 
                ar.*,
                RANK() OVER (ORDER BY ar.taux_completion DESC) as rank_completion,
                RANK() OVER (ORDER BY ar.total_enquetes DESC) as rank_volume,
                ep.nb_enqueteurs
             FROM vue_avancement_region ar
             LEFT JOIN vue_enqueteurs_par_region ep ON ar.region = ep.region
             ORDER BY ar.taux_completion DESC"
        );
    }
    
    /**
     * Get status distribution by region
     */
    public function getStatusDistribution($regionCode) {
        return $this->select(
            "SELECT 
                statut_libelle,
                COUNT(*) as count,
                ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER(), 2) as percentage
             FROM vue_enquetes_finales 
             WHERE reg = ?
             GROUP BY statut_libelle
             ORDER BY count DESC",
            [$regionCode]
        );
    }
    
    /**
     * Get top performing provinces in a region
     */
    public function getTopProvinces($regionCode, $limit = 5) {
        return $this->select(
            "SELECT 
                pro as province,
                COUNT(*) as total_enquetes,
                SUM(CASE WHEN statut_libelle = 'Terminé' THEN 1 ELSE 0 END) as enquetes_terminees,
                ROUND(SUM(CASE WHEN statut_libelle = 'Terminé' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as taux_completion
             FROM vue_enquetes_finales 
             WHERE reg = ?
             GROUP BY pro
             ORDER BY taux_completion DESC, total_enquetes DESC
             LIMIT ?",
            [$regionCode, $limit]
        );
    }
    
    /**
     * Get regional performance trends
     */
    public function getPerformanceTrends($regionCode, $days = 30) {
        return $this->select(
            "SELECT 
                DATE(dd) as date,
                COUNT(*) as daily_surveys,
                SUM(COUNT(*)) OVER (ORDER BY DATE(dd)) as cumulative_surveys,
                ROUND(AVG(dureeintervw), 1) as avg_duration
             FROM vue_enquetes_finales 
             WHERE reg = ? 
               AND dd >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
             GROUP BY DATE(dd)
             ORDER BY date",
            [$regionCode, $days]
        );
    }
    
    /**
     * Get unique regions list
     */
    public function getRegionsList() {
        return $this->select(
            "SELECT DISTINCT reg as region, COUNT(*) as total_enquetes
             FROM vue_enquetes_finales 
             WHERE reg IS NOT NULL
             GROUP BY reg
             ORDER BY reg"
        );
    }
}
?>
