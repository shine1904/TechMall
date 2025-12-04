<?php
require_once 'utils/Database.php';

class RBACMiddleware {
    private $db;
    
    public function __construct() {
        try {
            $this->db = (new Database())->getConnection();
            if (!$this->db) {
                throw new Exception("Database connection failed");
            }
        } catch (Exception $e) {
            error_log("RBACMiddleware: " . $e->getMessage());
            throw new Exception("RBACMiddleware initialization failed: " . $e->getMessage());
        }
    }
    
    public function checkPermission($user_id, $required_permission) {
        // Debug logging
        error_log("RBAC: Checking permission '$required_permission' for user_id: $user_id");
        
        // Kiểm tra super admin
        if ($this->isSuperAdmin($user_id)) {
            error_log("RBAC: User $user_id is super admin - permission granted");
            return true;
        }

        // "authenticated" permission: any authenticated user is allowed
        if ($required_permission === 'authenticated') {
            error_log("RBAC: Authenticated permission granted for user_id: $user_id");
            return $user_id !== null;
        }
        
        // Kiểm tra quyền cụ thể
        $query = "SELECT p.permission_name 
                  FROM user_role ur 
                  JOIN role_permission rp ON ur.role_id = rp.role_id 
                  JOIN permission p ON rp.permission_id = p.id 
                  WHERE ur.user_id = :user_id AND p.permission_name = :permission";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':permission', $required_permission);
        $stmt->execute();
        
        $has_permission = $stmt->rowCount() > 0;
        error_log("RBAC: Permission '$required_permission' for user_id $user_id: " . ($has_permission ? 'GRANTED' : 'DENIED'));
        
        if (!$has_permission) {
            // Debug: Let's see what permissions this user actually has
            $debug_query = "SELECT p.permission_name 
                           FROM user_role ur 
                           JOIN role_permission rp ON ur.role_id = rp.role_id 
                           JOIN permission p ON rp.permission_id = p.id 
                           WHERE ur.user_id = :user_id";
            $debug_stmt = $this->db->prepare($debug_query);
            $debug_stmt->bindParam(':user_id', $user_id);
            $debug_stmt->execute();
            $user_permissions = $debug_stmt->fetchAll(PDO::FETCH_COLUMN);
            error_log("RBAC: User $user_id has permissions: " . implode(', ', $user_permissions));
        }
        
        return $has_permission;
    }
    
    public function getUserRoles($user_id) {
        $query = "SELECT r.id, r.role_name, r.role_type 
                  FROM user_role ur 
                  JOIN roles r ON ur.role_id = r.id 
                  WHERE ur.user_id = :user_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function isSuperAdmin($user_id) {
        $query = "SELECT r.role_name 
                  FROM user_role ur 
                  JOIN roles r ON ur.role_id = r.id 
                  WHERE ur.user_id = :user_id AND r.role_name = 'super_admin'";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    public function isAdmin($user_id) {
        $roles = $this->getUserRoles($user_id);
        foreach ($roles as $role) {
            if ($role['role_type'] === 'full_admin' || $role['role_type'] === 'limited_admin') {
                return true;
            }
        }
        return false;
    }
    
    public function isCustomer($user_id) {
        $roles = $this->getUserRoles($user_id);
        foreach ($roles as $role) {
            if ($role['role_type'] === 'customer') {
                return true;
            }
        }
        return false;
    }
}