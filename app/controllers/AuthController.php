<?php
namespace app\controllers;

use app\models\User;

/**
 * Authentication Controller - Handles login/logout
 */
class AuthController extends BaseController {
    
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }
    
    /**
     * Display login form or handle login
     */
    public function login() {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }
        
        $error = null;
        
        // Handle login form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $this->getPost('username');
            $password = $this->getPost('password');
            $csrf_token = $this->getPost('csrf_token');
            
            // Validate CSRF token
            if (!$this->validateCSRF($csrf_token)) {
                $error = 'Token de sécurité invalide.';
            } else if (empty($username) || empty($password)) {
                $error = 'Veuillez saisir votre nom d\'utilisateur et mot de passe.';
            } else {
                // Attempt authentication
                $user = $this->userModel->authenticate($username, $password);
                
                if ($user) {
                    // Set session data
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['full_name'] = $user['full_name'];
                    
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    // Set success message
                    $this->setFlashMessage('success', 'Connexion réussie. Bienvenue !');
                    
                    // Redirect to dashboard
                    $this->redirect('/dashboard');
                } else {
                    $error = 'Nom d\'utilisateur ou mot de passe incorrect.';
                }
            }
        }
        
        // Render login page
        $this->render('auth/login', [
            'error' => $error,
            'csrf_token' => $this->generateCSRF()
        ]);
    }
    
    /**
     * Handle logout
     */
    public function logout() {
        // Destroy session
        session_destroy();
        
        // Start new session for flash message
        session_start();
        $this->setFlashMessage('info', 'Vous avez été déconnecté avec succès.');
        
        // Redirect to login
        $this->redirect('/login');
    }
    
    /**
     * Register new user (admin only)
     */
    public function register() {
        $this->requireAdmin();
        
        $error = null;
        $success = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => $this->getPost('username'),
                'password' => $this->getPost('password'),
                'confirm_password' => $this->getPost('confirm_password'),
                'email' => $this->getPost('email'),
                'full_name' => $this->getPost('full_name'),
                'role' => $this->getPost('role', 'utilisateur')
            ];
            
            // Validate data
            if (empty($data['username']) || empty($data['password'])) {
                $error = 'Nom d\'utilisateur et mot de passe requis.';
            } else if ($data['password'] !== $data['confirm_password']) {
                $error = 'Les mots de passe ne correspondent pas.';
            } else if (strlen($data['password']) < 6) {
                $error = 'Le mot de passe doit contenir au moins 6 caractères.';
            } else if ($this->userModel->usernameExists($data['username'])) {
                $error = 'Ce nom d\'utilisateur existe déjà.';
            } else {
                // Create user
                if ($this->userModel->create($data)) {
                    $success = 'Utilisateur créé avec succès.';
                } else {
                    $error = 'Erreur lors de la création de l\'utilisateur.';
                }
            }
        }
        
        $this->render('auth/register', [
            'error' => $error,
            'success' => $success,
            'csrf_token' => $this->generateCSRF()
        ]);
    }
    
    /**
     * Change password
     */
    public function changePassword() {
        $this->requireAuth();
        
        $error = null;
        $success = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $this->getPost('current_password');
            $newPassword = $this->getPost('new_password');
            $confirmPassword = $this->getPost('confirm_password');
            
            // Get current user
            $user = $this->userModel->getById($_SESSION['user_id']);
            
            if (!password_verify($currentPassword, $user['password'])) {
                $error = 'Mot de passe actuel incorrect.';
            } else if ($newPassword !== $confirmPassword) {
                $error = 'Les nouveaux mots de passe ne correspondent pas.';
            } else if (strlen($newPassword) < 6) {
                $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
            } else {
                // Update password
                if ($this->userModel->update($_SESSION['user_id'], ['password' => $newPassword])) {
                    $success = 'Mot de passe modifié avec succès.';
                } else {
                    $error = 'Erreur lors de la modification du mot de passe.';
                }
            }
        }
        
        $this->render('auth/change-password', [
            'error' => $error,
            'success' => $success,
            'csrf_token' => $this->generateCSRF()
        ]);
    }
}
?>
