<?php
namespace app\controllers;

use app\models\Dashboard;
use app\models\Survey;
use app\models\Enqueteur;
use app\models\Region;

/**
 * Dashboard Controller - Main dashboard and KPI display
 */
class DashboardController extends BaseController {
    
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
     * Main dashboard page
     */
    public function index() {
        try {
            // Get main KPI data
            $kpiData = $this->dashboardModel->getKPIData();
            $globalStats = $this->dashboardModel->getGlobalStats();
            
            // Get recent surveys
            $recentSurveys = $this->dashboardModel->getRecentSurveys();
            
            // Get top performing enqueteurs
            $topEnqueteurs = $this->dashboardModel->getTopEnqueteurs(5);
            
            // Get region completion rates
            $regionCompletion = $this->dashboardModel->getRegionCompletion();
            
            // Get collection trends (last 30 days)
            $collectionTrends = $this->dashboardModel->getCollectionTrends();
            
            // Get status distribution
            $statusDistribution = $this->dashboardModel->getStatusDistribution();
            
            // Get productivity metrics
            $productivityMetrics = $this->dashboardModel->getProductivityMetrics();
            
            // Get data quality indicators
            $dataQuality = $this->dashboardModel->getDataQualityIndicators();
            
            // Get flash messages
            $flashMessages = $this->getFlashMessages();
            
            $this->render('dashboard/index', [
                'kpiData' => $kpiData,
                'globalStats' => $globalStats,
                'recentSurveys' => $recentSurveys,
                'topEnqueteurs' => $topEnqueteurs,
                'regionCompletion' => $regionCompletion,
                'collectionTrends' => $collectionTrends,
                'statusDistribution' => $statusDistribution,
                'productivityMetrics' => $productivityMetrics,
                'dataQuality' => $dataQuality,
                'flashMessages' => $flashMessages,
                'user' => $this->user
            ]);
            
        } catch (Exception $e) {
            error_log("Dashboard error: " . $e->getMessage());
            $this->renderError('Erreur lors du chargement du tableau de bord.');
        }
    }
    
    /**
     * API endpoint for KPI data (for AJAX requests)
     */
    public function apiKpi() {
        try {
            $kpiData = $this->dashboardModel->getKPIData();
            $statusDistribution = $this->dashboardModel->getStatusDistribution();
            
            // Format data for charts
            $chartData = [
                'enquetes_terminees' => 0,
                'enquetes_en_cours' => 0,
                'enquetes_partielles' => 0,
                'enquetes_refus' => 0
            ];
            
            foreach ($statusDistribution as $status) {
                switch ($status['statut']) {
                    case 'COMPLETE':
                        $chartData['enquetes_terminees'] = $status['nombre'];
                        break;
                    case 'EN_COURS':
                        $chartData['enquetes_en_cours'] = $status['nombre'];
                        break;
                    case 'PARTIEL':
                        $chartData['enquetes_partielles'] = $status['nombre'];
                        break;
                    case 'REFUSE':
                        $chartData['enquetes_refus'] = $status['nombre'];
                        break;
                }
            }
            
            $this->renderJSON($chartData);
            
        } catch (Exception $e) {
            $this->renderJSON(['error' => 'Erreur lors du chargement des données KPI'], 500);
        }
    }
    
    /**
     * API endpoint for status distribution
     */
    public function apiStatus() {
        try {
            $statusDistribution = $this->dashboardModel->getStatusDistribution();
            
            $labels = [];
            $values = [];
            
            foreach ($statusDistribution as $status) {
                $labels[] = $status['statut'];
                $values[] = $status['nombre'];
            }
            
            $this->renderJSON([
                'labels' => $labels,
                'values' => $values
            ]);
            
        } catch (Exception $e) {
            $this->renderJSON(['error' => 'Erreur lors du chargement des données de statut'], 500);
        }
    }
    
    /**
     * Get collection trends for charts
     */
    public function apiCollectionTrends() {
        try {
            $trends = $this->dashboardModel->getCollectionTrends();
            
            $labels = [];
            $values = [];
            
            foreach ($trends as $trend) {
                $labels[] = $this->formatDate($trend['jour'], 'd/m');
                $values[] = $trend['nbr_enquetes'];
            }
            
            $this->renderJSON([
                'labels' => $labels,
                'values' => $values
            ]);
            
        } catch (Exception $e) {
            $this->renderJSON(['error' => 'Erreur lors du chargement des tendances'], 500);
        }
    }
    
    /**
     * Get completion rate trend
     */
    public function apiCompletionTrend() {
        try {
            $trends = $this->dashboardModel->getCompletionTrend();
            
            $labels = [];
            $completionRates = [];
            $totalSurveys = [];
            
            foreach ($trends as $trend) {
                $labels[] = $this->formatDate($trend['date'], 'd/m');
                $completionRates[] = $trend['completion_rate'];
                $totalSurveys[] = $trend['total'];
            }
            
            $this->renderJSON([
                'labels' => $labels,
                'completion_rates' => $completionRates,
                'total_surveys' => $totalSurveys
            ]);
            
        } catch (Exception $e) {
            $this->renderJSON(['error' => 'Erreur lors du chargement des tendances de completion'], 500);
        }
    }
    
    /**
     * Export dashboard data
     */
    public function export() {
        $this->requireAuth();
        
        try {
            $format = $this->getGet('format', 'pdf');
            
            // Get all dashboard data
            $data = [
                'kpiData' => $this->dashboardModel->getKPIData(),
                'globalStats' => $this->dashboardModel->getGlobalStats(),
                'topEnqueteurs' => $this->dashboardModel->getTopEnqueteurs(10),
                'regionCompletion' => $this->dashboardModel->getRegionCompletion(),
                'statusDistribution' => $this->dashboardModel->getStatusDistribution(),
                'productivityMetrics' => $this->dashboardModel->getProductivityMetrics()
            ];
            
            if ($format === 'pdf') {
                $this->exportToPDF($data, 'dashboard_report');
            } else {
                $this->exportToCSV($data, 'dashboard_data');
            }
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Erreur lors de l\'export: ' . $e->getMessage());
            $this->redirect('/dashboard');
        }
    }
    
    /**
     * Export data to PDF
     */
    private function exportToPDF($data, $filename) {
        // This would require a PDF library like TCPDF
        // For now, we'll create a simple HTML version
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
        
        // Generate HTML content for PDF conversion
        echo $this->generatePDFContent($data);
    }
    
    /**
     * Export data to CSV
     */
    private function exportToCSV($data, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Write CSV headers and data
        fputcsv($output, ['Metric', 'Value']);
        
        if (isset($data['kpiData'])) {
            foreach ($data['kpiData'] as $key => $value) {
                fputcsv($output, [$key, $value]);
            }
        }
        
        fclose($output);
    }
    
    /**
     * Generate HTML content for PDF
     */
    private function generatePDFContent($data) {
        ob_start();
        
        echo '<html><head><title>Rapport Dashboard</title></head><body>';
        echo '<h1>Rapport du Tableau de Bord</h1>';
        echo '<p>Généré le: ' . date('d/m/Y H:i') . '</p>';
        
        if (isset($data['kpiData'])) {
            echo '<h2>Indicateurs Clés</h2>';
            echo '<table border="1">';
            echo '<tr><th>Métrique</th><th>Valeur</th></tr>';
            foreach ($data['kpiData'] as $key => $value) {
                echo '<tr><td>' . htmlspecialchars($key) . '</td><td>' . htmlspecialchars($value) . '</td></tr>';
            }
            echo '</table>';
        }
        
        echo '</body></html>';
        
        return ob_get_clean();
    }
}
?>
