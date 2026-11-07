<?php
namespace app\controllers;

use app\models\Dashboard;
use app\models\Survey;
use app\models\Enqueteur;
use app\models\Region;

/**
 * Rapport Controller - Handles report generation and export
 */
class RapportController extends BaseController {
    
    private $dashboardModel;
    private $surveyModel;
    private $enqueteurModel;
    private $regionModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        
        $this->dashboardModel = new Dashboard();
        $this->surveyModel = new Survey();
        $this->enqueteurModel = new Enqueteur();
        $this->regionModel = new Region();
    }
    
    /**
     * Reports dashboard - select report type and parameters
     */
    public function index() {
        try {
            // Get available report types
            $reportTypes = $this->getReportTypes();
            
            // Get available enqueteurs and regions for filters
            $enqueteurs = $this->enqueteurModel->getAll();
            $regions = $this->regionModel->getRegionsList();
            
            // Get recent reports (if we implement report history)
            $recentReports = $this->getRecentReports();
            
            $this->render('rapports/index', [
                'reportTypes' => $reportTypes,
                'enqueteurs' => $enqueteurs,
                'regions' => $regions,
                'recentReports' => $recentReports,
                'user' => $this->user
            ]);
            
        } catch (Exception $e) {
            error_log("Reports index error: " . $e->getMessage());
            $this->renderError('Erreur lors du chargement des rapports.');
        }
    }
    
    /**
     * Generate report based on parameters
     */
    public function generate() {
        try {
            $reportType = $this->getPost('type') ?? $this->getGet('type');
            $format = $this->getPost('format', 'pdf');
            $filters = $this->getReportFilters();
            
            if (empty($reportType)) {
                $this->renderError('Type de rapport manquant.', 400);
                return;
            }
            
            // Generate report data based on type
            $data = $this->generateReportData($reportType, $filters);
            
            // Export in requested format
            if ($format === 'pdf') {
                $this->generatePDFReport($reportType, $data, $filters);
            } else if ($format === 'csv') {
                $this->generateCSVReport($reportType, $data, $filters);
            } else if ($format === 'excel') {
                $this->generateExcelReport($reportType, $data, $filters);
            } else {
                $this->renderError('Format de rapport non supporté.', 400);
            }
            
        } catch (Exception $e) {
            error_log("Report generation error: " . $e->getMessage());
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->renderJSON(['success' => false, 'message' => 'Erreur lors de la génération du rapport: ' . $e->getMessage()], 500);
            } else {
                $this->renderError('Erreur lors de la génération du rapport.');
            }
        }
    }
    
    /**
     * Get available report types
     */
    private function getReportTypes() {
        return [
            'dashboard' => [
                'name' => 'Tableau de Bord Complet',
                'description' => 'KPI globaux, statistiques générales et tendances',
                'icon' => 'fas fa-tachometer-alt'
            ],
            'enqueteurs' => [
                'name' => 'Performance des Enquêteurs',
                'description' => 'Statistiques détaillées par enquêteur',
                'icon' => 'fas fa-users'
            ],
            'regions' => [
                'name' => 'Analyse Régionale',
                'description' => 'Progression et statistiques par région',
                'icon' => 'fas fa-map-marked-alt'
            ],
            'anomalies' => [
                'name' => 'Rapport d\'Anomalies',
                'description' => 'Détection et analyse des incohérences',
                'icon' => 'fas fa-exclamation-triangle'
            ],
            'collecte' => [
                'name' => 'Suivi de Collecte',
                'description' => 'Évolution quotidienne de la collecte',
                'icon' => 'fas fa-chart-line'
            ],
            'qualite' => [
                'name' => 'Qualité des Données',
                'description' => 'Indicateurs de qualité et complétude',
                'icon' => 'fas fa-check-circle'
            ]
        ];
    }
    
    /**
     * Get report filters from request
     */
    private function getReportFilters() {
        return [
            'date_debut' => $this->getPost('date_debut') ?? $this->getGet('date_debut'),
            'date_fin' => $this->getPost('date_fin') ?? $this->getGet('date_fin'),
            'enqueteur' => $this->getPost('enqueteur') ?? $this->getGet('enqueteur'),
            'region' => $this->getPost('region') ?? $this->getGet('region'),
            'statut' => $this->getPost('statut') ?? $this->getGet('statut'),
            'include_charts' => $this->getPost('include_charts', false),
            'detailed' => $this->getPost('detailed', false)
        ];
    }
    
    /**
     * Generate report data based on type
     */
    private function generateReportData($type, $filters) {
        $data = [];
        
        switch ($type) {
            case 'dashboard':
                $data = $this->generateDashboardReportData($filters);
                break;
                
            case 'enqueteurs':
                $data = $this->generateEnqueteursReportData($filters);
                break;
                
            case 'regions':
                $data = $this->generateRegionsReportData($filters);
                break;
                
            case 'anomalies':
                $data = $this->generateAnomaliesReportData($filters);
                break;
                
            case 'collecte':
                $data = $this->generateCollecteReportData($filters);
                break;
                
            case 'qualite':
                $data = $this->generateQualiteReportData($filters);
                break;
                
            default:
                throw new Exception("Type de rapport non reconnu: $type");
        }
        
        return $data;
    }
    
    /**
     * Generate dashboard report data
     */
    private function generateDashboardReportData($filters) {
        return [
            'title' => 'Rapport du Tableau de Bord',
            'kpiData' => $this->dashboardModel->getKPIData(),
            'globalStats' => $this->dashboardModel->getGlobalStats(),
            'topEnqueteurs' => $this->dashboardModel->getTopEnqueteurs(10),
            'regionCompletion' => $this->dashboardModel->getRegionCompletion(),
            'statusDistribution' => $this->dashboardModel->getStatusDistribution(),
            'collectionTrends' => $this->dashboardModel->getCollectionTrends(),
            'productivityMetrics' => $this->dashboardModel->getProductivityMetrics(),
            'dataQuality' => $this->dashboardModel->getDataQualityIndicators()
        ];
    }
    
    /**
     * Generate enqueteurs report data
     */
    private function generateEnqueteursReportData($filters) {
        $enqueteurs = $this->enqueteurModel->getAll();
        
        // Apply filters if specified
        if (!empty($filters['enqueteur'])) {
            $enqueteurs = array_filter($enqueteurs, function($e) use ($filters) {
                return $e['ninv'] === $filters['enqueteur'];
            });
        }
        
        return [
            'title' => 'Rapport de Performance des Enquêteurs',
            'enqueteurs' => $enqueteurs,
            'performanceStats' => $this->enqueteurModel->getPerformanceStats(),
            'completionStats' => $this->enqueteurModel->getCompletionStats(),
            'durationStats' => $this->enqueteurModel->getDurationStats(),
            'topPerformers' => $this->enqueteurModel->getTopPerformers(10)
        ];
    }
    
    /**
     * Generate regions report data
     */
    private function generateRegionsReportData($filters) {
        $regions = $this->regionModel->getAll();
        
        if (!empty($filters['region'])) {
            $regions = array_filter($regions, function($r) use ($filters) {
                return $r['region'] == $filters['region'];
            });
        }
        
        return [
            'title' => 'Rapport d\'Analyse Régionale',
            'regions' => $regions,
            'regionComparison' => $this->regionModel->getRegionComparison(),
            'milieuDistribution' => $this->regionModel->getMilieuDistribution(),
            'provinces' => $this->regionModel->getProvinces()
        ];
    }
    
    /**
     * Generate anomalies report data
     */
    private function generateAnomaliesReportData($filters) {
        // This would use the AnomalieController logic
        $anomalieController = new \app\controllers\AnomalieController();
        
        return [
            'title' => 'Rapport d\'Anomalies',
            'summary' => 'Analyse des incohérences et problèmes de qualité des données'
        ];
    }
    
    /**
     * Generate collecte report data
     */
    private function generateCollecteReportData($filters) {
        return [
            'title' => 'Rapport de Suivi de Collecte',
            'dailyStats' => $this->surveyModel->getDailyStats(),
            'collectionTrends' => $this->dashboardModel->getCollectionTrends(),
            'completionTrend' => $this->dashboardModel->getCompletionTrend()
        ];
    }
    
    /**
     * Generate qualite report data
     */
    private function generateQualiteReportData($filters) {
        return [
            'title' => 'Rapport de Qualité des Données',
            'dataQuality' => $this->dashboardModel->getDataQualityIndicators(),
            'statistics' => $this->surveyModel->getStatistics()
        ];
    }
    
    /**
     * Generate PDF report
     */
    private function generatePDFReport($type, $data, $filters) {
        // Set headers for PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="rapport_' . $type . '_' . date('Y-m-d_H-i') . '.pdf"');
        
        // Generate HTML content for PDF
        $htmlContent = $this->generateReportHTML($type, $data, $filters);
        
        // For a real implementation, you would use a library like TCPDF or mPDF
        // For now, we'll output HTML that can be converted to PDF
        echo $htmlContent;
    }
    
    /**
     * Generate CSV report
     */
    private function generateCSVReport($type, $data, $filters) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="rapport_' . $type . '_' . date('Y-m-d_H-i') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Generate CSV content based on report type
        $this->writeCSVContent($output, $type, $data);
        
        fclose($output);
    }
    
    /**
     * Generate Excel report
     */
    private function generateExcelReport($type, $data, $filters) {
        // For Excel, we'll generate CSV with .xlsx extension
        // In a real implementation, you'd use PHPSpreadsheet
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="rapport_' . $type . '_' . date('Y-m-d_H-i') . '.xlsx"');
        
        $output = fopen('php://output', 'w');
        $this->writeCSVContent($output, $type, $data);
        fclose($output);
    }
    
    /**
     * Generate HTML content for PDF reports
     */
    private function generateReportHTML($type, $data, $filters) {
        ob_start();
        
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>' . htmlspecialchars($data['title']) . '</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 0; 
                    padding: 20px;
                    font-size: 12px;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 30px; 
                    border-bottom: 2px solid #667eea;
                    padding-bottom: 20px;
                }
                .logo { 
                    color: #667eea; 
                    font-size: 24px; 
                    font-weight: bold; 
                    margin-bottom: 10px;
                }
                table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    margin: 20px 0; 
                }
                th, td { 
                    border: 1px solid #ddd; 
                    padding: 8px; 
                    text-align: left; 
                }
                th { 
                    background-color: #f8f9fa; 
                    font-weight: bold;
                }
                .kpi-section {
                    display: flex;
                    justify-content: space-between;
                    margin: 20px 0;
                }
                .kpi-card {
                    background: #f8f9fa;
                    padding: 15px;
                    border-radius: 5px;
                    text-align: center;
                    width: 22%;
                }
                .kpi-number {
                    font-size: 20px;
                    font-weight: bold;
                    color: #667eea;
                }
                .section-title {
                    background: #667eea;
                    color: white;
                    padding: 10px;
                    margin: 20px 0 10px 0;
                    border-radius: 3px;
                }
                .filters-info {
                    background: #e9ecef;
                    padding: 10px;
                    border-radius: 3px;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>';
        
        // Header
        echo '<div class="header">';
        echo '<div class="logo">HCPVision</div>';
        echo '<h1>' . htmlspecialchars($data['title']) . '</h1>';
        echo '<p>Généré le: ' . $this->formatDate(date('Y-m-d H:i:s')) . '</p>';
        echo '<p>Utilisateur: ' . htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']) . '</p>';
        echo '</div>';
        
        // Filters info
        if (!empty(array_filter($filters))) {
            echo '<div class="filters-info">';
            echo '<strong>Filtres appliqués:</strong> ';
            $filterTexts = [];
            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    $filterTexts[] = ucfirst($key) . ': ' . htmlspecialchars($value);
                }
            }
            echo implode(' | ', $filterTexts);
            echo '</div>';
        }
        
        // Generate content based on report type
        $this->generateReportContent($type, $data);
        
        echo '<div style="margin-top: 40px; text-align: center; font-size: 10px; color: #666;">
                <p>Rapport généré par HCPVision - Système de Visualisation des Données d\'Enquêtes</p>
              </div>';
        
        echo '</body></html>';
        
        return ob_get_clean();
    }
    
    /**
     * Generate specific report content based on type
     */
    private function generateReportContent($type, $data) {
        switch ($type) {
            case 'dashboard':
                $this->generateDashboardContent($data);
                break;
                
            case 'enqueteurs':
                $this->generateEnqueteursContent($data);
                break;
                
            case 'regions':
                $this->generateRegionsContent($data);
                break;
                
            default:
                echo '<p>Contenu du rapport en cours de développement.</p>';
        }
    }
    
    /**
     * Generate dashboard content for PDF
     */
    private function generateDashboardContent($data) {
        if (isset($data['kpiData'])) {
            echo '<h2 class="section-title">Indicateurs Clés de Performance</h2>';
            echo '<div class="kpi-section">';
            
            $kpi = $data['kpiData'];
            echo '<div class="kpi-card">';
            echo '<div class="kpi-number">' . $this->formatNumber($kpi['total_enqueteurs'] ?? 0) . '</div>';
            echo '<div>Enquêteurs</div>';
            echo '</div>';
            
            echo '<div class="kpi-card">';
            echo '<div class="kpi-number">' . $this->formatNumber($kpi['total_enquetes'] ?? 0) . '</div>';
            echo '<div>Enquêtes</div>';
            echo '</div>';
            
            echo '<div class="kpi-card">';
            echo '<div class="kpi-number">' . $this->formatNumber($kpi['enquetes_terminees'] ?? 0) . '</div>';
            echo '<div>Terminées</div>';
            echo '</div>';
            
            echo '<div class="kpi-card">';
            echo '<div class="kpi-number">' . $this->formatNumber($kpi['duree_moyenne_globale'] ?? 0, 1) . '</div>';
            echo '<div>Durée Moy. (min)</div>';
            echo '</div>';
            
            echo '</div>';
        }
        
        if (isset($data['topEnqueteurs'])) {
            echo '<h2 class="section-title">Top Enquêteurs</h2>';
            echo '<table>';
            echo '<thead><tr><th>NINV</th><th>Nom</th><th>Nb Enquêtes</th></tr></thead>';
            echo '<tbody>';
            foreach ($data['topEnqueteurs'] as $enqueteur) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($enqueteur['ninv']) . '</td>';
                echo '<td>' . htmlspecialchars($enqueteur['enq_nom']) . '</td>';
                echo '<td>' . $this->formatNumber($enqueteur['nbr_enquetes']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
    }
    
    /**
     * Generate enqueteurs content for PDF
     */
    private function generateEnqueteursContent($data) {
        if (isset($data['enqueteurs'])) {
            echo '<h2 class="section-title">Performance des Enquêteurs</h2>';
            echo '<table>';
            echo '<thead>
                    <tr>
                        <th>NINV</th>
                        <th>Nom</th>
                        <th>Statut</th>
                        <th>Nb Enquêtes</th>
                        <th>Taux Completion</th>
                        <th>Durée Moyenne</th>
                    </tr>
                  </thead>';
            echo '<tbody>';
            foreach ($data['enqueteurs'] as $enqueteur) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($enqueteur['ninv']) . '</td>';
                echo '<td>' . htmlspecialchars($enqueteur['enq_nom']) . '</td>';
                echo '<td>' . htmlspecialchars($enqueteur['statut']) . '</td>';
                echo '<td>' . $this->formatNumber($enqueteur['nbr_enquetes']) . '</td>';
                echo '<td>' . $this->formatPercentage($enqueteur['taux_completion']) . '</td>';
                echo '<td>' . $this->formatNumber($enqueteur['duree_moyenne'], 1) . ' min</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
    }
    
    /**
     * Generate regions content for PDF
     */
    private function generateRegionsContent($data) {
        if (isset($data['regions'])) {
            echo '<h2 class="section-title">Analyse Régionale</h2>';
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
            foreach ($data['regions'] as $region) {
                echo '<tr>';
                echo '<td>Région ' . htmlspecialchars($region['region']) . '</td>';
                echo '<td>' . $this->formatNumber($region['total_enquetes']) . '</td>';
                echo '<td>' . $this->formatNumber($region['enquetes_terminees']) . '</td>';
                echo '<td>' . $this->formatPercentage($region['taux_completion']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
    }
    
    /**
     * Write CSV content based on report type
     */
    private function writeCSVContent($output, $type, $data) {
        switch ($type) {
            case 'enqueteurs':
                if (isset($data['enqueteurs'])) {
                    fputcsv($output, ['NINV', 'Nom', 'Statut', 'Nb Enquêtes', 'Taux Completion', 'Durée Moyenne'], ';');
                    foreach ($data['enqueteurs'] as $enqueteur) {
                        fputcsv($output, [
                            $enqueteur['ninv'],
                            $enqueteur['enq_nom'],
                            $enqueteur['statut'],
                            $enqueteur['nbr_enquetes'],
                            $enqueteur['taux_completion'],
                            $enqueteur['duree_moyenne']
                        ], ';');
                    }
                }
                break;
                
            case 'regions':
                if (isset($data['regions'])) {
                    fputcsv($output, ['Région', 'Total Enquêtes', 'Terminées', 'Taux Completion'], ';');
                    foreach ($data['regions'] as $region) {
                        fputcsv($output, [
                            $region['region'],
                            $region['total_enquetes'],
                            $region['enquetes_terminees'],
                            $region['taux_completion']
                        ], ';');
                    }
                }
                break;
                
            default:
                fputcsv($output, ['Type', 'Valeur'], ';');
                fputcsv($output, ['Rapport', $data['title'] ?? 'Non spécifié'], ';');
                fputcsv($output, ['Date génération', date('Y-m-d H:i:s')], ';');
        }
    }
    
    /**
     * Get recent reports (placeholder for future implementation)
     */
    private function getRecentReports() {
        return []; // Would be implemented with report history tracking
    }
}
?>
