<?php
require_once 'utils/Response.php';
require_once 'middleware/AuthMiddleware.php';
require_once 'middleware/RBACMiddleware.php';

/**
 * Middleware để đảm bảo chỉ super_admin mới có thể truy cập
 * Sử dụng cho các trang/API quản trị nhạy cảm
 */
class AdminOnlyMiddleware {
    private $authMiddleware;
    private $rbacMiddleware;
    
    public function __construct() {
        $this->authMiddleware = new AuthMiddleware();
        $this->rbacMiddleware = new RBACMiddleware();
    }
    
    /**
     * Kiểm tra user có phải super_admin không
     * @return array User data nếu là super_admin
     * @throws Exception nếu không có quyền
     */
    public function requireSuperAdmin() {
        try {
            // 1. Xác thực token
            $user_data = $this->authMiddleware->authenticate();
            
            if (!$user_data || !isset($user_data['user_id'])) {
                throw new Exception('Authentication required', 401);
            }
            
            // 2. Kiểm tra có phải super_admin không
            if (!$this->rbacMiddleware->isSuperAdmin($user_data['user_id'])) {
                throw new Exception('Admin access required. Only super_admin can access this resource.', 403);
            }
            
            return $user_data;
            
        } catch (Exception $e) {
            // Re-throw với code phù hợp
            if ($e->getCode() == 401) {
                Response::sendError('Unauthorized: ' . $e->getMessage(), 401);
            } else {
                Response::sendError('Forbidden: ' . $e->getMessage(), 403);
            }
        }
    }
    
    /**
     * Kiểm tra user có phải admin (bao gồm super_admin và các loại admin khác)
     * @return array User data nếu là admin
     * @throws Exception nếu không có quyền
     */
    public function requireAnyAdmin() {
        try {
            // 1. Xác thực token
            $user_data = $this->authMiddleware->authenticate();
            
            if (!$user_data || !isset($user_data['user_id'])) {
                throw new Exception('Authentication required', 401);
            }
            
            // 2. Kiểm tra có phải admin không (super_admin hoặc các admin khác)
            if (!$this->rbacMiddleware->isAdmin($user_data['user_id']) && 
                !$this->rbacMiddleware->isSuperAdmin($user_data['user_id'])) {
                throw new Exception('Admin access required', 403);
            }
            
            return $user_data;
            
        } catch (Exception $e) {
            // Re-throw với code phù hợp
            if ($e->getCode() == 401) {
                Response::sendError('Unauthorized: ' . $e->getMessage(), 401);
            } else {
                Response::sendError('Forbidden: ' . $e->getMessage(), 403);
            }
        }
    }
    
    /**
     * Kiểm tra user có quyền cụ thể hoặc là super_admin
     * @param string $required_permission Quyền cần thiết
     * @return array User data nếu có quyền
     */
    public function requirePermissionOrSuperAdmin($required_permission) {
        try {
            // 1. Xác thực token
            $user_data = $this->authMiddleware->authenticate();
            
            if (!$user_data || !isset($user_data['user_id'])) {
                throw new Exception('Authentication required', 401);
            }
            
            $user_id = $user_data['user_id'];
            
            // 2. Super admin bypass tất cả
            if ($this->rbacMiddleware->isSuperAdmin($user_id)) {
                return $user_data;
            }
            
            // 3. Kiểm tra quyền cụ thể
            if (!$this->rbacMiddleware->checkPermission($user_id, $required_permission)) {
                throw new Exception("Permission required: {$required_permission}", 403);
            }
            
            return $user_data;
            
        } catch (Exception $e) {
            if ($e->getCode() == 401) {
                Response::sendError('Unauthorized: ' . $e->getMessage(), 401);
            } else {
                Response::sendError('Forbidden: ' . $e->getMessage(), 403);
            }
        }
    }
}