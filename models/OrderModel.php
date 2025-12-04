<?php
require_once 'utils/Database.php';

class OrderModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // ================= TẠO ĐƠN HÀNG =================
    public function createOrder($userId, $data) {
        $items = $data['items'];
        $payment_method = $data['payment_method'] ?? null;
        $total = $data['total'] ?? 0;

        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare("
    INSERT INTO orders (
        user_id, fullname, email, phone, address, total,
        payment_method, payment_status, status, created_at
    ) VALUES (
        :user_id, :fullname, :email, :phone, :address, :total,
        :payment_method, 'pending', 'pending', NOW()
    )
");
$stmt->execute([
    ':user_id' => $userId,
    ':fullname' => $data['fullname'] ?? '',
    ':email' => $data['email'] ?? '',
    ':phone' => $data['phone'] ?? '',
    ':address' => $data['address'] ?? '',
    ':total' => $total,
    ':payment_method' => $payment_method
            ]);
            $orderId = $this->conn->lastInsertId();

            $stmtItem = $this->conn->prepare("
                INSERT INTO order_details (order_id, product_id, price, quantity)
                VALUES (:order_id, :product_id, :price, :quantity)
            ");
            foreach ($items as $it) {
                $stmtItem->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $it['product_id'],
                    ':price' => $it['price'],
                    ':quantity' => $it['quantity']
                ]);
            }

            $this->conn->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    // ================= LẤY CHI TIẾT ĐƠN HÀNG THEO ID =================
    public function getOrderById($orderId, $userId = null) {
        $query = "SELECT id, user_id, fullname, email, phone, address, total,
                         payment_method, payment_status, status, paypal_transaction_id,
                         momo_transaction_id, created_at
                  FROM orders
                  WHERE id = :id";
        $params = [':id' => $orderId];
        if ($userId !== null) {
            $query .= " AND user_id = :user_id";
            $params[':user_id'] = $userId;
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) return null;

        $stmt2 = $this->conn->prepare("SELECT product_id, price, quantity FROM order_details WHERE order_id = :order_id");
        $stmt2->execute([':order_id' => $orderId]);
        $order['items'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return $order;
    }

    // ================= LẤY DANH SÁCH ĐƠN =================
    public function getAllOrders() {
        $stmt = $this->conn->query("
            SELECT id, fullname, phone, address, payment_method,
                   payment_status, status, total, created_at
            FROM orders ORDER BY created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrders($filters) {
        $query = "SELECT id, fullname, phone, address, payment_method,
                         payment_status, status, total, created_at
                  FROM orders WHERE 1=1";
        $params = [];
        if (!empty($filters['user_id'])) {
            $query .= " AND user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        $query .= " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ================= USER HỦY =================
   public function userCancelOrder($orderId, $userId) {
    // Lấy thông tin đơn hàng
    $stmt = $this->conn->prepare("
        SELECT payment_method, created_at, payment_status
        FROM orders 
        WHERE id = :order_id AND user_id = :user_id
    ");
    $stmt->execute([
        ':order_id' => $orderId,
        ':user_id' => $userId
    ]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        return false;
    }

    // Kiểm tra thời gian
    $orderTime = strtotime($order['created_at']);
    $currentTime = time();
    $hourDiff = ($currentTime - $orderTime) / 3600; // Chuyển đổi thành giờ

    // Nếu quá 1 tiếng
    if ($hourDiff > 1) {
        return false;
    }

    // Logic khác nhau cho COD và PayPal
    if ($order['payment_method'] === 'cod') {
        // COD: Có thể hủy trực tiếp trong 1 giờ (bất kể trạng thái thanh toán)
        $stmt = $this->conn->prepare("
            UPDATE orders
            SET status = 'cancelled', 
                payment_status = 'failed', 
                updated_at = NOW()
            WHERE id = :order_id 
            AND user_id = :user_id
        ");
        return $stmt->execute([
            ':order_id' => $orderId,
            ':user_id' => $userId
        ]);
    } else {
        // PayPal: Chỉ hủy được khi chưa thanh toán
        $stmt = $this->conn->prepare("
            UPDATE orders
            SET status = 'cancelled', 
                payment_status = 'failed', 
                updated_at = NOW()
            WHERE id = :order_id 
            AND user_id = :user_id
            AND payment_status = 'pending'
        ");
        return $stmt->execute([
            ':order_id' => $orderId,
            ':user_id' => $userId
        ]);
    }
}

    public function requestCancel($orderId, $userId) {
        // Lấy thông tin đơn hàng để kiểm tra thời gian và phương thức thanh toán
        $stmt = $this->conn->prepare("
            SELECT payment_method, created_at, payment_status
            FROM orders 
            WHERE id = :order_id AND user_id = :user_id
        ");
        $stmt->execute([
            ':order_id' => $orderId,
            ':user_id' => $userId
        ]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return false;
        }

        // Kiểm tra thời gian (chỉ cho phép trong 1 giờ)
        $orderTime = strtotime($order['created_at']);
        $currentTime = time();
        $hourDiff = ($currentTime - $orderTime) / 3600;

        if ($hourDiff > 1) {
            return false;
        }

        // Chỉ PayPal mới được gửi yêu cầu hủy khi đã thanh toán
        if ($order['payment_method'] !== 'paypal' || $order['payment_status'] !== 'paid') {
            return false;
        }

        $stmt = $this->conn->prepare("
            UPDATE orders
            SET status = 'cancel_request', updated_at = NOW()
            WHERE id = :order_id AND user_id = :user_id AND payment_status = 'paid'
        ");
        return $stmt->execute([
            ':order_id' => $orderId,
            ':user_id' => $userId
        ]);
    }

    // ================= ADMIN HỦY =================
    public function adminCancelOrder($orderId) {
        $stmt = $this->conn->prepare("
            UPDATE orders
            SET status = 'cancelled', payment_status = 'failed', updated_at = NOW()
            WHERE id = :order_id AND status = 'cancel_request' AND payment_status = 'paid'
        ");
        return $stmt->execute([':order_id' => $orderId]);
    }

    // ================= ADMIN XÁC NHẬN ĐƠN HÀNG =================
    public function confirmOrderByAdmin($orderId) {
        $stmt = $this->conn->prepare("
            UPDATE orders
            SET status = 'confirmed', updated_at = NOW()
            WHERE id = :order_id AND status = 'pending'
        ");
        return $stmt->execute([':order_id' => $orderId]);
    }

    // ================= USER CẬP NHẬT SHIPPING =================
  
public function updateShippingInfo($orderId, $userId, $data) {
    // Kiểm tra thông tin đơn hàng
    $stmt = $this->conn->prepare("
        SELECT payment_method, payment_status, created_at, status
        FROM orders 
        WHERE id = :order_id 
        AND user_id = :user_id 
        AND (status = 'pending' OR (status = 'confirmed' AND payment_status = 'paid'))
    ");
    $stmt->execute([
        ':order_id' => $orderId,
        ':user_id' => $userId
    ]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        return false;
    }

    // Kiểm tra thời gian 1 giờ cho tất cả phương thức thanh toán
    $orderTime = strtotime($order['created_at']);
    $currentTime = time();
    $hourDiff = ($currentTime - $orderTime) / 3600;

    if ($hourDiff > 1) {
        return false; // Không cho sửa sau 1 giờ
    }

    // PayPal và COD đều chỉ giới hạn bởi thời gian 1 giờ

    // Cập nhật thông tin shipping
    $stmt = $this->conn->prepare("
        UPDATE orders
        SET fullname = :fullname, 
            phone = :phone, 
            address = :address, 
            updated_at = NOW()
        WHERE id = :order_id 
        AND user_id = :user_id 
        AND (status = 'pending' OR (status = 'confirmed' AND payment_status = 'paid'))
    ");
    
    return $stmt->execute([
        ':fullname' => $data['fullname'] ?? null,
        ':phone' => $data['phone'] ?? null,
        ':address' => $data['address'] ?? null,
        ':order_id' => $orderId,
        ':user_id' => $userId
    ]);
}

    // ================= PAYPAL PAYMENT =================
    public function updatePaypalPayment($orderId, $paypalId, $payerEmail, $status) {
        $query = "UPDATE orders
                  SET paypal_transaction_id = :paypal_id,
                      paypal_payer_email = :payer_email,
                      payment_status = :status,
                      payment_date = NOW()
                  WHERE id = :order_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':paypal_id' => $paypalId,
            ':payer_email' => $payerEmail,
            ':status' => $status,
            ':order_id' => $orderId
        ]);
    }

    // ================= XÓA ĐƠN =================
    public function deleteOrder($id) {
        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare("DELETE FROM order_details WHERE order_id = :id");
            $stmt->execute([':id' => $id]);

            $stmt = $this->conn->prepare("DELETE FROM orders WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
     public function getDB() {
        return $this->conn;
    }
    public function confirmOrder($orderId, $userId, $data = []) {
    $db = $this->getDB();
    // Lấy đơn hàng
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order) return false;

    // Chỉ admin mới xác nhận hủy
    if ($order['status'] === 'cancel_request') {
        // Cập nhật trạng thái sang cancelled + failed
        $stmt = $db->prepare("UPDATE orders SET status = 'cancelled', payment_status = 'failed' WHERE id = ?");
        return $stmt->execute([$orderId]);
    }

    // Hoặc xác nhận thanh toán (tùy business logic)
    $stmt = $db->prepare("UPDATE orders SET status = 'confirmed' WHERE id = ?");
    return $stmt->execute([$orderId]);
}

// ================= CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG =================
public function updateOrderStatus($orderId, $status) {
    $stmt = $this->conn->prepare("
        UPDATE orders
        SET status = :status, updated_at = NOW()
        WHERE id = :order_id
    ");
    return $stmt->execute([
        ':status'    => $status,
        ':order_id'  => $orderId
    ]);
}
public function cancelPendingPaypal($orderId) {
    $stmt = $this->conn->prepare("
        UPDATE orders
        SET status = 'cancelled', payment_status = 'failed', updated_at = NOW()
        WHERE id = :order_id AND payment_status = 'pending'
    ");
    return $stmt->execute([
        ':order_id' => $orderId
    ]); 

}

// ================= TIẾP TỤC THANH TOÁN PAYPAL =================
public function continuePayment($orderId) {
    // Lấy thông tin đơn hàng
    $stmt = $this->conn->prepare("
        SELECT id, payment_method, payment_status, status, created_at, paypal_order_id
        FROM orders 
        WHERE id = :order_id
    ");
    $stmt->execute([':order_id' => $orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        return false;
    }

    // Chỉ cho phép tiếp tục thanh toán PayPal với trạng thái pending
    if ($order['payment_method'] !== 'paypal' || $order['payment_status'] !== 'pending') {
        return false;
    }

    // Kiểm tra thời gian 1 giờ
    $orderTime = strtotime($order['created_at']);
    $currentTime = time();
    $hourDiff = ($currentTime - $orderTime) / 3600;

    if ($hourDiff > 1) {
        return false; // Không cho tiếp tục thanh toán sau 1 giờ
    }

    // Trả về thông tin đơn hàng để tiếp tục thanh toán
    return $order;
}

// Auto cancel pending PayPal orders
public function autoCancelPendingPaypalOrders() {
    try {
        require_once __DIR__ . '/../models/PaymentModel.php';
        require_once __DIR__ . '/../utils/MailService.php';
        
        $paymentModel = new PaymentModel();
        $orders = $paymentModel->getPendingPaypalOrders(1);
        $count = 0;
        
        foreach ($orders as $order) {
            $success = $paymentModel->cancelPendingPaypalOrder($order['id']);
            if ($success) {
                // Gửi email thông báo user
                try {
                    MailService::sendOrderCancelled($order);
                } catch (Exception $e) {
                    // Log error nhưng không dừng quá trình
                    error_log("Failed to send cancellation email for order #{$order['id']}: " . $e->getMessage());
                }
                $count++;
            }
        }
        
        if ($count > 0) {
            error_log("Auto cancelled {$count} pending PayPal orders");
        }
    } catch (Exception $e) {
        // Log error nhưng không dừng quá trình chính
        error_log("Auto cancel PayPal orders failed: " . $e->getMessage());
    }
}
}