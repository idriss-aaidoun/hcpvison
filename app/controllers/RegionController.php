<?php
namespace app\controllers;

use app\models\Region;
use app\models\Survey;

/**
 * Region Controller - Manages regional data and statistics
 */
class RegionController extends BaseController {
    
    private $regionModel;
    private $surveyModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        
        $this->regionModel = new Region();
        $this->surveyModel = new Survey();
    }
    
    /**
     * List all regions with statistics
     */
    public function index() {
        try {
            // Get all regions with completion statistics
            $regions = $this->regionModel->getAll();
            
            // Get region comparison data
            $regionComparison = $this->regionModel->getRegionComparison();
            
            // Get enqueteurs count by region
            $enqueteursCount = $this->regionModel->getEnqueteursCount();
            
            // Get milieu distribution
            $milieuDistribution = $this->regionModel->getMilieuDistribution();
            
            // Get provinces summary
            $provinces = $this->regionModel->getProvinces();
            
            $this->render('regions/index', [
                'regions' => $regions,
                'regionComparison' => $regionComparison,
                'enqueteursCount' => $enqueteursCount,
                'milieuDistribution' => $milieuDistribution,
                'provinces' => $provinces,
                'user' => $this->user
            ]);
            
        } catch (Exception $e) {
            error_log("Regions index error: " . $e->getMessage());
            $this->renderError('Erreur lors du chargement des données régionales.');
        }
    }
    
    /**
     * Show detailed view of a specific region
     */
    public function detail() {
        try {
            $regionCode = $this->getGet('code');
            
            if (empty($regionCode)) {
                $this->renderError('Code région manquant.', 400);
            }
            
            // Get region detailed statistics
            $regionStats = $this->regionModel->getDetailedStats($regionCode);
            
            if (!$regionStats) {
                $this->renderError('Région non trouvée.', 404);
            }
            
            // Get duration statistics
            $durationStats = $this->regionModel->getDurationStats($regionCode);
            
            // Get daily progress
            $dailyProgress = $this->regionModel->getDailyProgress($regionCode);
            
            // Get status distribution
            $statusDistribution = $this->regionModel->getStatusDistribution($regionCode);
            
            // Get top provinces
            $topProvinces = $this->regionModel->getTopProvinces($regionCode, 10);
            
            // Get performance trends
            $performanceTrends = $this->regionModel->getPerformanceTrends($regionCode, 30);
            
            // Get surveys for this region
            $recentSurveys = $this->surveyModel->getByRegion($regionCode);
            $recentSurveys = array_slice($recentSurveys, 0, 20); // Limit to 20 most recent
            
            // Get provinces and communes
            $provinces = $this->regionModel->getProvinces($regionCode);
            
            $this->render('regions/detail', [
                'regionStats' => $regionStats,
                'durationStats' => $durationStats,
                'dailyProgress' => $dailyProgress,
                'statusDistribution' => $statusDistribution,
                'topProvinces' => $topProvinces,
                'performanceTrends' => $performanceTrends,
                'recentSurveys' => $recentSurveys,
                'provinces' => $provinces,
                'regionCode' => $regionCode,
                'user' => $this->user
            ]);
            
        } catch (Exception $e) {
            error_log("Region detail error: " . $e->getMessage());
            $this->renderError('Erreur lors du chargement des détails de la région.');
        }
    }
    
    /**
     * API endpoint for regional progress data
     */
    public function apiProgress() {
        try {
            $regions = $this->regionModel->getAll();
            
            $labels = [];
            $tauxCompletion = [];
            $totalEnquetes = [];
            
            foreach ($regions as $region) {
                $labels[] = 'Région ' . $region['region'];
                $tauxCompletion[] = $region['taux_completion'];
                $totalEnquetes[] = $region['total_enquetes'];
            }
            
            $this->renderJSON([
                'labels' => $labels,
                'taux_completion' => $tauxCompletion,
                'total_enquetes' => $totalEnquetes
            ]);
            
        } catch (Exception $e) {
            $this->renderJSON(['error' => 'Erreur lors du chargement des données régionales'], 500);
        }
    }
    
    /**
     * API endpoint for milieu distribution
     */
    public function apiMilieuDistribution() {
        try {
            $regionCode = $this->getGet('region');
            $milieuData = $this->regionModel->getMilieuDistribution();
            
            if ($regionCode) {
                $milieuData = array_filter($milieuData, function($item) use ($regionCode) {
                    return $item['region'] == $regionCode;
                });
            }
            
            $urbain = 0;
            $rural = 0;
            
            foreach ($milieuData as $item) {
                if ($item['milieu'] == '1') { // Urbain
                    $urbain += $item['total_enquetes'];
                } else if ($item['milieu'] == '2') { // Rural
                    $rural += $item['total_enquetes'];
                }
            }
            
            $this->renderJSON([
                'labels' => ['Urbain', 'Rural'],
                'values' => [$urbain, $rural]
            ]);
            
        } catch (Exception $e) {
            $this->renderJSON(['error' => 'Erreur lors du chargement de la distribution milieu'], 500);
        }
    }
    
    /**
     * API endpoint for daily progress trends
     */
    public function apiDailyTrends() {
        try {
            $regionCode = $this->getGet('region');
            
            if (empty($regionCode)) {
                $this->renderJSON(['error' => 'Code région requis'], 400);
                return;
            }
            
            $trends = $this->regionModel->getPerformanceTrends($regionCode, 30);
            
            $labels = [];
            $dailySurveys = [];
            $cumulativeSurveys = [];
            $avgDuration = [];
            
            foreach ($trends as $trend) {
                $labels[] = $this->formatDate($trend['date'], 'd/m');
                $dailySurveys[] = $trend['daily_surveys'];
                $cumulativeSurveys[] = $trend['cumulative_surveys'];
                $avgDuration[] = $trend['avg_duration'];
            }
            
            $this->renderJSON([
                'labels' => $labels,
                'daily_surveys' => $dailySurveys,
                'cumulative_surveys' => $cumulativeSurveys,
                'avg_duration' => $avgDuration
            ]);
            
        } catch (Exception $e) {
            $this->renderJSON(['error' => 'Erreur lors du chargement des tendances'], 500);
        }
    }
    
    /**
     * Get provinces for a region (AJAX)
     */
    public function apiProvinces() {
        try {
            $regionCode = $this->getGet('region');
            $provinces = $this->regionModel->getProvinces($regionCode);
            
            $this->renderJSON($provinces);
            
        } catch (Exception $e) {
            $this->renderJSON(['error' => 'Erreur lors du chargement des provinces'], 500);
        }
    }
    
    /**
     * Get communes for a province (AJAX)
     */
    public function apiCommunes() {
        try {
            $provinceCode = $this->getGet('province');
            $communes = $this->regionModel->getCommunes($provinceCode);
            
            $this->renderJSON($communes);
            
        } catch (Exception $e) {
            $this->renderJSON(['error' => 'Erreur lors du chargement des communes'], 500);
        }
    }
    
    /**
     * Export regional data
     */
    public function export() {
        $this->requireAuth();
        
        try {
            $format = $this->getGet('format', 'csv');
            $regionCode = $this->getGet('region');
            
            if ($regionCode) {
                // Export specific region
                $data = $this->regionModel->getDetailedStats($regionCode);
                $filename = "region_{$regionCode}_" . date('Y-m-d');
            } else {
                // Export all regions
                $data = $this->regionModel->getAll();
                $filename = "regions_" . date('Y-m-d');
            }
            
            if ($format === 'csv') {
                $this->exportToCSV($data, $filename);
            } else {
                $this->exportToPDF($data, $filename);
            }
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Erreur lors de l\'export: ' . $e->getMessage());
            $this->redirect('/regions');
        }
    }
    
    /**
     * Export to CSV
     */
    private function exportToCSV($data, $filename) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        if (is_array($data) && isset($data[0]['region'])) {
            // Multiple regions
            fputcsv($output, [
                'Région',
                'Total Enquêtes',
                'Enquêtes Terminées',
                'Taux Completion (%)'
            ], ';');
            
            foreach ($data as $region) {
                fputcsv($output, [
                    $region['region'],
                    $region['total_enquetes'],
                    $region['enquetes_terminees'],
                    $this->formatNumber($region['taux_completion'], 1)
                ], ';');
            }
        } else {
            // Single region details
            fputcsv($output, ['Métrique', 'Valeur'], ';');
            
            if (isset($data['region'])) {
                fputcsv($output, ['Région', $data['region']], ';');
                fputcsv($output, ['Total Enquêtes', $data['total_enquetes']], ';');
                fputcsv($output, ['Enquêtes Terminées', $data['enquetes_terminees']], ';');
                fputcsv($output, ['Taux Completion', $this->formatPercentage($data['taux_completion'])], ';');
            }
        }
        
        fclose($output);
    }
    
    /**
     * Export to PDF
     */
    private function exportToPDF($data, $filename) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
        
        echo $this->generatePDFContent($data, $filename);
    }
    
    /**
     * Generate PDF content
     */
    private function generatePDFContent($data, $title) {
        ob_start();
        
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>' . htmlspecialchars($title) . '</title>
            <style>
                body { font-family: Arial, sans-serif; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .header { text-align: center; margin-bottom: 30px; }
            </style>
        </head>
        <body>';
        
        echo '<div class="header">';
        echo '<h1>Rapport Régional</h1>';
        echo '<p>Généré le: ' . $this->formatDate(date('Y-m-d H:i:s')) . '</p>';
        echo '</div>';
        
        if (is_array($data) && isset($data[0]['region'])) {
            echo '<h2>Résumé par Région</h2>';
            echo '<table>';
            echo '<thead>
                    <tr>
                        <th>Région</th>
                        <th>Total Enquêtes</th>
                        <th>Terminées</th>
                        <th>Taux Completion</th>
                    </tr>
                  </thead>';
            
            echo '<tbody>';
            foreach ($data as $region) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($region['region']) . '</td>';
                echo '<td>' . $this->formatNumber($region['total_enquetes']) . '</td>';
                echo '<td>' . $this->formatNumber($region['enquetes_terminees']) . '</td>';
                echo '<td>' . $this->formatPercentage($region['taux_completion']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        
        echo '</body></html>';
        
        return ob_get_clean();
    }
}
?>
