<?php
namespace app\models;

/**
 * User Model - Handles user authentication and management
 */
class User extends Database {
    
    /**
     * Create users table if it doesn't exist (PostgreSQL compatible)
     */
    public function createUsersTable() {
        // Table already exists from setup, just ensure default admin
        $this->createDefaultAdmin();
    }
    
    /**
     * Create default admin user
     */
    private function createDefaultAdmin() {
        $count = $this->selectOne("SELECT COUNT(*) as count FROM users");
        
        if ($count['count'] == 0) {
            $this->create([
                'username' => 'admin',
                'password' => 'admin123',
                'email' => 'admin@hcpvision.ma',
                'full_name' => 'Administrateur Système',
                'role' => 'admin'
            ]);
        }
    }
    
    /**
     * Authenticate user
     */
    public function authenticate($username, $password) {
        $user = $this->selectOne(
            "SELECT * FROM users WHERE username = ? AND active = true",
            [$username]
        );
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login (if you want to track this)
            return $user;
        }
        
        return false;
    }
    
    /**
     * Create new user
     */
    public function create($data) {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return $this->execute(
            "INSERT INTO users (username, password_hash, email, full_name, role) VALUES (?, ?, ?, ?, ?)",
            [
                $data['username'],
                $hashedPassword,
                $data['email'] ?? null,
                $data['full_name'] ?? null,
                $data['role'] ?? 'utilisateur'
            ]
        );
    }
    
    /**
     * Get all users
     */
    public function getAll() {
        return $this->select(
            "SELECT id, username, email, full_name, role, created_at, active FROM users ORDER BY created_at DESC"
        );
    }
    
    /**
     * Get user by ID
     */
    public function getById($id) {
        return $this->selectOne(
            "SELECT id, username, email, full_name, role, created_at, active FROM users WHERE id = ?",
            [$id]
        );
    }
    
    /**
     * Update user
     */
    public function update($id, $data) {
        $sql = "UPDATE users SET ";
        $params = [];
        $updates = [];
        
        if (isset($data['email'])) {
            $updates[] = "email = ?";
            $params[] = $data['email'];
        }
        
        if (isset($data['full_name'])) {
            $updates[] = "full_name = ?";
            $params[] = $data['full_name'];
        }
        
        if (isset($data['role'])) {
            $updates[] = "role = ?";
            $params[] = $data['role'];
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            $updates[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (isset($data['active'])) {
            $updates[] = "active = ?";
            $params[] = $data['active'];
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $sql .= implode(", ", $updates) . " WHERE id = ?";
        $params[] = $id;
        
        return $this->execute($sql, $params);
    }
    
    /**
     * Delete user (soft delete)
     */
    public function delete($id) {
        return $this->execute(
            "UPDATE users SET active = 0 WHERE id = ?",
            [$id]
        );
    }
    
    /**
     * Check if username exists
     */
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = ?";
        $params = [$username];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->selectOne($sql, $params);
        return $result['count'] > 0;
    }
}
?>
