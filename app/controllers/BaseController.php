<?php
namespace app\controllers;

/**
 * Base Controller - Common functionality for all controllers
 */
class BaseController {
    
    protected $user;
    
    public function __construct() {
        // Set user session data
        $this->user = $_SESSION ?? null;
        
        // Initialize models if needed
        $this->initializeModels();
    }
    
    /**
     * Initialize any models that need setup
     */
    protected function initializeModels() {
        // Create users table if it doesn't exist
        $userModel = new \app\models\User();
        $userModel->createUsersTable();
    }
    
    /**
     * Render a view with data
     */
    protected function render($view, $data = []) {
        // Make data available to view
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewPath = ROOT_PATH . '/app/views/' . $view . '.php';
        
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new Exception("View not found: $view");
        }
        
        // Get content and clean buffer
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }
    
    /**
     * Render JSON response
     */
    protected function renderJSON($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Redirect to a URL
     */
    protected function redirect($url, $statusCode = 302) {
        http_response_code($statusCode);
        header("Location: $url");
        exit;
    }
    
    /**
     * Check if user is authenticated
     */
    protected function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }
    
    /**
     * Check if user has admin role
     */
    protected function requireAdmin() {
        $this->requireAuth();
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->renderError('Accès refusé. Privilèges administrateur requis.', 403);
        }
    }
    
    /**
     * Render error page
     */
    protected function renderError($message, $statusCode = 500) {
        http_response_code($statusCode);
        $this->render('error', [
            'message' => $message,
            'statusCode' => $statusCode
        ]);
        exit;
    }
    
    /**
     * Sanitize input data
     */
    protected function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCSRF($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generate CSRF token
     */
    protected function generateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Get POST data safely
     */
    protected function getPost($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        
        return isset($_POST[$key]) ? $this->sanitizeInput($_POST[$key]) : $default;
    }
    
    /**
     * Get GET data safely
     */
    protected function getGet($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        
        return isset($_GET[$key]) ? $this->sanitizeInput($_GET[$key]) : $default;
    }
    
    /**
     * Set flash message
     */
    protected function setFlashMessage($type, $message) {
        $_SESSION['flash_messages'][] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * Get and clear flash messages
     */
    protected function getFlashMessages() {
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }
    
    /**
     * Format number for display
     */
    protected function formatNumber($number, $decimals = 0) {
        return number_format($number, $decimals, ',', ' ');
    }
    
    /**
     * Format percentage
     */
    protected function formatPercentage($number, $decimals = 1) {
        return number_format($number, $decimals, ',', ' ') . '%';
    }
    
    /**
     * Format date for display
     */
    protected function formatDate($date, $format = 'd/m/Y H:i') {
        if (empty($date)) return '-';
        
        $dateObj = new \DateTime($date);
        return $dateObj->format($format);
    }
}
?>
