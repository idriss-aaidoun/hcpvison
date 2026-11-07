<?php
/**
 * Authentication and authorization configuration
 */

class Auth {
    private static $user = null;
    
    /**
     * Start session if not already started
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Check if user is authenticated
     */
    public static function check() {
        self::init();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Get current authenticated user
     */
    public static function user() {
        self::init();
        if (self::$user === null && self::check()) {
            $userModel = new User();
            self::$user = $userModel->findById($_SESSION['user_id']);
        }
        return self::$user;
    }
    
    /**
     * Login user
     */
    public static function login($user) {
        self::init();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['nom'];
        self::$user = $user;
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        self::init();
        session_destroy();
        self::$user = null;
    }
    
    /**
     * Check if user is admin
     */
    public static function isAdmin() {
        $user = self::user();
        return $user && $user['role'] === 'admin';
    }
    
    /**
     * Require authentication
     */
    public static function requireAuth() {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }
    
    /**
     * Require admin role
     */
    public static function requireAdmin() {
        self::requireAuth();
        if (!self::isAdmin()) {
            header('HTTP/1.1 403 Forbidden');
            echo "Accès refusé - Droits administrateur requis";
            exit;
        }
    }
}
