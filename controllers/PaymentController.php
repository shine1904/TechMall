<?php
require_once 'models/PaymentModel.php';
require_once 'models/OrderModel.php';
require_once 'utils/Response.php';
require_once 'middleware/RBACMiddleware.php';
require_once __DIR__ . '/../utils/MailService.php';

class PaymentController {
    private $paymentModel;
    private $orderModel;
    private $rbac;

    public function __construct() {
        $this->paymentModel = new PaymentModel();
        $this->orderModel = new OrderModel();
        $this->rbac = new RBACMiddleware();
    }

    // ================== COD ==================
public function processCOD($params, $user_data) {
    try {
        $order_id = $params[0] ?? null;
        if (!$order_id) {
            Response::sendError('Order ID is required', 400);
        }

        if (!$this->rbac->checkPermission($user_data['user_id'], 'order.create')) {
            Response::sendError('Insufficient permissions', 403);
        }

        if ($this->paymentModel->processCOD($order_id, $user_data['user_id'])) {
            // Gửi email thông báo đơn hàng COD đã được xác nhận
            $order = $this->orderModel->getOrderById($order_id);
            if ($order) {
                MailService::sendOrderSuccess($order);
            }
            
            // ✅ trả redirect_url để FE xử lý như PayPal
            Response::sendSuccess([
                "redirect_url" => "http://127.0.0.1:5500/testcase/test%20html/cart.html?status=success&order_id={$order_id}"
            ], "COD order confirmed");
        } else {
            Response::sendError("Thanh toán COD thất bại", 400);
        }
    } catch (Exception $e) {
        Response::sendError($e->getMessage(), 500);
    }
}



    // ================== MOMO ==================
  public function createMomoPayment($params, $user_data) {
    try {
        $order_id = $params[0] ?? null;
        if (!$order_id) {
            Response::sendError('Order ID is required', 400);
        }

        if (!$this->rbac->checkPermission($user_data['user_id'], 'order.create')) {
            Response::sendError('Insufficient permissions', 403);
        }

        $result = $this->paymentModel->createMomoPayment($order_id, $user_data['user_id']);
        if ($result) {
            // Gửi email thông báo thanh toán thành công
            $order = $this->orderModel->getOrderById($order_id);
            if ($order) {
                MailService::sendOrderSuccess($order);
            }
            
            // ✅ giống COD → trả redirect_url về cart
            Response::sendSuccess([
                "redirect_url" => "http://127.0.0.1:5500/testcase/test%20html/cart.html?status=success&order_id={$order_id}"
            ], "Momo payment success");
        } else {
            Response::sendError("Momo payment failed", 400);
        }
    } catch (Exception $e) {
        Response::sendError($e->getMessage(), 500);
    }
}


    public function processMomoCallback($params, $user_data) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data)) {
                $data = [
                    'transactionId' => $_GET['transactionId'] ?? null,
                    'resultCode' => $_GET['resultCode'] ?? 0,
                    'amount' => $_GET['amount'] ?? 0
                ];
            }

            if (empty($data['transactionId'])) {
                Response::sendError('Transaction ID is required', 400);
            }

            $result = $this->paymentModel->processMomoCallback($data);
            if ($result['success']) {
                Response::sendSuccess([
                    'order_id' => $result['order_id'],
                    'transaction_id' => $result['transaction_id'],
                    'status' => 'paid'
                ], 'Momo payment processed successfully');
            } else {
                Response::sendError('Momo payment failed', 400);
            }
        } catch (Exception $e) {
            Response::sendError('Error processing Momo callback: ' . $e->getMessage());
        }
    }
       public function continuePayment($params, $user_data) {
        $orderId = $params[0] ?? null;
        if (!$orderId) {
            Response::sendError('Order ID is required', 400);
        }

        if (!$this->rbac->checkPermission($user_data['user_id'], 'order.read')) {
            Response::sendError('Insufficient permissions', 403);
        }

        $order = $this->orderModel->continuePayment($orderId);
        if (!$order) {
            Response::sendError('Không thể tiếp tục thanh toán. Đơn hàng đã quá thời gian cho phép (1 giờ) hoặc không hợp lệ', 400);
        }
        
        Response::sendSuccess($order, 'Có thể tiếp tục thanh toán');
    }

    // ================== PAYPAL ==================
   public function createPaypalPayment($params, $user_data) {
    try {
        $order_id = $params[0] ?? null;
        if (!$order_id) {
            Response::sendError('Order ID is required', 400);
        }

        if (!$this->rbac->checkPermission($user_data['user_id'], 'order.create')) {
            Response::sendError('Insufficient permissions', 403);
        }

        $result = $this->paymentModel->createPaypalPayment($order_id, $user_data['user_id']);
        if ($result) {
            Response::sendSuccess($result, 'Paypal payment initiated successfully');
        } else {
            Response::sendError('Failed to create Paypal payment');
        }
    } catch (Exception $e) {
        Response::sendError('Error creating Paypal payment: ' . $e->getMessage());
    }
}


public function processPaypalSuccess($params, $user_data) {
    try {
        // Auto cancel pending PayPal orders trước khi xử lý thanh toán
        $this->orderModel->autoCancelPendingPaypalOrders();
        
        $paypal_order_id = $_GET['paypal_order_id'] ?? ($_GET['token'] ?? null);

        if (!$paypal_order_id) {
            Response::sendError('Paypal token is required', 400);
        }

        $order_id = $this->paymentModel->getOrderIdByPaypalOrder($paypal_order_id);
        if (!$order_id) {
            Response::sendError('Order not found for this PayPal order', 404);
        }

        $result = $this->paymentModel->capturePaypalPayment($order_id, $paypal_order_id);

        if ($result) {
            // cập nhật order_status = confirmed, payment_status = paid
            $this->paymentModel->updateOrderStatusAfterPaypal($order_id, 'confirmed', 'paid');

            // Gửi email thông báo thanh toán thành công
            $order = $this->orderModel->getOrderById($order_id);
            if ($order) {
                MailService::sendOrderSuccess($order);
            }

            $redirectUrl = "http://127.0.0.1:5500/testcase/test%20html/cart.html?status=success&order_id={$order_id}";
            header("Location: $redirectUrl");
            exit;
        } else {
            $redirectUrl = "http://127.0.0.1:5500/testcase/test%20html/cart.html?status=failed&order_id={$order_id}";
            header("Location: $redirectUrl");
            exit;
        }
    } catch (Exception $e) {
        $redirectUrl = "http://127.0.0.1:5500/testcase/test%20html/cart.html?status=failed&error=" . urlencode($e->getMessage());
        header("Location: $redirectUrl");
        exit;
    }
}






   public function processPaypalCancel($params, $user_data) {
    try {
        $order_id = $_GET['order_id'] ?? null;
        $paypal_order_id = $_GET['paypal_order_id'] ?? ($_GET['token'] ?? null);

        if (!$order_id || !$paypal_order_id) {
            Response::sendError('Order ID and PayPal Order ID are required', 400);
        }

        $order = $this->paymentModel->processPaypalCancel($order_id, $paypal_order_id);

        if ($order) {
            MailService::sendOrderCancelled($order);

            $redirectUrl = "http://127.0.0.1:5500/testcase/test%20html/cart.html?status=cancel&order_id={$order_id}";
            header("Location: $redirectUrl");
            exit;
        } else {
            Response::sendError('Failed to process Paypal cancel');
        }
    } catch (Exception $e) {
        Response::sendError('Error processing Paypal cancel: ' . $e->getMessage());
    }
}


    // ================== CHECK STATUS ==================
    public function checkPaymentStatus($params, $user_data) {
        try {
            $order_id = $params[0] ?? null;
            if (!$order_id) {
                Response::sendError('Order ID is required', 400);
            }

            if (!$this->rbac->checkPermission($user_data['user_id'], 'order.read')) {
                Response::sendError('Insufficient permissions', 403);
            }

            $status = $this->paymentModel->checkPaymentStatus($order_id, $user_data['user_id']);
            if ($status) {
                Response::sendSuccess($status, 'Payment status retrieved successfully');
            } else {
                Response::sendError('Payment status not found', 404);
            }
        } catch (Exception $e) {
            Response::sendError('Error checking payment status: ' . $e->getMessage());
        }
    }

}
