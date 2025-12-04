<?php
require_once 'utils/Database.php';

class AdminModel {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    
    public function getDashboardStats() {
        $stats = [];
        
        // Total users
        $query = "SELECT COUNT(*) as total_users FROM users WHERE is_active = TRUE";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
        
        // Total products
        $query = "SELECT COUNT(*) as total_products FROM Product WHERE is_active = TRUE";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];
        
        // Total orders
        $query = "SELECT COUNT(*) as total_orders FROM Orders";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];
        
        // Total revenue
        $query = "SELECT SUM(total) as total_revenue FROM Orders WHERE status = 'delivered'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;
        
        // Recent orders
        $query = "SELECT o.*, u.full_name 
                  FROM Orders o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  ORDER BY o.order_date DESC 
                  LIMIT 5";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['recent_orders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Top products
        $query = "SELECT p.product_name, SUM(od.quantity) as total_sold 
                  FROM Order_Details od 
                  JOIN Product p ON od.product_id = p.id 
                  GROUP BY od.product_id 
                  ORDER BY total_sold DESC 
                  LIMIT 5";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['top_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
    
    public function getSalesData($period = 'month') {
        switch ($period) {
            case 'week':
                $interval = '7 DAY';
                break;
            case 'month':
                $interval = '1 MONTH';
                break;
            case 'year':
                $interval = '1 YEAR';
                break;
            default:
                $interval = '1 MONTH';
        }
        
        $query = "SELECT 
                    DATE(order_date) as date,
                    COUNT(*) as order_count,
                    SUM(total) as revenue
                  FROM Orders 
                  WHERE order_date >= DATE_SUB(NOW(), INTERVAL $interval)
                  AND status != 'cancelled'
                  GROUP BY DATE(order_date)
                  ORDER BY date";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUserGrowth() {
        $query = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as user_count
                  FROM users
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                  GROUP BY DATE(created_at)
                  ORDER BY date";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}