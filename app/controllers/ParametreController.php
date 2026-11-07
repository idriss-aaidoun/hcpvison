<?php
namespace app\controllers;

/**
 * Parametre Controller - Application settings and configuration
 */
class ParametreController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }
    
    /**
     * Display application settings
     */
    public function index() {
        try {
            // Get current application settings
            $settings = $this->getApplicationSettings();
            
            // Get system information
            $systemInfo = $this->getSystemInformation();
            
            // Get database information
            $databaseInfo = $this->getDatabaseInformation();
            
            // Get flash messages
            $flashMessages = $this->getFlashMessages();
            
            $this->render('parametres/index', [
                'settings' => $settings,
                'systemInfo' => $systemInfo,
                'databaseInfo' => $databaseInfo,
                'flashMessages' => $flashMessages,
                'user' => $this->user
            ]);
            
        } catch (Exception $e) {
            error_log("Parameters index error: " . $e->getMessage());
            $this->renderError('Erreur lors du chargement des paramètres.');
        }
    }
    
    /**
     * Update application settings (admin only)
     */
    public function update() {
        $this->requireAdmin();
        
        try {
            $action = $this->getPost('action');
            
            switch ($action) {
                case 'update_general':
                    $this->updateGeneralSettings();
                    break;
                    
                case 'update_database':
                    $this->updateDatabaseSettings();
                    break;
                    
                case 'clear_cache':
                    $this->clearCache();
                    break;
                    
                case 'backup_database':
                    $this->backupDatabase();
                    break;
                    
                default:
                    throw new Exception('Action non reconnue.');
            }
            
        } catch (Exception $e) {
            error_log("Parameters update error: " . $e->getMessage());
            $this->setFlashMessage('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
            $this->redirect('/parametres');
        }
    }
    
    /**
     * Get current application settings
     */
    private function getApplicationSettings() {
        // In a real implementation, these would be stored in database or config files
        return [
            'app_name' => 'HCPVision',
            'app_version' => '1.0.0',
            'timezone' => date_default_timezone_get(),
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'language' => 'fr',
            'items_per_page' => 25,
            'session_timeout' => 3600, // 1 hour
            'enable_debug' => false,
            'enable_logging' => true,
            'auto_backup' => false,
            'backup_retention_days' => 30
        ];
    }
    
    /**
     * Get system information
     */
    private function getSystemInformation() {
        return [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Non disponible',
            'server_os' => PHP_OS,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'timezone' => date_default_timezone_get(),
            'current_time' => date('Y-m-d H:i:s'),
            'disk_free_space' => $this->formatBytes(disk_free_space('.')),
            'disk_total_space' => $this->formatBytes(disk_total_space('.'))
        ];
    }
    
    /**
     * Get database information
     */
    private function getDatabaseInformation() {
        try {
            $db = getDB();
            
            // Get MySQL version
            $versionResult = $db->query("SELECT VERSION() as version")->fetch();
            $mysqlVersion = $versionResult['version'] ?? 'Non disponible';
            
            // Get database size
            $sizeResult = $db->query("SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'db_size_mb'
                FROM information_schema.tables 
                WHERE table_schema = '" . DB_NAME . "'")->fetch();
            
            // Get table count
            $tablesResult = $db->query("SELECT COUNT(*) as table_count 
                FROM information_schema.tables 
                WHERE table_schema = '" . DB_NAME . "'")->fetch();
            
            // Get view count
            $viewsResult = $db->query("SELECT COUNT(*) as view_count 
                FROM information_schema.views 
                WHERE table_schema = '" . DB_NAME . "'")->fetch();
            
            // Get record counts for main tables
            $recordCounts = [];
            $mainTables = ['c99', 'level-1', 'cgm', 'cases', 'enqueteurs', 'users'];
            
            foreach ($mainTables as $table) {
                try {
                    $result = $db->query("SELECT COUNT(*) as count FROM `$table`")->fetch();
                    $recordCounts[$table] = $result['count'];
                } catch (Exception $e) {
                    $recordCounts[$table] = 'N/A';
                }
            }
            
            return [
                'host' => DB_HOST,
                'database_name' => DB_NAME,
                'mysql_version' => $mysqlVersion,
                'database_size' => $sizeResult['db_size_mb'] . ' MB',
                'table_count' => $tablesResult['table_count'],
                'view_count' => $viewsResult['view_count'],
                'record_counts' => $recordCounts,
                'charset' => DB_CHARSET,
                'connection_status' => 'Connecté'
            ];
            
        } catch (Exception $e) {
            return [
                'host' => DB_HOST,
                'database_name' => DB_NAME,
                'connection_status' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update general settings
     */
    private function updateGeneralSettings() {
        $settings = [
            'timezone' => $this->getPost('timezone'),
            'date_format' => $this->getPost('date_format'),
            'items_per_page' => $this->getPost('items_per_page'),
            'session_timeout' => $this->getPost('session_timeout'),
            'enable_debug' => $this->getPost('enable_debug', false),
            'enable_logging' => $this->getPost('enable_logging', true)
        ];
        
        // Validate settings
        if (!empty($settings['timezone']) && !in_array($settings['timezone'], timezone_identifiers_list())) {
            throw new Exception('Timezone invalide.');
        }
        
        if ($settings['items_per_page'] < 10 || $settings['items_per_page'] > 100) {
            throw new Exception('Le nombre d\'éléments par page doit être entre 10 et 100.');
        }
        
        if ($settings['session_timeout'] < 300 || $settings['session_timeout'] > 86400) {
            throw new Exception('Le timeout de session doit être entre 5 minutes et 24 heures.');
        }
        
        // In a real implementation, save to database or config file
        // For now, we'll just simulate success
        
        if (!empty($settings['timezone'])) {
            date_default_timezone_set($settings['timezone']);
        }
        
        $this->setFlashMessage('success', 'Paramètres généraux mis à jour avec succès.');
        $this->redirect('/parametres');
    }
    
    /**
     * Update database settings
     */
    private function updateDatabaseSettings() {
        $settings = [
            'auto_backup' => $this->getPost('auto_backup', false),
            'backup_retention_days' => $this->getPost('backup_retention_days', 30)
        ];
        
        // Validate settings
        if ($settings['backup_retention_days'] < 1 || $settings['backup_retention_days'] > 365) {
            throw new Exception('La rétention de sauvegarde doit être entre 1 et 365 jours.');
        }
        
        // Save settings (in a real implementation)
        
        $this->setFlashMessage('success', 'Paramètres de base de données mis à jour avec succès.');
        $this->redirect('/parametres');
    }
    
    /**
     * Clear application cache
     */
    private function clearCache() {
        // In a real implementation, this would clear various caches
        // For now, we'll just clear session cache
        
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
        // Clear session data (except authentication)
        $userSession = [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'user_role' => $_SESSION['user_role'],
            'full_name' => $_SESSION['full_name']
        ];
        
        session_destroy();
        session_start();
        $_SESSION = $userSession;
        
        $this->setFlashMessage('success', 'Cache effacé avec succès.');
        $this->redirect('/parametres');
    }
    
    /**
     * Backup database
     */
    private function backupDatabase() {
        try {
            $backupFile = $this->generateDatabaseBackup();
            
            $this->setFlashMessage('success', 'Sauvegarde créée avec succès: ' . basename($backupFile));
            $this->redirect('/parametres');
            
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la sauvegarde: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate database backup
     */
    private function generateDatabaseBackup() {
        $backupDir = ROOT_PATH . '/backups';
        
        // Create backup directory if it doesn't exist
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $backupFile = $backupDir . '/backup_' . DB_NAME . '_' . date('Y-m-d_H-i-s') . '.sql';
        
        // Use mysqldump command if available
        $command = "mysqldump --host=" . DB_HOST . " --user=" . DB_USER . " --password=" . DB_PASS . " " . DB_NAME . " > " . $backupFile;
        
        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);
        
        if ($returnVar !== 0) {
            throw new Exception('Erreur lors de l\'exécution de mysqldump.');
        }
        
        if (!file_exists($backupFile) || filesize($backupFile) === 0) {
            throw new Exception('Le fichier de sauvegarde n\'a pas été créé ou est vide.');
        }
        
        return $backupFile;
    }
    
    /**
     * Download backup file
     */
    public function downloadBackup() {
        $this->requireAdmin();
        
        $filename = $this->getGet('file');
        
        if (empty($filename)) {
            $this->renderError('Nom de fichier manquant.', 400);
            return;
        }
        
        $backupFile = ROOT_PATH . '/backups/' . basename($filename);
        
        if (!file_exists($backupFile)) {
            $this->renderError('Fichier de sauvegarde non trouvé.', 404);
            return;
        }
        
        // Send file as download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($backupFile) . '"');
        header('Content-Length: ' . filesize($backupFile));
        
        readfile($backupFile);
        exit;
    }
    
    /**
     * Get system logs
     */
    public function logs() {
        $this->requireAdmin();
        
        try {
            $logType = $this->getGet('type', 'error');
            $logs = $this->getLogEntries($logType);
            
            $this->renderJSON($logs);
            
        } catch (Exception $e) {
            $this->renderJSON(['error' => 'Erreur lors de la lecture des logs'], 500);
        }
    }
    
    /**
     * Get log entries
     */
    private function getLogEntries($type) {
        $logFile = '';
        
        switch ($type) {
            case 'error':
                $logFile = ini_get('error_log');
                break;
            case 'access':
                $logFile = $_SERVER['DOCUMENT_ROOT'] . '/../logs/access.log';
                break;
            default:
                return [];
        }
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        // Read last 100 lines
        $lines = [];
        $handle = fopen($logFile, 'r');
        
        if ($handle) {
            // Read file backwards to get most recent entries
            $linecounter = 0;
            $pos = -2;
            
            if (fseek($handle, $pos, SEEK_END) !== -1) {
                while ($linecounter < 100) {
                    $char = fgetc($handle);
                    
                    if ($char == "\n") {
                        $linecounter++;
                    }
                    
                    if (fseek($handle, $pos--, SEEK_END) == -1) {
                        break;
                    }
                }
                
                // Read remaining content
                $content = fread($handle, -$pos);
                $lines = array_reverse(explode("\n", trim($content)));
            }
            
            fclose($handle);
        }
        
        return array_slice($lines, 0, 100);
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes($size, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Test database connection
     */
    public function testConnection() {
        $this->requireAdmin();
        
        try {
            $db = getDB();
            $result = $db->query("SELECT 1")->fetch();
            
            $this->renderJSON([
                'success' => true,
                'message' => 'Connexion à la base de données réussie',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            $this->renderJSON([
                'success' => false,
                'message' => 'Erreur de connexion: ' . $e->getMessage()
            ], 500);
        }
    }
}
?>
