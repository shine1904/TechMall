<?php
/**
 * Script tự động hủy đơn hàng PayPal pending sau 1 phút
 * Chạy script này mỗi phút qua cron job
 */

// Load database config first
require_once 'config/database.php';
require_once 'models/OrderModel.php';
require_once 'models/PaymentModel.php';
require_once 'utils/MailService.php';
require_once 'utils/Database.php';
require_once 'utils/Logger.php';

class AutoCancelPaypal {
    private $orderModel;
    private $paymentModel;
    private $logger;

    public function __construct() {
        $this->orderModel = new OrderModel();
        $this->paymentModel = new PaymentModel();
        $this->logger = new Logger();
    }

    public function run() {
        try {
            $this->logger->info("Starting auto cancel PayPal pending orders");
            
            // Lấy các đơn PayPal pending > 1 phút
            $orders = $this->paymentModel->getPendingPaypalOrders(1);

            $count = 0;
            foreach ($orders as $order) {
                try {
                    // Hủy đơn
                    $success = $this->paymentModel->cancelPendingPaypalOrder($order['id']);
                    
                    if ($success) {
                        $this->logger->info("Cancelled PayPal order #{$order['id']} - User: {$order['user_id']}");
                        
                        // Gửi email thông báo user
                        try {
                            MailService::sendOrderCancelled($order);
                            $this->logger->info("Sent cancellation email for order #{$order['id']}");
                        } catch (Exception $e) {
                            $this->logger->error("Failed to send cancellation email for order #{$order['id']}: " . $e->getMessage());
                        }
                        
                        $count++;
                    } else {
                        $this->logger->error("Failed to cancel order #{$order['id']}");
                    }
                } catch (Exception $e) {
                    $this->logger->error("Error processing order #{$order['id']}: " . $e->getMessage());
                }
            }

            $this->logger->info("Auto cancel completed. Cancelled {$count} PayPal pending orders");
            
            // Trả về kết quả cho cron job
            echo "Success: Cancelled {$count} PayPal pending orders\n";
            return $count;
            
        } catch (Exception $e) {
            $this->logger->error("Auto cancel PayPal failed: " . $e->getMessage());
            echo "Error: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Chạy script nếu được gọi trực tiếp
if (php_sapi_name() === 'cli') {
    $autoCancel = new AutoCancelPaypal();
    $result = $autoCancel->run();
    exit($result !== false ? 0 : 1);
}
?>
