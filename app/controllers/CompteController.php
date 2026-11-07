<?php
namespace app\controllers;

use app\models\User;

/**
 * Compte Controller - Manages user account settings and profile
 */
class CompteController extends BaseController {
    
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        
        $this->userModel = new User();
    }
    
    /**
     * Display user profile and account settings
     */
    public function index() {
        try {
            // Get current user data
            $currentUser = $this->userModel->getById($_SESSION['user_id']);
            
            if (!$currentUser) {
                $this->renderError('Utilisateur non trouvé.', 404);
                return;
            }
            
            // Get all users (admin only)
            $allUsers = [];
            if ($_SESSION['user_role'] === 'admin') {
                $allUsers = $this->userModel->getAll();
            }
            
            // Get flash messages
            $flashMessages = $this->getFlashMessages();
            
            $this->render('comptes/index', [
                'currentUser' => $currentUser,
                'allUsers' => $allUsers,
                'flashMessages' => $flashMessages,
                'user' => $this->user
            ]);
            
        } catch (Exception $e) {
            error_log("Account index error: " . $e->getMessage());
            $this->renderError('Erreur lors du chargement du profil utilisateur.');
        }
    }
    
    /**
     * Update user profile
     */
    public function update() {
        try {
            $action = $this->getPost('action');
            
            switch ($action) {
                case 'update_profile':
                    $this->updateProfile();
                    break;
                    
                case 'change_password':
                    $this->changePassword();
                    break;
                    
                case 'create_user':
                    $this->requireAdmin();
                    $this->createUser();
                    break;
                    
                case 'update_user':
                    $this->requireAdmin();
                    $this->updateUser();
                    break;
                    
                case 'delete_user':
                    $this->requireAdmin();
                    $this->deleteUser();
                    break;
                    
                default:
                    $this->renderJSON(['success' => false, 'message' => 'Action non reconnue'], 400);
                    return;
            }
            
        } catch (Exception $e) {
            error_log("Account update error: " . $e->getMessage());
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjaxRequest()) {
                $this->renderJSON(['success' => false, 'message' => 'Erreur serveur'], 500);
            } else {
                $this->setFlashMessage('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
                $this->redirect('/comptes');
            }
        }
    }
    
    /**
     * Update current user profile
     */
    private function updateProfile() {
        $data = [
            'email' => $this->getPost('email'),
            'full_name' => $this->getPost('full_name')
        ];
        
        // Validate data
        if (empty($data['full_name'])) {
            throw new Exception('Le nom complet est requis.');
        }
        
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format d\'email invalide.');
        }
        
        // Update user
        if ($this->userModel->update($_SESSION['user_id'], $data)) {
            // Update session data
            $_SESSION['full_name'] = $data['full_name'];
            
            if ($this->isAjaxRequest()) {
                $this->renderJSON(['success' => true, 'message' => 'Profil mis à jour avec succès']);
            } else {
                $this->setFlashMessage('success', 'Profil mis à jour avec succès.');
                $this->redirect('/comptes');
            }
        } else {
            throw new Exception('Erreur lors de la mise à jour du profil.');
        }
    }
    
    /**
     * Change current user password
     */
    private function changePassword() {
        $currentPassword = $this->getPost('current_password');
        $newPassword = $this->getPost('new_password');
        $confirmPassword = $this->getPost('confirm_password');
        
        // Validate passwords
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            throw new Exception('Tous les champs mot de passe sont requis.');
        }
        
        if ($newPassword !== $confirmPassword) {
            throw new Exception('Les nouveaux mots de passe ne correspondent pas.');
        }
        
        if (strlen($newPassword) < 6) {
            throw new Exception('Le nouveau mot de passe doit contenir au moins 6 caractères.');
        }
        
        // Get current user to verify password
        $currentUser = $this->userModel->getById($_SESSION['user_id']);
        
        if (!password_verify($currentPassword, $currentUser['password'])) {
            throw new Exception('Mot de passe actuel incorrect.');
        }
        
        // Update password
        if ($this->userModel->update($_SESSION['user_id'], ['password' => $newPassword])) {
            if ($this->isAjaxRequest()) {
                $this->renderJSON(['success' => true, 'message' => 'Mot de passe modifié avec succès']);
            } else {
                $this->setFlashMessage('success', 'Mot de passe modifié avec succès.');
                $this->redirect('/comptes');
            }
        } else {
            throw new Exception('Erreur lors de la modification du mot de passe.');
        }
    }
    
    /**
     * Create new user (admin only)
     */
    private function createUser() {
        $data = [
            'username' => $this->getPost('username'),
            'password' => $this->getPost('password'),
            'confirm_password' => $this->getPost('confirm_password'),
            'email' => $this->getPost('email'),
            'full_name' => $this->getPost('full_name'),
            'role' => $this->getPost('role', 'utilisateur')
        ];
        
        // Validate data
        if (empty($data['username']) || empty($data['password']) || empty($data['full_name'])) {
            throw new Exception('Nom d\'utilisateur, mot de passe et nom complet sont requis.');
        }
        
        if ($data['password'] !== $data['confirm_password']) {
            throw new Exception('Les mots de passe ne correspondent pas.');
        }
        
        if (strlen($data['password']) < 6) {
            throw new Exception('Le mot de passe doit contenir au moins 6 caractères.');
        }
        
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format d\'email invalide.');
        }
        
        if ($this->userModel->usernameExists($data['username'])) {
            throw new Exception('Ce nom d\'utilisateur existe déjà.');
        }
        
        if (!in_array($data['role'], ['admin', 'utilisateur'])) {
            throw new Exception('Rôle invalide.');
        }
        
        // Create user
        if ($this->userModel->create($data)) {
            if ($this->isAjaxRequest()) {
                $this->renderJSON(['success' => true, 'message' => 'Utilisateur créé avec succès']);
            } else {
                $this->setFlashMessage('success', 'Utilisateur créé avec succès.');
                $this->redirect('/comptes');
            }
        } else {
            throw new Exception('Erreur lors de la création de l\'utilisateur.');
        }
    }
    
    /**
     * Update existing user (admin only)
     */
    private function updateUser() {
        $userId = $this->getPost('user_id');
        $data = [
            'email' => $this->getPost('email'),
            'full_name' => $this->getPost('full_name'),
            'role' => $this->getPost('role'),
            'active' => $this->getPost('active', 1)
        ];
        
        // Add password if provided
        $newPassword = $this->getPost('password');
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 6) {
                throw new Exception('Le mot de passe doit contenir au moins 6 caractères.');
            }
            $data['password'] = $newPassword;
        }
        
        // Validate data
        if (empty($userId) || empty($data['full_name'])) {
            throw new Exception('ID utilisateur et nom complet sont requis.');
        }
        
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format d\'email invalide.');
        }
        
        if (!in_array($data['role'], ['admin', 'utilisateur'])) {
            throw new Exception('Rôle invalide.');
        }
        
        // Prevent admin from deactivating themselves
        if ($userId == $_SESSION['user_id'] && $data['active'] == 0) {
            throw new Exception('Vous ne pouvez pas désactiver votre propre compte.');
        }
        
        // Update user
        if ($this->userModel->update($userId, $data)) {
            if ($this->isAjaxRequest()) {
                $this->renderJSON(['success' => true, 'message' => 'Utilisateur mis à jour avec succès']);
            } else {
                $this->setFlashMessage('success', 'Utilisateur mis à jour avec succès.');
                $this->redirect('/comptes');
            }
        } else {
            throw new Exception('Erreur lors de la mise à jour de l\'utilisateur.');
        }
    }
    
    /**
     * Delete user (admin only)
     */
    private function deleteUser() {
        $userId = $this->getPost('user_id');
        
        if (empty($userId)) {
            throw new Exception('ID utilisateur requis.');
        }
        
        // Prevent admin from deleting themselves
        if ($userId == $_SESSION['user_id']) {
            throw new Exception('Vous ne pouvez pas supprimer votre propre compte.');
        }
        
        // Soft delete user
        if ($this->userModel->delete($userId)) {
            if ($this->isAjaxRequest()) {
                $this->renderJSON(['success' => true, 'message' => 'Utilisateur supprimé avec succès']);
            } else {
                $this->setFlashMessage('success', 'Utilisateur supprimé avec succès.');
                $this->redirect('/comptes');
            }
        } else {
            throw new Exception('Erreur lors de la suppression de l\'utilisateur.');
        }
    }
    
    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * Get user activity log (placeholder for future implementation)
     */
    public function activity() {
        $this->requireAuth();
        
        // This would show user activity history
        $activities = []; // Placeholder
        
        $this->renderJSON($activities);
    }
    
    /**
     * Export user data (admin only)
     */
    public function export() {
        $this->requireAdmin();
        
        try {
            $format = $this->getGet('format', 'csv');
            $users = $this->userModel->getAll();
            
            if ($format === 'csv') {
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="utilisateurs_' . date('Y-m-d') . '.csv"');
                
                $output = fopen('php://output', 'w');
                
                // Add BOM for UTF-8
                fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // Headers
                fputcsv($output, [
                    'ID',
                    'Nom d\'utilisateur',
                    'Email',
                    'Nom complet',
                    'Rôle',
                    'Actif',
                    'Date création'
                ], ';');
                
                // Data
                foreach ($users as $user) {
                    fputcsv($output, [
                        $user['id'],
                        $user['username'],
                        $user['email'],
                        $user['full_name'],
                        $user['role'],
                        $user['active'] ? 'Oui' : 'Non',
                        $this->formatDate($user['created_at'])
                    ], ';');
                }
                
                fclose($output);
            } else {
                $this->renderError('Format d\'export non supporté.', 400);
            }
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', 'Erreur lors de l\'export: ' . $e->getMessage());
            $this->redirect('/comptes');
        }
    }
}
?>
