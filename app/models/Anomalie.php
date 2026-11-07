<?php
/**
 * Anomalie model for detecting and managing data inconsistencies
 */

class Anomalie extends BaseModel {
    
    /**
     * Get surveys from anomaly view
     */
    public function getAnomaliesC99() {
        $query = "SELECT * FROM vue_c99_anomalie ORDER BY `c99-id` DESC";
        return $this->select($query);
    }
    
    /**
     * Get surveys with duration anomalies (too short or too long)
     */
    public function getAnomaliesDuree() {
        $query = "
            SELECT 
                ninv, enq_nom, dd, df, dureeintervw,
                'Durée anormale' as type_anomalie,
                CASE 
                    WHEN dureeintervw < 5 THEN 'Trop courte (< 5 min)'
                    WHEN dureeintervw > 180 THEN 'Trop longue (> 3h)'
                    ELSE 'Durée normale'
                END as description
            FROM vue_enquetes_finales 
            WHERE dureeintervw < 5 OR dureeintervw > 180
            ORDER BY dureeintervw DESC
        ";
        return $this->select($query);
    }
    
    /**
     * Get surveys with date inconsistencies
     */
    public function getAnomaliesDates() {
        $query = "
            SELECT 
                ninv, enq_nom, dd, df,
                'Date incohérente' as type_anomalie,
                'Date fin antérieure à date début' as description
            FROM c99 
            WHERE df IS NOT NULL AND dd IS NOT NULL AND df < dd
            ORDER BY dd DESC
        ";
        return $this->select($query);
    }
    
    /**
     * Get surveys with missing critical data
     */
    public function getAnomaliesDonnees() {
        $query = "
            SELECT 
                `c99-id`, ninv, enq_nom, dd, df, dureeintervw,
                'Données manquantes' as type_anomalie,
                CASE 
                    WHEN df IS NULL THEN 'Date fin manquante'
                    WHEN dureeintervw IS NULL THEN 'Durée manquante'
                    WHEN enq_nom IS NULL THEN 'Nom enquêteur manquant'
                    ELSE 'Autres données manquantes'
                END as description
            FROM c99 
            WHERE df IS NULL OR dureeintervw IS NULL OR enq_nom IS NULL
            ORDER BY dd DESC
        ";
        return $this->select($query);
    }
    
    /**
     * Get surveyors with suspicious patterns
     */
    public function getAnomaliesEnqueteurs() {
        $query = "
            SELECT 
                ninv, enq_nom,
                COUNT(*) as total_enquetes,
                AVG(dureeintervw) as duree_moyenne,
                MIN(dureeintervw) as duree_min,
                MAX(dureeintervw) as duree_max,
                'Comportement suspect' as type_anomalie,
                CASE 
                    WHEN AVG(dureeintervw) < 10 THEN 'Durée moyenne très faible'
                    WHEN MAX(dureeintervw) - MIN(dureeintervw) < 5 THEN 'Durées trop uniformes'
                    WHEN COUNT(*) > 100 THEN 'Productivité excessive'
                    ELSE 'Pattern suspect'
                END as description
            FROM vue_enquetes_finales 
            WHERE dureeintervw IS NOT NULL
            GROUP BY ninv, enq_nom
            HAVING 
                AVG(dureeintervw) < 10 OR 
                (MAX(dureeintervw) - MIN(dureeintervw) < 5 AND COUNT(*) > 10) OR
                COUNT(*) > 100
            ORDER BY total_enquetes DESC
        ";
        return $this->select($query);
    }
    
    /**
     * Get deleted cases analysis
     */
    public function getCasSupprimes() {
        $query = "
            SELECT 
                ninv, enq_nom,
                COUNT(*) as total_enquetes,
                SUM(CASE WHEN case_deleted = '1' THEN 1 ELSE 0 END) as cas_supprimes,
                ROUND(SUM(CASE WHEN case_deleted = '1' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as taux_suppression,
                'Taux suppression élevé' as type_anomalie
            FROM vue_enquetes_finales
            GROUP BY ninv, enq_nom
            HAVING taux_suppression > 10
            ORDER BY taux_suppression DESC
        ";
        return $this->select($query);
    }
    
    /**
     * Get summary of all anomalies by type
     */
    public function getResumeAnomalies() {
        $anomalies = [];
        
        // Count duration anomalies
        $query = "SELECT COUNT(*) as count FROM vue_enquetes_finales WHERE dureeintervw < 5 OR dureeintervw > 180";
        $result = $this->selectOne($query);
        $anomalies[] = [
            'type' => 'Durée anormale',
            'count' => $result['count'],
            'description' => 'Enquêtes avec durée < 5 min ou > 3h'
        ];
        
        // Count date anomalies
        $query = "SELECT COUNT(*) as count FROM c99 WHERE df IS NOT NULL AND dd IS NOT NULL AND df < dd";
        $result = $this->selectOne($query);
        $anomalies[] = [
            'type' => 'Dates incohérentes',
            'count' => $result['count'],
            'description' => 'Date fin antérieure à date début'
        ];
        
        // Count missing data
        $query = "SELECT COUNT(*) as count FROM c99 WHERE df IS NULL OR dureeintervw IS NULL OR enq_nom IS NULL";
        $result = $this->selectOne($query);
        $anomalies[] = [
            'type' => 'Données manquantes',
            'count' => $result['count'],
            'description' => 'Champs critiques vides'
        ];
        
        // Count high deletion rate surveyors
        $query = "
            SELECT COUNT(*) as count FROM (
                SELECT ninv FROM vue_enquetes_finales
                GROUP BY ninv
                HAVING SUM(CASE WHEN case_deleted = '1' THEN 1 ELSE 0 END) / COUNT(*) * 100 > 10
            ) tmp
        ";
        $result = $this->selectOne($query);
        $anomalies[] = [
            'type' => 'Taux suppression élevé',
            'count' => $result['count'],
            'description' => 'Enquêteurs avec > 10% de suppressions'
        ];
        
        return $anomalies;
    }
    
    /**
     * Get detailed anomaly report for a specific surveyor
     */
    public function getAnomaliesEnqueteur($ninv) {
        $anomalies = [];
        
        // Duration anomalies
        $query = "
            SELECT * FROM vue_enquetes_finales 
            WHERE ninv = ? AND (dureeintervw < 5 OR dureeintervw > 180)
            ORDER BY dd DESC
        ";
        $anomalies['duree'] = $this->select($query, [$ninv]);
        
        // Missing data
        $query = "
            SELECT * FROM c99 
            WHERE ninv = ? AND (df IS NULL OR dureeintervw IS NULL)
            ORDER BY dd DESC
        ";
        $anomalies['donnees_manquantes'] = $this->select($query, [$ninv]);
        
        // Deleted cases
        $query = "
            SELECT * FROM vue_enquetes_finales 
            WHERE ninv = ? AND case_deleted = '1'
            ORDER BY dd DESC
        ";
        $anomalies['cas_supprimes'] = $this->select($query, [$ninv]);
        
        return $anomalies;
    }
}
