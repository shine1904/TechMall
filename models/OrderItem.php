<?php
require_once 'BaseModel.php';

class OrderItem extends BaseModel {
    protected $table = 'order_items';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Tạo order item mới
     */
    public function createOrderItem($data) {
        try {
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price, total) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['order_id'],
                $data['product_id'],
                $data['quantity'],
                $data['price'],
                $data['total']
            ]);
            
            return $result ? $this->db->lastInsertId() : false;
        } catch (Exception $e) {
            error_log('Error creating order item: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy tất cả items của một đơn hàng
     */
    public function getOrderItems($order_id) {
        try {
            $sql = "SELECT oi.*, p.name as product_name, p.image_url 
                    FROM order_items oi 
                    LEFT JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$order_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error getting order items: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Cập nhật order item
     */
    public function updateOrderItem($id, $data) {
        try {
            $fields = [];
            $values = [];
            
            foreach ($data as $field => $value) {
                $fields[] = "$field = ?";
                $values[] = $value;
            }
            
            $values[] = $id;
            
            $sql = "UPDATE order_items SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (Exception $e) {
            error_log('Error updating order item: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa order item
     */
    public function deleteOrderItem($id) {
        try {
            $sql = "DELETE FROM order_items WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log('Error deleting order item: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa tất cả items của một đơn hàng
     */
    public function deleteOrderItems($order_id) {
        try {
            $sql = "DELETE FROM order_items WHERE order_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$order_id]);
        } catch (Exception $e) {
            error_log('Error deleting order items: ' . $e->getMessage());
            return false;
        }
    }
}
?>
