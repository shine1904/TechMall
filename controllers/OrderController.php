<?php
require_once 'models/OrderModel.php';
require_once 'utils/Response.php';
require_once 'middleware/ValidationMiddleware.php';
require_once 'models/PaymentModel.php';
require_once 'middleware/RBACMiddleware.php';
require_once __DIR__ . '/../utils/MailService.php';

class OrderController {
    private $orderModel;
    private $validator;
    private $paymentModel;
    private $rbac;

    public function __construct() {
        $this->orderModel = new OrderModel();
        $this->validator = new ValidationMiddleware();
        $this->paymentModel = new PaymentModel();
        $this->rbac = new RBACMiddleware();
    }

    // POST /api/orders
    public function createOrder($params, $user_data) {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || empty($data['items'])) {
            Response::sendError("Items required", 400);
        }
        $userId = $user_data['user_id'] ?? null;
        if (!$userId) Response::sendError("Unauthorized", 401);

        try {
            // Auto cancel pending PayPal orders trước khi tạo đơn mới
            $this->orderModel->autoCancelPendingPaypalOrders();
            
            $orderId = $this->orderModel->createOrder($userId, $data);
            
            // Gửi email cảm ơn đặt hàng (chưa thanh toán)
            $order = $this->orderModel->getOrderById($orderId, $userId);
            if ($order) {
                MailService::sendOrderThankYou($order);
            }
            
            Response::sendSuccess(['order_id' => $orderId], "Order created (pending)");
        } catch (Exception $e) {
            Response::sendError("Failed to create order: " . $e->getMessage(), 500);
        }
    }

    // GET /api/orders
    public function getOrders($params, $user_data) {
        try {
            // Auto cancel pending PayPal orders trước khi lấy danh sách
            $this->orderModel->autoCancelPendingPaypalOrders();
            
            if ($this->isAdmin($user_data)) {
                $orders = $this->orderModel->getAllOrders();
            } else {
                $orders = $this->orderModel->getOrders(['user_id' => $user_data['user_id']]);
            }
            Response::sendSuccess($orders, "Orders fetched");
        } catch (Exception $e) {
            Response::sendError("Error fetching orders: " . $e->getMessage(), 500);
        }
    }
     public function userUpdateOrder($params, $user_data) {
    $orderId = $params[0] ?? null;
    if (!$orderId) Response::sendError("Order ID required", 400);

    $userId = $user_data['user_id'] ?? null;
    if (!$userId) Response::sendError("Unauthorized", 401);

    $data = json_decode(file_get_contents("php://input"), true);

    try {
        // Kiểm tra đơn hàng tồn tại
        $order = $this->orderModel->getOrderById($orderId, $userId);
        if (!$order) {
            Response::sendError("Đơn hàng không tồn tại hoặc không có quyền truy cập", 404);
        }

        // Nếu yêu cầu hủy đơn
        if (isset($data['cancel']) && $data['cancel'] === true) {
            // Kiểm tra phương thức thanh toán để quyết định logic
            if ($order['payment_method'] === 'cod') {
                // COD: Hủy trực tiếp
                $ok = $this->orderModel->userCancelOrder($orderId, $userId);
                if ($ok) {
                    Response::sendSuccess(true, "Đã hủy đơn hàng COD thành công");
                } else {
                    Response::sendError("Không thể hủy đơn hàng COD sau 1 giờ đặt hàng", 400);
                }
            } else {
                // PayPal: Kiểm tra trạng thái thanh toán
                if ($order['payment_status'] === 'pending') {
                    // Chưa thanh toán: Hủy trực tiếp
                    $ok = $this->orderModel->userCancelOrder($orderId, $userId);
                    if ($ok) {
                        Response::sendSuccess(true, "Đã hủy đơn hàng PayPal thành công");
                    } else {
                        Response::sendError("Không thể hủy đơn hàng PayPal sau 1 giờ đặt hàng", 400);
                    }
                } elseif ($order['payment_status'] === 'paid') {
                    // Đã thanh toán: Gửi yêu cầu hủy
                    $ok = $this->orderModel->requestCancel($orderId, $userId);
                    if ($ok) {
                        Response::sendSuccess(true, "Yêu cầu hủy đơn PayPal đã được gửi, chờ admin xác nhận");
                    } else {
                        Response::sendError("Không thể gửi yêu cầu hủy đơn PayPal sau 1 giờ đặt hàng", 400);
                    }
                } else {
                    Response::sendError("Không thể hủy đơn ở trạng thái thanh toán này", 400);
                }
            }
            return;
        }

        // Nếu cập nhật shipping info
        if (isset($data['fullname']) || isset($data['phone']) || isset($data['address'])) {
            $ok = $this->orderModel->updateShippingInfo($orderId, $userId, $data);
            if ($ok) {
                Response::sendSuccess(true, "Cập nhật thông tin giao hàng thành công");
            } else {
                // Lấy thông tin đơn hàng để xác định lý do lỗi
                $order = $this->orderModel->getOrderById($orderId, $userId);
                if (!$order) {
                    Response::sendError("Đơn hàng không tồn tại", 404);
                } elseif ($order['status'] !== 'pending') {
                    Response::sendError("Không thể sửa thông tin giao hàng khi đơn đã được xử lý", 400);
                } else {
                    Response::sendError("Không thể sửa thông tin sau 1 giờ đặt hàng", 400);
                }
            }
            return;
        }

        Response::sendError("Invalid request data", 400);
    } catch (Exception $e) {
        Response::sendError("Error: " . $e->getMessage(), 500);
    }
}


    // GET /api/orders/user/{id}
    public function getUserOrders($params, $user_data) {
        $user_id = $params[0] ?? null;
        if (!$user_id) Response::sendError("User ID required", 400);

        if ($user_data['user_id'] != $user_id && !$this->isAdmin($user_data)) {
            Response::sendError("Access denied", 403);
        }

        try {
            $filters = ['user_id' => $user_id];
            if (isset($_GET['status'])) $filters['status'] = $_GET['status'];
            $orders = $this->orderModel->getOrders($filters);
            Response::sendSuccess($orders);
        } catch (Exception $e) {
            Response::sendError("Error retrieving user orders: " . $e->getMessage(), 500);
        }
    }

    // GET /api/orders/{id}
    public function getOrderById($params, $user_data) {
        $id = $params[0] ?? null;
        if (!$id) Response::sendError("Order ID required", 400);

        try {
            $order = $this->orderModel->getOrderById($id, $user_data['user_id']);
            if (!$order) Response::sendError("Order not found", 404);

            if ($order['user_id'] != $user_data['user_id'] && !$this->isAdmin($user_data)) {
                Response::sendError("Access denied", 403);
            }

            Response::sendSuccess($order);
        } catch (Exception $e) {
            Response::sendError("Error retrieving order: " . $e->getMessage(), 500);
        }
    }

    // PUT /api/orders/{id}
    public function updateOrder($params, $user_data) {
        $orderId = $params[0] ?? null;
        if (!$orderId) Response::sendError("Order ID required", 400);

        $userId = $user_data['user_id'] ?? null;
        if (!$userId) Response::sendError("Unauthorized", 401);

        $data = json_decode(file_get_contents("php://input"), true);

        try {
            // Kiểm tra nếu admin cố gắng thay đổi shipping - không cho phép
            if ($this->isAdmin($user_data) && (isset($data['fullname']) || isset($data['phone']) || isset($data['address']))) {
                Response::sendError("Admin không thể thay đổi thông tin giao hàng. Chỉ user mới có thể sửa thông tin của mình", 403);
            }

            if (isset($data['cancel']) && $data['cancel'] === true) {
                $order = $this->orderModel->getOrderById($orderId, $userId);
                if (!$order) Response::sendError("Order not found", 404);

                // Kiểm tra phương thức thanh toán để quyết định logic
                if ($order['payment_method'] === 'cod') {
                    // COD: Hủy trực tiếp
                    $ok = $this->orderModel->userCancelOrder($orderId, $userId);
                    if ($ok) {
                        Response::sendSuccess(true, "Đơn hàng COD đã được hủy");
                        MailService::sendOrderCancelled($order);
                    } else {
                        Response::sendError("Không thể hủy đơn hàng COD sau 1 giờ đặt hàng");
                    }
                } else {
                    // PayPal: Kiểm tra trạng thái thanh toán
                    if ($order['payment_status'] === 'pending') {
                        // Chưa thanh toán: Hủy trực tiếp
                        $ok = $this->orderModel->userCancelOrder($orderId, $userId);
                        if ($ok) {
                            Response::sendSuccess(true, "Đơn hàng PayPal đã được hủy (chưa thanh toán)");
                            MailService::sendOrderCancelled($order);
                        } else {
                            Response::sendError("Không thể hủy đơn hàng PayPal sau 1 giờ đặt hàng");
                        }
                    } elseif ($order['payment_status'] === 'paid') {
                        // Đã thanh toán: Gửi yêu cầu hủy
                        $ok = $this->orderModel->requestCancel($orderId, $userId);
                        if ($ok) {
                            // Gửi email thông báo user đã gửi yêu cầu hủy
                            MailService::sendCancelRequest($order);
                            Response::sendSuccess(true, "Yêu cầu hủy đơn PayPal đã được gửi, chờ admin xác nhận");
                        } else {
                            Response::sendError("Không thể gửi yêu cầu hủy đơn PayPal sau 1 giờ đặt hàng");
                        }
                    } else {
                        Response::sendError("Không thể hủy đơn ở trạng thái thanh toán này");
                    }
                }
                return;
            }

            $ok = $this->orderModel->updateShippingInfo($orderId, $userId, $data);
            if ($ok) {
                // Lấy thông tin order đã cập nhật để gửi email
                $order = $this->orderModel->getOrderById($orderId, $userId);
                if ($order) {
                    MailService::sendShippingUpdate($order);
                }
                Response::sendSuccess(true, "Cập nhật thông tin giao hàng thành công");
            } else {
                // Lấy thông tin đơn hàng để xác định lý do lỗi
                $order = $this->orderModel->getOrderById($orderId, $userId);
                if (!$order) {
                    Response::sendError("Đơn hàng không tồn tại", 404);
                } elseif ($order['status'] !== 'pending') {
                    Response::sendError("Không thể sửa thông tin giao hàng khi đơn đã được xử lý", 400);
                } else {
                    Response::sendError("Không thể sửa thông tin sau 1 giờ đặt hàng", 400);
                }
            }
        } catch (Exception $e) {
            Response::sendError("Error: " . $e->getMessage(), 500);
        }
    }

    // PUT /api/orders/{id}/status
    public function updateOrderStatus($params, $user_data) {
        $id = $params[0] ?? null;
        if (!$id) Response::sendError("Order ID required", 400);

        if (!$this->isAdmin($user_data)) Response::sendError("Access denied", 403);

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['status'])) Response::sendError("Status is required", 400);

        try {
            $success = $this->orderModel->updateOrderStatus($id, $data['status']);
            if ($success) {
                // Gửi email thông báo khi trạng thái thay đổi
                $order = $this->orderModel->getOrderById($id);
                if ($order) {
                    if ($data['status'] === 'shipped') {
                        MailService::sendOrderShipped($order);
                    } elseif ($data['status'] === 'delivered') {
                        MailService::sendOrderDelivered($order);
                    }
                }
                Response::sendSuccess([], "Order status updated successfully");
            } else {
                Response::sendError("Failed to update order status");
            }
        } catch (Exception $e) {
            Response::sendError("Error updating order status: " . $e->getMessage(), 500);
        }
    }

    // PUT /api/orders/{order_id}/confirm
    public function confirmOrder($params, $user_data) {
        $orderId = $params[0] ?? null;
        if (!$orderId) Response::sendError("Order ID required", 400);

        $userId = $user_data['user_id'] ?? null;
        if (!$userId) Response::sendError("Unauthorized", 401);

        $data = json_decode(file_get_contents("php://input"), true);

        try {
            $ok = $this->orderModel->confirmOrder($orderId, $userId, $data);
            if ($ok) {
                Response::sendSuccess(true, "Order confirmed");
            } else {
                Response::sendError("Update failed");
            }
        } catch (Exception $e) {
            Response::sendError("Error: " . $e->getMessage(), 500);
        }
    }
 
// ...existing code...
    public function cancelOrder($params, $user_data) {
        $orderId = $params[0] ?? null;
        if (!$orderId) Response::sendError("Order ID required", 400);

        $currentUserId = $user_data['user_id'] ?? null;
        if (!$currentUserId) Response::sendError("Unauthorized", 401);

        try {
            // Fetch order without restrictive user filter to check ownership
            $order = $this->orderModel->getOrderById($orderId, $currentUserId);
            if (!$order) {
                // maybe the order exists but belongs to someone else
                // try to fetch order only by id
                $order = $this->orderModel->getOrderById($orderId, $order ? $order['user_id'] : $currentUserId);
            }
            if (!$order) Response::sendError("Order not found", 404);

            // Admin không thể tự hủy đơn hàng - chỉ có thể xác nhận hủy qua endpoint riêng
            if ($this->isAdmin($user_data)) {
                Response::sendError("Admin không thể tự hủy đơn hàng. Chỉ có thể xác nhận hủy đơn từ yêu cầu của user", 403);
            }

            // Owner can cancel only if they own the order
            if ($order['user_id'] == $currentUserId) {
                // Kiểm tra phương thức thanh toán để quyết định logic
                if ($order['payment_method'] === 'cod') {
                    // COD: Hủy trực tiếp
                    $ok = $this->orderModel->userCancelOrder($orderId, $currentUserId);
                    if ($ok) {
                        Response::sendSuccess(true, "Đã hủy đơn hàng COD");
                        MailService::sendOrderCancelled($order);
                    } else {
                        Response::sendError("Không thể hủy đơn hàng COD sau 1 giờ đặt hàng");
                    }
                    return;
                } else {
                    // PayPal: Kiểm tra trạng thái thanh toán
                    if ($order['payment_status'] === 'pending') {
                        // Chưa thanh toán: Hủy trực tiếp
                        $ok = $this->orderModel->userCancelOrder($orderId, $currentUserId);
                        if ($ok) {
                            Response::sendSuccess(true, "Đã hủy đơn hàng PayPal");
                            MailService::sendOrderCancelled($order);
                        } else {
                            Response::sendError("Không thể hủy đơn hàng PayPal sau 1 giờ đặt hàng");
                        }
                        return;
                    } elseif ($order['payment_status'] === 'paid') {
                        // Đã thanh toán: Gửi yêu cầu hủy
                        $ok = $this->orderModel->requestCancel($orderId, $currentUserId);
                        if ($ok) {
                            // Gửi email thông báo user đã gửi yêu cầu hủy
                            MailService::sendCancelRequest($order);
                            Response::sendSuccess(true, "Yêu cầu hủy đơn PayPal đã được gửi, chờ admin xác nhận");
                        } else {
                            Response::sendError("Không thể gửi yêu cầu hủy đơn PayPal sau 1 giờ đặt hàng");
                        }
                        return;
                    }
                }
            }

            Response::sendError("Bạn chỉ có thể hủy đơn của mình khi đang chờ xử lý", 403);
        } catch (Exception $e) {
            Response::sendError("Error: " . $e->getMessage(), 500);
        }
    }
// ...existing code...


    // PUT /api/orders/{id}/cancel (admin duyệt hủy)
    public function adminCancelOrder($params, $user_data) {
        if (!$this->isAdmin($user_data)) Response::sendError("Access denied", 403);

        $orderId = $params[0] ?? null;
        if (!$orderId) Response::sendError("Order ID required", 400);

        try {
            // Lấy thông tin order TRƯỚC khi hủy để gửi email
            $order = $this->orderModel->getOrderById($orderId);
            if (!$order) {
                Response::sendError("Order not found", 404);
                return;
            }
            
            $ok = $this->orderModel->adminCancelOrder($orderId);
            if ($ok) {
                // Gửi email thông báo admin duyệt hủy
                try {
                    error_log("Attempting to send cancellation email for order #{$orderId}, user_id: {$order['user_id']}");
                   $emailResult = MailService::sendOrderCancelled($order);    
                    error_log("Email send result for order #{$orderId}: " . ($emailResult ? 'SUCCESS' : 'FAILED'));
                } catch (Exception $e) {
                    error_log("Failed to send admin approval cancellation email for order #{$orderId}: " . $e->getMessage());
                }
                Response::sendSuccess(true, "Đơn hàng đã bị hủy (admin duyệt)");
            } else {
                Response::sendError("Hủy đơn thất bại (kiểm tra trạng thái)");
            }
        } catch (Exception $e) {
            Response::sendError("Error: " . $e->getMessage(), 500);
        }
    }
    // PUT /api/orders/pending-paypal/auto-cancel
public function autoCancelPendingPaypal($params, $user_data) {
    // Chỉ admin mới được chạy
    if (!$this->isAdmin($user_data)) {
        Response::sendError("Access denied", 403);
    }

    try {
        // Sử dụng PaymentModel để lấy và hủy đơn hàng
        $orders = $this->paymentModel->getPendingPaypalOrders(1);
        $count = 0;
        
        foreach ($orders as $order) {
            // Hủy đơn
            $success = $this->paymentModel->cancelPendingPaypalOrder($order['id']);
            
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

        Response::sendSuccess([
            'count' => $count,
            'total_found' => count($orders)
        ], "Cancelled {$count} pending PayPal orders and notified users");
        
    } catch (Exception $e) {
        Response::sendError("Error auto-cancelling PayPal orders: " . $e->getMessage(), 500);
    }
}


    // check quyền admin
    private function isAdmin($user_data) {
        // Kiểm tra nếu roles không tồn tại hoặc không phải array
        if (!isset($user_data['roles']) || !is_array($user_data['roles'])) {
            return false;
        }
        
        return in_array('super_admin', $user_data['roles']) ||
               in_array('order_admin', $user_data['roles']);
    }


}
