<?php
namespace app\controllers;

use app\models\Survey;
use app\models\Database;

/**
 * Anomalie Controller - Handles data anomaly detection and reporting
 */
class AnomalieController extends BaseController {
    
    private $surveyModel;
    private $database;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        
        $this->surveyModel = new Survey();
        $this->database = new Database();
    }
    
    /**
     * Display anomalies dashboard
     */
    public function index() {
        try {
            // Get anomalies from vue_c99_anomalie
            $anomalies = $this->getAnomalies();
            
            // Get anomalies by type
            $anomaliesByType = $this->getAnomaliesByType();
            
            // Get anomalies by enqueteur
            $anomaliesByEnqueteur = $this->getAnomaliesByEnqueteur();
            
            // Get date-related anomalies
            $dateAnomalies = $this->getDateAnomalies();
            
            // Get duration anomalies
            $durationAnomalies = $this->getDurationAnomalies();
            
            // Get data quality metrics
            $qualityMetrics = $this->getDataQualityMetrics();
            
            $this->render('anomalies/index', [
                'anomalies' => $anomalies,
                'anomaliesByType' => $anomaliesByType,
                'anomaliesByEnqueteur' => $anomaliesByEnqueteur,
                'dateAnomalies' => $dateAnomalies,
                'durationAnomalies' => $durationAnomalies,
                'qualityMetrics' => $qualityMetrics,
                'user' => $this->user
            ]);
            
        } catch (Exception $e) {
            error_log("Anomalies index error: " . $e->getMessage());
            $this->renderError('Erreur lors du chargement des anomalies.');
        }
    }
    
    /**
     * Get all anomalies from vue_c99_anomalie
     */
    private function getAnomalies($limit = 100) {
        return $this->database->select(
            "SELECT * FROM vue_c99_anomalie 
             ORDER BY dd DESC 
             LIMIT ?",
            [$limit]
        );
    }
    
    /**
     * Get anomalies grouped by type
     */
    private function getAnomaliesByType() {
        $anomalies = [];
        
        // Date inconsistencies (df <= dd)
        $dateAnomalies = $this->database->select(
            "SELECT COUNT(*) as count FROM vue_c99_anomalie 
             WHERE df <= dd AND df IS NOT NULL"
        );
        
        // Null durations
        $nullDurations = $this->database->select(
            "SELECT COUNT(*) as count FROM vue_c99_anomalie 
             WHERE dureeintervw IS NULL"
        );
        
        // Extreme durations (>9999 or >=180)
        $extremeDurations = $this->database->select(
            "SELECT COUNT(*) as count FROM vue_c99_anomalie 
             WHERE dureeintervw > 9999 OR dureeintervw >= 180"
        );
        
        return [
            'date_inconsistencies' => $dateAnomalies[0]['count'] ?? 0,
            'null_durations' => $nullDurations[0]['count'] ?? 0,
            'extreme_durations' => $extremeDurations[0]['count'] ?? 0
        ];
    }
    
    /**
     * Get anomalies by enqueteur
     */
    private function getAnomaliesByEnqueteur() {
        return $this->database->select(
            "SELECT ninv, enq_nom, COUNT(*) as anomalies_count
             FROM vue_c99_anomalie 
             GROUP BY ninv, enq_nom
             ORDER BY anomalies_count DESC
             LIMIT 20"
        );
    }
    
    /**
     * Get date-related anomalies
     */
    private function getDateAnomalies() {
        return $this->database->select(
            "SELECT * FROM vue_c99_anomalie 
             WHERE df <= dd AND df IS NOT NULL
             ORDER BY dd DESC
             LIMIT 50"
        );
    }
    
    /**
     * Get duration anomalies
     */
    private function getDurationAnomalies() {
        return $this->database->select(
            "SELECT * FROM vue_c99_anomalie 
             WHERE dureeintervw IS NULL OR dureeintervw > 180 OR dureeintervw > 9999
             ORDER BY dureeintervw DESC
             LIMIT 50"
        );
    }
    
    /**
     * Get data quality metrics
     */
    private function getDataQualityMetrics() {
        // Total surveys
        $totalSurveys = $this->database->selectOne(
            "SELECT COUNT(*) as count FROM c99"
        );
        
        // Total anomalies
        $totalAnomalies = $this->database->selectOne(
            "SELECT COUNT(*) as count FROM vue_c99_anomalie"
        );
        
        // Suppression rate
        $suppressionData = $this->database->selectOne(
            "SELECT * FROM vue_taux_suppression"
        );
        
        // Null values analysis
        $nullAnalysis = $this->database->selectOne(
            "SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN dd IS NULL THEN 1 ELSE 0 END) AS null_dd,
                SUM(CASE WHEN df IS NULL THEN 1 ELSE 0 END) AS null_df,
                SUM(CASE WHEN dureeintervw IS NULL THEN 1 ELSE 0 END) AS null_duree,
                SUM(CASE WHEN enq_nom IS NULL THEN 1 ELSE 0 END) AS null_enq
             FROM c99"
        );
        
        return [
            'total_surveys' => $totalSurveys['count'] ?? 0,
            'total_anomalies' => $totalAnomalies['count'] ?? 0,
            'anomaly_rate' => $totalSurveys['count'] > 0 ? 
                round(($totalAnomalies['count'] / $totalSurveys['count']) * 100, 2) : 0,
            'suppression_rate' => $suppressionData['taux_suppression'] ?? 0,
            'null_analysis' => $nullAnalysis
        ];
    }
    
    /**
     * API endpoint for anomalies data
     */
    public function apiAnomaliesData() {
        try {
            $type = $this->getGet('type', 'all');
            
            switch ($type) {
                case 'by_type':
                    $data = $this->getAnomaliesByType();
                    break;
                    
                case 'by_enqueteur':
                    $data = $this->getAnomaliesByEnqueteur();
                    break;
                    
                case 'trends':
                    $data = $this->getAnomaliesTrends();
                    break;
                    
                default:
                    $data = $this->getAnomalies(50);
            }
            
            $this->renderJSON($data);
            
        } catch (Exception $e) {
            $this->renderJSON(['error' => 'Erreur lors du chargement des données d\'anomalies'], 500);
        }
    }
    
    /**
     * Get anomalies trends over time
     */
    private function getAnomaliesTrends() {
        return $this->database->select(
            "SELECT 
                DATE(dd) as date,
                COUNT(*) as anomalies_count
             FROM vue_c99_anomalie 
             WHERE dd IS NOT NULL 
               AND dd >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
             GROUP BY DATE(dd)
             ORDER BY date"
        );
    }
    
    /**
     * Export anomalies report
     */
    public function export() {
        $this->requireAuth();
        
        try {
            $format = $this->getGet('format', 'csv');
            $type = $this->getGet('type', 'all');
            
            $data = $this->getAnomalies(1000); // Get more data for export
            
            if ($format === 'csv') {
                $this->exportToCSV($data);
            } else {
                $this->exportToPDF($data);
            }
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Erreur lors de l\'export: ' . $e->getMessage());
            $this->redirect('/anomalies');
        }
    }
    
    /**
     * Export to CSV
     */
    private function exportToCSV($anomalies) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="anomalies_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        fputcsv($output, [
            'ID',
            'NINV',
            'Enquêteur',
            'Date Début',
            'Date Fin',
            'Durée',
            'Version',
            'Région',
            'Statut',
            'Type Anomalie'
        ], ';');
        
        // Data
        foreach ($anomalies as $anomalie) {
            $typeAnomalie = [];
            
            if (empty($anomalie['df']) || $anomalie['df'] <= $anomalie['dd']) {
                $typeAnomalie[] = 'Date incohérente';
            }
            if (empty($anomalie['dureeintervw'])) {
                $typeAnomalie[] = 'Durée manquante';
            }
            if (!empty($anomalie['dureeintervw']) && ($anomalie['dureeintervw'] > 9999 || $anomalie['dureeintervw'] >= 180)) {
                $typeAnomalie[] = 'Durée extrême';
            }
            
            fputcsv($output, [
                $anomalie['c99-id'],
                $anomalie['ninv'],
                $anomalie['enq_nom'],
                $anomalie['dd'],
                $anomalie['df'],
                $anomalie['dureeintervw'],
                $anomalie['version'],
                $anomalie['enq_reg'],
                $anomalie['statut'],
                implode(', ', $typeAnomalie)
            ], ';');
        }
        
        fclose($output);
    }
    
    /**
     * Export to PDF
     */
    private function exportToPDF($anomalies) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="anomalies_' . date('Y-m-d') . '.pdf"');
        
        echo $this->generatePDFContent($anomalies);
    }
    
    /**
     * Generate PDF content
     */
    private function generatePDFContent($anomalies) {
        ob_start();
        
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Rapport d\'Anomalies</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
                th { background-color: #f2f2f2; }
                .header { text-align: center; margin-bottom: 30px; }
                .anomalie-type { color: #dc3545; font-weight: bold; }
            </style>
        </head>
        <body>';
        
        echo '<div class="header">';
        echo '<h1>Rapport d\'Anomalies</h1>';
        echo '<p>Généré le: ' . $this->formatDate(date('Y-m-d H:i:s')) . '</p>';
        echo '<p>Total anomalies: ' . count($anomalies) . '</p>';
        echo '</div>';
        
        echo '<table>';
        echo '<thead>
                <tr>
                    <th>ID</th>
                    <th>NINV</th>
                    <th>Enquêteur</th>
                    <th>Date Début</th>
                    <th>Date Fin</th>
                    <th>Durée</th>
                    <th>Type Anomalie</th>
                </tr>
              </thead>';
        
        echo '<tbody>';
        foreach (array_slice($anomalies, 0, 100) as $anomalie) { // Limit for PDF
            $typeAnomalie = [];
            
            if (empty($anomalie['df']) || $anomalie['df'] <= $anomalie['dd']) {
                $typeAnomalie[] = 'Date incohérente';
            }
            if (empty($anomalie['dureeintervw'])) {
                $typeAnomalie[] = 'Durée manquante';
            }
            if (!empty($anomalie['dureeintervw']) && ($anomalie['dureeintervw'] > 9999 || $anomalie['dureeintervw'] >= 180)) {
                $typeAnomalie[] = 'Durée extrême';
            }
            
            echo '<tr>';
            echo '<td>' . htmlspecialchars($anomalie['c99-id']) . '</td>';
            echo '<td>' . htmlspecialchars($anomalie['ninv']) . '</td>';
            echo '<td>' . htmlspecialchars($anomalie['enq_nom']) . '</td>';
            echo '<td>' . $this->formatDate($anomalie['dd'], 'd/m/Y H:i') . '</td>';
            echo '<td>' . ($anomalie['df'] ? $this->formatDate($anomalie['df'], 'd/m/Y H:i') : '-') . '</td>';
            echo '<td>' . ($anomalie['dureeintervw'] ?? '-') . '</td>';
            echo '<td class="anomalie-type">' . implode(', ', $typeAnomalie) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        
        if (count($anomalies) > 100) {
            echo '<p><em>Note: Seules les 100 premières anomalies sont affichées dans ce rapport PDF.</em></p>';
        }
        
        echo '</body></html>';
        
        return ob_get_clean();
    }
    
    /**
     * Fix anomaly (admin only)
     */
    public function fix() {
        $this->requireAdmin();
        
        try {
            $id = $this->getPost('id');
            $action = $this->getPost('action');
            
            if (empty($id) || empty($action)) {
                $this->renderJSON(['success' => false, 'message' => 'Données manquantes'], 400);
                return;
            }
            
            $success = false;
            $message = '';
            
            switch ($action) {
                case 'mark_resolved':
                    // Add a comment or flag to mark as resolved
                    $success = $this->database->execute(
                        "UPDATE c99 SET commentaire = CONCAT(COALESCE(commentaire, ''), ' [ANOMALIE RÉSOLUE]') WHERE `c99-id` = ?",
                        [$id]
                    );
                    $message = 'Anomalie marquée comme résolue';
                    break;
                    
                case 'delete':
                    // Soft delete the survey
                    $success = $this->database->execute(
                        "UPDATE c99 SET statut = 'SUPPRIME' WHERE `c99-id` = ?",
                        [$id]
                    );
                    $message = 'Enquête supprimée';
                    break;
                    
                default:
                    $message = 'Action non reconnue';
            }
            
            $this->renderJSON([
                'success' => $success,
                'message' => $message
            ]);
            
        } catch (Exception $e) {
            $this->renderJSON(['success' => false, 'message' => 'Erreur serveur'], 500);
        }
    }
}
?>
