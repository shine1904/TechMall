<?php
require_once 'middleware/AdminOnlyMiddleware.php';
require_once 'controllers/ProductController.php';
require_once 'controllers/OrderController.php';
require_once 'controllers/UserController.php';
require_once 'utils/Response.php';

/**
 * Controller chuyên dụng cho Super Admin
 * Chỉ super_admin (role_id = 3) mới có thể truy cập
 */
class SuperAdminController {
    private $adminMiddleware;
    private $productController;
    private $orderController;
    private $userController;
    
    public function __construct() {
        $this->adminMiddleware = new AdminOnlyMiddleware();
        $this->productController = new ProductController();
        $this->orderController = new OrderController();
        $this->userController = new UserController();
    }
    
    /**
     * Lấy danh sách tất cả sản phẩm (Admin view)
     */
    public function getAllProducts($params = [], $user_data = null) {
        // Chỉ super_admin mới được truy cập
        $user_data = $this->adminMiddleware->requireSuperAdmin();
        
        // Gọi method gốc từ ProductController
        return $this->productController->getProducts($params, $user_data);
    }
    
    /**
     * Tạo sản phẩm mới (Admin only)
     */
    public function createProduct($params = [], $user_data = null) {
        $user_data = $this->adminMiddleware->requireSuperAdmin();
        return $this->productController->createProduct($params, $user_data);
    }
    
    /**
     * Cập nhật sản phẩm (Admin only)
     */
    public function updateProduct($params = [], $user_data = null) {
        $user_data = $this->adminMiddleware->requireSuperAdmin();
        return $this->productController->updateProduct($params, $user_data);
    }
    
    /**
     * Xóa sản phẩm (Admin only)
     */
    public function deleteProduct($params = [], $user_data = null) {
        $user_data = $this->adminMiddleware->requireSuperAdmin();
        return $this->productController->deleteProduct($params, $user_data);
    }
    
    /**
     * Lấy tất cả đơn hàng (Admin view)
     */
    public function getAllOrders($params = [], $user_data = null) {
        $user_data = $this->adminMiddleware->requireSuperAdmin();
        return $this->orderController->getOrders($params, $user_data);
    }
    
    /**
     * Cập nhật trạng thái đơn hàng (Admin only)
     */
    public function updateOrderStatus($params = [], $user_data = null) {
        $user_data = $this->adminMiddleware->requireSuperAdmin();
        return $this->orderController->updateOrderStatus($params, $user_data);
    }
    
    /**
     * Xác nhận đơn hàng (Admin only)
     */
    public function confirmOrder($params = [], $user_data = null) {
        $user_data = $this->adminMiddleware->requireSuperAdmin();
        return $this->orderController->confirmOrder($params, $user_data);
    }
    
    /**
     * Admin hủy đơn hàng
     */
    public function adminCancelOrder($params = [], $user_data = null) {
        $user_data = $this->adminMiddleware->requireSuperAdmin();
        return $this->orderController->adminCancelOrder($params, $user_data);
    }
    
    /**
     * Lấy danh sách tất cả người dùng (Admin view)
     */
    public function getAllUsers($params = [], $user_data = null) {
        $user_data = $this->adminMiddleware->requireSuperAdmin();
        return $this->userController->getUsers($params, $user_data);
    }
    
    /**
     * Cập nhật thông tin người dùng (Admin only)
     */
    public function updateUser($params = [], $user_data = null) {
        $user_data = $this->adminMiddleware->requireSuperAdmin();
        return $this->userController->updateUser($params, $user_data);
    }
    
    /**
     * Xóa người dùng (Admin only)
     */
    public function deleteUser($params = [], $user_data = null) {
        $user_data = $this->adminMiddleware->requireSuperAdmin();
        return $this->userController->deleteUser($params, $user_data);
    }
    
    /**
     * Dashboard thống kê tổng quan (Super Admin only)
     */
    public function getDashboard($params = [], $user_data = null) {
        $user_data = $this->adminMiddleware->requireSuperAdmin();
        
        // Logic dashboard cho super admin
        try {
            require_once 'utils/Database.php';
            $db = (new Database())->getConnection();
            
            // Lấy số liệu thống kê
            $stats = [
                'total_users' => $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'],
                'total_products' => $db->query("SELECT COUNT(*) as count FROM product")->fetch()['count'],
                'total_orders' => $db->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'],
                'pending_orders' => $db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch()['count'],
                'total_revenue' => $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status = 'delivered'")->fetch()['total'],
                'recent_orders' => $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC)
            ];
            
            Response::sendSuccess($stats, 'Dashboard data retrieved successfully');
            
        } catch (Exception $e) {
            Response::sendError('Failed to retrieve dashboard data: ' . $e->getMessage());
        }
    }
    
    /**
     * Kiểm tra quyền admin hiện tại
     */
    public function checkAdminAccess($params = [], $user_data = null) {
        $user_data = $this->adminMiddleware->requireSuperAdmin();
        
        Response::sendSuccess([
            'user_id' => $user_data['user_id'],
            'email' => $user_data['email'],
            'roles' => $user_data['roles'],
            'is_super_admin' => true,
            'access_level' => 'full_admin'
        ], 'Super Admin access confirmed');
    }
}