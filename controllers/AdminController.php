<?php
require_once 'models/AdminModel.php';
require_once 'models/OrderModel.php';
require_once 'middleware/AuthMiddleware.php';
require_once 'middleware/RBACMiddleware.php';
require_once 'utils/Response.php';
require_once 'utils/Database.php';

class AdminController {
    private $adminModel;
    private $orderModel;
    private $authMiddleware;
    private $rbacMiddleware;
    
    public function __construct() {
        $this->adminModel = new AdminModel();
        $this->orderModel = new OrderModel();
        $this->authMiddleware = new AuthMiddleware();
        $this->rbacMiddleware = new RBACMiddleware();
    }
    
    /**
     * Verify if user has admin role
     * GET /api/admin/verify
     */
    public function verifyAdmin() {
        try {
            // Authenticate user
            $payload = $this->authMiddleware->authenticate();
            $user_id = $payload['user_id'];
            
            // Check if user has any admin role
            $isAdmin = $this->rbacMiddleware->isAdmin($user_id);
            $isSuperAdmin = $this->rbacMiddleware->isSuperAdmin($user_id);
            
            if (!$isAdmin && !$isSuperAdmin) {
                Response::sendError('Access denied. Admin role required.', 403);
                return;
            }
            
            // Get user roles for detailed info
            $roles = $this->rbacMiddleware->getUserRoles($user_id);
            $roleNames = array_column($roles, 'role_name');
            
            Response::sendSuccess([
                'user_id' => $user_id,
                'email' => $payload['email'],
                'roles' => $roleNames,
                'is_super_admin' => $isSuperAdmin,
                'is_admin' => $isAdmin,
                'permissions' => $this->getAdminPermissions($user_id)
            ], 'Admin access verified');
            
        } catch (Exception $e) {
            Response::sendError('Authorization failed: ' . $e->getMessage(), 401);
        }
    }

    public function getDashboard($params, $user_data) {
        try {
            $stats = $this->adminModel->getDashboardStats();
            
            Response::sendSuccess($stats);
        } catch (Exception $e) {
            Response::sendError('Error retrieving dashboard data: ' . $e->getMessage());
        }
    }
    
    public function getSalesReport($params, $user_data) {
        try {
            $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
            $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
            
            $report = $this->orderModel->getSalesReport($start_date, $end_date);
            
            Response::sendSuccess($report);
        } catch (Exception $e) {
            Response::sendError('Error retrieving sales report: ' . $e->getMessage());
        }
    }
    
    public function getSalesData($params, $user_data) {
        try {
            $period = isset($_GET['period']) ? $_GET['period'] : 'month';
            
            $salesData = $this->adminModel->getSalesData($period);
            
            Response::sendSuccess($salesData);
        } catch (Exception $e) {
            Response::sendError('Error retrieving sales data: ' . $e->getMessage());
        }
    }
    
    public function getUserGrowth($params, $user_data) {
        try {
            $userGrowth = $this->adminModel->getUserGrowth();
            
            Response::sendSuccess($userGrowth);
        } catch (Exception $e) {
            Response::sendError('Error retrieving user growth data: ' . $e->getMessage());
        }
    }
    
    /**
     * Get admin permissions for user
     */
    private function getAdminPermissions($user_id) {
        try {
            $db = (new Database())->getConnection();
            
            $query = "SELECT DISTINCT p.permission_name, p.permission_group 
                      FROM user_role ur 
                      JOIN role_permission rp ON ur.role_id = rp.role_id 
                      JOIN permission p ON rp.permission_id = p.id 
                      WHERE ur.user_id = :user_id 
                      ORDER BY p.permission_group, p.permission_name";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }
}
?>