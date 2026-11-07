<?php
namespace app\controllers;

use app\models\Enqueteur;
use app\models\Survey;

/**
 * Enqueteur Controller - Manages surveyor data and statistics
 */
class EnqueteurController extends BaseController {
    
    private $enqueteurModel;
    private $surveyModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        
        $this->enqueteurModel = new Enqueteur();
        $this->surveyModel = new Survey();
    }
    
    /**
     * List all enqueteurs with statistics
     */
    public function index() {
        try {
            // Get all enqueteurs with statistics
            $enqueteurs = $this->enqueteurModel->getAll();
            
            // Get performance comparison data
            $performanceComparison = $this->enqueteurModel->getPerformanceComparison();
            
            // Get filters from request
            $filters = [
                'search' => $this->getGet('search'),
                'status' => $this->getGet('status'),
                'min_surveys' => $this->getGet('min_surveys', 0)
            ];
            
            // Apply filters
            if (!empty($filters['search'])) {
                $enqueteurs = array_filter($enqueteurs, function($enqueteur) use ($filters) {
                    return stripos($enqueteur['enq_nom'], $filters['search']) !== false ||
                           stripos($enqueteur['ninv'], $filters['search']) !== false;
                });
            }
            
            if (!empty($filters['status'])) {
                $enqueteurs = array_filter($enqueteurs, function($enqueteur) use ($filters) {
                    return $enqueteur['statut'] === $filters['status'];
                });
            }
            
            if ($filters['min_surveys'] > 0) {
                $enqueteurs = array_filter($enqueteurs, function($enqueteur) use ($filters) {
                    return $enqueteur['nbr_enquetes'] >= $filters['min_surveys'];
                });
            }
            
            $this->render('enqueteurs/index', [
                'enqueteurs' => $enqueteurs,
                'performanceComparison' => $performanceComparison,
                'filters' => $filters,
                'user' => $this->user
            ]);
            
        } catch (Exception $e) {
            error_log("Enqueteurs index error: " . $e->getMessage());
            $this->renderError('Erreur lors du chargement des enquêteurs.');
        }
    }
    
    /**
     * Show detailed view of a specific enqueteur
     */
    public function detail() {
        try {
            $ninv = $this->getGet('ninv');
            
            if (empty($ninv)) {
                $this->renderError('Identifiant enquêteur manquant.', 400);
            }
            
            // Get enqueteur details
            $enqueteur = $this->enqueteurModel->getByNinv($ninv);
            
            if (!$enqueteur) {
                $this->renderError('Enquêteur non trouvé.', 404);
            }
            
            // Get detailed statistics
            $statusBreakdown = $this->enqueteurModel->getStatusBreakdown($ninv);
            $surveysByDate = $this->enqueteurModel->getSurveysByDate($ninv);
            $regionalDistribution = $this->enqueteurModel->getRegionalDistribution($ninv);
            $anomalies = $this->enqueteurModel->getAnomalies($ninv);
            
            // Get recent surveys
            $recentSurveys = $this->surveyModel->getByEnqueteur($ninv);
            $recentSurveys = array_slice($recentSurveys, 0, 20); // Limit to 20 most recent
            
            // Handle comment update (admin only)
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_comment'])) {
                $this->requireAdmin();
                
                $comment = $this->getPost('commentaire');
                
                if ($this->enqueteurModel->updateComment($ninv, $comment)) {
                    $this->setFlashMessage('success', 'Commentaire mis à jour avec succès.');
                } else {
                    $this->setFlashMessage('error', 'Erreur lors de la mise à jour du commentaire.');
                }
                
                $this->redirect("/enqueteurs/detail?ninv=$ninv");
            }
            
            $this->render('enqueteurs/detail', [
                'enqueteur' => $enqueteur,
                'statusBreakdown' => $statusBreakdown,
                'surveysByDate' => $surveysByDate,
                'regionalDistribution' => $regionalDistribution,
                'anomalies' => $anomalies,
                'recentSurveys' => $recentSurveys,
                'flashMessages' => $this->getFlashMessages(),
                'user' => $this->user
            ]);
            
        } catch (Exception $e) {
            error_log("Enqueteur detail error: " . $e->getMessage());
            $this->renderError('Erreur lors du chargement des détails de l\'enquêteur.');
        }
    }
    
    /**
     * API endpoint for enqueteur performance data
     */
    public function apiPerformance() {
        try {
            $performanceStats = $this->enqueteurModel->getPerformanceStats();
            $completionStats = $this->enqueteurModel->getCompletionStats();
            
            $labels = [];
            $enquetes = [];
            $tauxCompletion = [];
            
            // Limit to top 15 for chart readability
            $topPerformers = array_slice($performanceStats, 0, 15);
            
            foreach ($topPerformers as $enqueteur) {
                $labels[] = substr($enqueteur['enq_nom'], 0, 10); // Truncate names
                $enquetes[] = $enqueteur['nbr_enquetes'];
                
                // Find completion rate for this enqueteur
                $completion = array_filter($completionStats, function($item) use ($enqueteur) {
                    return $item['ninv'] === $enqueteur['ninv'];
                });
                
                $tauxCompletion[] = $completion ? reset($completion)['taux_completion'] : 0;
            }
            
            $this->renderJSON([
                'labels' => $labels,
                'enquetes' => $enquetes,
                'taux_completion' => $tauxCompletion
            ]);
            
        } catch (Exception $e) {
            $this->renderJSON(['error' => 'Erreur lors du chargement des données de performance'], 500);
        }
    }
    
    /**
     * API endpoint for enqueteur comparison
     */
    public function apiComparison() {
        try {
            $comparison = $this->enqueteurModel->getPerformanceComparison();
            
            $this->renderJSON($comparison);
            
        } catch (Exception $e) {
            $this->renderJSON(['error' => 'Erreur lors du chargement des données de comparaison'], 500);
        }
    }
    
    /**
     * Update enqueteur status (admin only)
     */
    public function updateStatus() {
        $this->requireAdmin();
        
        try {
            $ninv = $this->getPost('ninv');
            $status = $this->getPost('status');
            
            if (empty($ninv) || empty($status)) {
                $this->renderJSON(['success' => false, 'message' => 'Données manquantes'], 400);
            }
            
            if ($this->enqueteurModel->updateStatus($ninv, $status)) {
                $this->renderJSON(['success' => true, 'message' => 'Statut mis à jour avec succès']);
            } else {
                $this->renderJSON(['success' => false, 'message' => 'Erreur lors de la mise à jour'], 500);
            }
            
        } catch (Exception $e) {
            $this->renderJSON(['success' => false, 'message' => 'Erreur serveur'], 500);
        }
    }
    
    /**
     * Export enqueteurs data
     */
    public function export() {
        $this->requireAuth();
        
        try {
            $format = $this->getGet('format', 'csv');
            $enqueteurs = $this->enqueteurModel->getAll();
            
            if ($format === 'csv') {
                $this->exportToCSV($enqueteurs);
            } else {
                $this->exportToPDF($enqueteurs);
            }
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Erreur lors de l\'export: ' . $e->getMessage());
            $this->redirect('/enqueteurs');
        }
    }
    
    /**
     * Export to CSV
     */
    private function exportToCSV($enqueteurs) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="enqueteurs_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        fputcsv($output, [
            'NINV',
            'Nom',
            'Statut',
            'Nombre Enquêtes',
            'Taux Completion (%)',
            'Durée Moyenne (min)',
            'Commentaire'
        ], ';');
        
        // Data
        foreach ($enqueteurs as $enqueteur) {
            fputcsv($output, [
                $enqueteur['ninv'],
                $enqueteur['enq_nom'],
                $enqueteur['statut'],
                $enqueteur['nbr_enquetes'],
                $this->formatNumber($enqueteur['taux_completion'], 1),
                $this->formatNumber($enqueteur['duree_moyenne'], 1),
                $enqueteur['commentaire'] ?? ''
            ], ';');
        }
        
        fclose($output);
    }
    
    /**
     * Export to PDF
     */
    private function exportToPDF($enqueteurs) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="enqueteurs_' . date('Y-m-d') . '.pdf"');
        
        // Simple HTML to PDF conversion (would need proper PDF library)
        echo $this->generatePDFContent($enqueteurs);
    }
    
    /**
     * Generate PDF content
     */
    private function generatePDFContent($enqueteurs) {
        ob_start();
        
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Rapport Enquêteurs</title>
            <style>
                body { font-family: Arial, sans-serif; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>';
        
        echo '<h1>Rapport des Enquêteurs</h1>';
        echo '<p>Généré le: ' . $this->formatDate(date('Y-m-d H:i:s')) . '</p>';
        
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
        foreach ($enqueteurs as $enqueteur) {
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
        
        echo '</body></html>';
        
        return ob_get_clean();
    }
}
?>
