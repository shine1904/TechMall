<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class MailService {
    private static function getEmailTemplate($content) {
        return "
        <!DOCTYPE html>
        <html lang='vi'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>TechMall</title>
        </head>
        <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
                <div style='text-align: center; margin-bottom: 30px; border-bottom: 2px solid #ecf0f1; padding-bottom: 20px;'>
                    <h1 style='color: #2c3e50; margin: 0; font-size: 32px; font-weight: bold;'>TechMall</h1>
                    <p style='color: #7f8c8d; margin: 5px 0 0 0; font-size: 14px;'>Cửa hàng công nghệ uy tín hàng đầu</p>
                </div>
                
                {$content}
                
                <div style='text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ecf0f1;'>
                    <p style='color: #95a5a6; font-size: 12px; margin: 0 0 10px 0;'>
                        <a href='http://localhost/ecommerce_api/testcase/test%20html/policy.html' style='color: #3498db; text-decoration: none; font-weight: 500;'>📋 Xem chính sách & điều khoản</a>
                    </p>
                    <p style='color: #95a5a6; font-size: 12px; margin: 0;'>
                        © 2024 TechMall. Tất cả quyền được bảo lưu.<br>
                        Email: support@techmall.com | Hotline: 1900-xxxx
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    private static function sendMail($to, $subject, $body) {
        // Kiểm tra email hợp lệ
        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log("Invalid email address: " . var_export($to, true));
            return false;
        }

        $mail = new PHPMailer(true);
        try {
            // Cấu hình SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'fokerface04@gmail.com';       // Gmail
            $mail->Password   = 'cafsbvhhdzupcosg';           // App password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('fokerface04@gmail.com', 'TechMall Support');
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            if ($mail->send()) {
                return true;
            } else {
                error_log("Mailer không thể gửi: " . $mail->ErrorInfo);
                return false;
            }
        } catch (Exception $e) {
            error_log("Mail error: " . $e->getMessage());
            return false;
        }
    }

    public static function sendOrderThankYou($order) {
        // Lấy email từ bảng users thông qua user_id
        require_once __DIR__ . '/../models/UserModel.php';
        $userModel = new UserModel();
        $user = $userModel->getUserById($order['user_id']);
        
        if (!$user || empty($user['email'])) {
            error_log("Order #{$order['id']} - User #{$order['user_id']} has no email address");
            return false;
        }

        $subject = "🎉 Cảm ơn bạn đã đặt hàng tại TechMall - Đơn hàng #{$order['id']}";
        $body = self::getEmailTemplate("
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #2c3e50; margin: 0; font-size: 28px;'>🎉 Cảm ơn bạn đã đặt hàng!</h1>
                <p style='color: #7f8c8d; margin: 10px 0 0 0; font-size: 16px;'>TechMall - Cửa hàng công nghệ uy tín</p>
            </div>
            
            <div style='background: #f8f9fa; padding: 25px; border-radius: 10px; margin: 20px 0;'>
                <h2 style='color: #2c3e50; margin: 0 0 15px 0; font-size: 20px;'>📋 Thông tin đơn hàng</h2>
                <p style='margin: 8px 0; color: #34495e;'><strong>Mã đơn hàng:</strong> #{$order['id']}</p>
                <p style='margin: 8px 0; color: #34495e;'><strong>Tổng tiền:</strong> <span style='color: #e74c3c; font-size: 18px; font-weight: bold;'>" . number_format($order['total']) . " VND</span></p>
                <p style='margin: 8px 0; color: #34495e;'><strong>Trạng thái:</strong> <span style='color: #f39c12; font-weight: bold;'>Chờ thanh toán</span></p>
            </div>
            
            <div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #856404; margin: 0 0 10px 0; font-size: 18px;'>⏰ Lưu ý quan trọng</h3>
                <p style='margin: 0; color: #856404; line-height: 1.6;'>
                    <strong>Vui lòng hoàn tất thanh toán trong vòng <span style='color: #e74c3c;'>1 giờ</span> để đơn hàng được xử lý.</strong><br>
                    Nếu không thanh toán kịp thời, đơn hàng sẽ bị hủy tự động để đảm bảo tính công bằng cho tất cả khách hàng.
                </p>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <p style='color: #7f8c8d; font-size: 14px; margin: 0;'>
                    Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi qua email hoặc hotline.
                </p>
                <p style='color: #7f8c8d; font-size: 12px; margin: 10px 0 0 0;'>
                    <a href='http://localhost/ecommerce_api/testcase/test%20html/policy.html' style='color: #3498db; text-decoration: none;'>📋 Xem chính sách giao hàng & điều khoản</a>
                </p>
            </div>
        ");
        return self::sendMail($user['email'], $subject, $body);
    }

    public static function sendOrderSuccess($order) {
        // Lấy email từ bảng users thông qua user_id
        require_once __DIR__ . '/../models/UserModel.php';
        $userModel = new UserModel();
        $user = $userModel->getUserById($order['user_id']);
        
        if (!$user || empty($user['email'])) {
            error_log("Order #{$order['id']} - User #{$order['user_id']} has no email address");
            return false;
        }

        $subject = "✅ Thanh toán thành công - Đơn hàng #{$order['id']}";
        $body = self::getEmailTemplate("
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #27ae60; margin: 0; font-size: 28px;'>✅ Thanh toán thành công!</h1>
                <p style='color: #7f8c8d; margin: 10px 0 0 0; font-size: 16px;'>TechMall - Cửa hàng công nghệ uy tín</p>
            </div>
            
            <div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 25px; border-radius: 10px; margin: 20px 0;'>
                <h2 style='color: #155724; margin: 0 0 15px 0; font-size: 20px;'>🎉 Chúc mừng!</h2>
                <p style='margin: 0; color: #155724; font-size: 16px; line-height: 1.6;'>
                    Đơn hàng của bạn đã được thanh toán thành công. Chúng tôi rất vui mừng được phục vụ bạn!
                </p>
            </div>
            
            <div style='background: #f8f9fa; padding: 25px; border-radius: 10px; margin: 20px 0;'>
                <h2 style='color: #2c3e50; margin: 0 0 15px 0; font-size: 20px;'>📋 Thông tin đơn hàng</h2>
                <p style='margin: 8px 0; color: #34495e;'><strong>Mã đơn hàng:</strong> #{$order['id']}</p>
                <p style='margin: 8px 0; color: #34495e;'><strong>Tổng tiền:</strong> <span style='color: #e74c3c; font-size: 18px; font-weight: bold;'>" . number_format($order['total']) . " VND</span></p>
                <p style='margin: 8px 0; color: #34495e;'><strong>Trạng thái:</strong> <span style='color: #27ae60; font-weight: bold;'>Đã thanh toán</span></p>
            </div>
            
            <div style='background: #e3f2fd; border: 1px solid #bbdefb; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #1565c0; margin: 0 0 10px 0; font-size: 18px;'>🚚 Bước tiếp theo</h3>
                <p style='margin: 0; color: #1565c0; line-height: 1.6;'>
                    Chúng tôi sẽ bắt đầu xử lý đơn hàng của bạn ngay lập tức và giao hàng trong thời gian sớm nhất có thể. 
                    Bạn sẽ nhận được thông báo cập nhật về tình trạng giao hàng qua email.
                </p>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <p style='color: #7f8c8d; font-size: 14px; margin: 0;'>
                    Cảm ơn bạn đã tin tưởng và lựa chọn TechMall. Chúc bạn có trải nghiệm mua sắm tuyệt vời!
                </p>
                <p style='color: #7f8c8d; font-size: 12px; margin: 10px 0 0 0;'>
                    <a href='http://localhost/ecommerce_api/testcase/test%20html/policy.html' style='color: #3498db; text-decoration: none;'>📋 Xem chính sách đổi trả & bảo hành</a>
                </p>
            </div>
        ");
        return self::sendMail($user['email'], $subject, $body);
    }

    public static function sendShippingUpdate($order) {
        // Lấy email từ bảng users thông qua user_id
        require_once __DIR__ . '/../models/UserModel.php';
        $userModel = new UserModel();
        $user = $userModel->getUserById($order['user_id']);
        
        if (!$user || empty($user['email'])) {
            error_log("Order #{$order['id']} - User #{$order['user_id']} has no email address");
            return false;
        }

        $subject = "📦 Cập nhật thông tin giao hàng - Đơn hàng #{$order['id']}";
        $body = self::getEmailTemplate("
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #3498db; margin: 0; font-size: 28px;'>📦 Cập nhật thông tin giao hàng</h1>
                <p style='color: #7f8c8d; margin: 10px 0 0 0; font-size: 16px;'>TechMall - Cửa hàng công nghệ uy tín</p>
            </div>
            
            <div style='background: #e3f2fd; border: 1px solid #bbdefb; padding: 25px; border-radius: 10px; margin: 20px 0;'>
                <h2 style='color: #1565c0; margin: 0 0 15px 0; font-size: 20px;'>ℹ️ Thông báo</h2>
                <p style='margin: 0; color: #1565c0; font-size: 16px; line-height: 1.6;'>
                    Chúng tôi đã cập nhật thông tin giao hàng cho đơn hàng của bạn. Vui lòng kiểm tra thông tin bên dưới để đảm bảo chính xác.
                </p>
            </div>
            
            <div style='background: #f8f9fa; padding: 25px; border-radius: 10px; margin: 20px 0;'>
                <h2 style='color: #2c3e50; margin: 0 0 15px 0; font-size: 20px;'>📋 Thông tin đơn hàng</h2>
                <p style='margin: 8px 0; color: #34495e;'><strong>Mã đơn hàng:</strong> #{$order['id']}</p>
            </div>
            
            <div style='background: #fff3e0; border: 1px solid #ffcc02; padding: 25px; border-radius: 10px; margin: 20px 0;'>
                <h2 style='color: #e65100; margin: 0 0 15px 0; font-size: 20px;'>🏠 Thông tin giao hàng</h2>
                <p style='margin: 8px 0; color: #bf360c;'><strong>👤 Họ và tên:</strong> {$order['fullname']}</p>
                <p style='margin: 8px 0; color: #bf360c;'><strong>📞 Số điện thoại:</strong> {$order['phone']}</p>
                <p style='margin: 8px 0; color: #bf360c;'><strong>📍 Địa chỉ:</strong> {$order['address']}</p>
            </div>
            
            <div style='background: #f3e5f5; border: 1px solid #ce93d8; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #7b1fa2; margin: 0 0 10px 0; font-size: 18px;'>💡 Lưu ý</h3>
                <p style='margin: 0; color: #7b1fa2; line-height: 1.6;'>
                    Nếu thông tin giao hàng không chính xác, vui lòng liên hệ ngay với chúng tôi để được hỗ trợ cập nhật.
                </p>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <p style='color: #7f8c8d; font-size: 14px; margin: 0;'>
                    Cảm ơn bạn đã tin tưởng TechMall. Chúng tôi sẽ giao hàng đến địa chỉ trên trong thời gian sớm nhất.
                </p>
                <p style='color: #7f8c8d; font-size: 12px; margin: 10px 0 0 0;'>
                    <a href='http://localhost/ecommerce_api/testcase/test%20html/policy.html' style='color: #3498db; text-decoration: none;'>📋 Xem chính sách giao hàng</a>
                </p>
            </div>
        ");
        return self::sendMail($user['email'], $subject, $body);
    }

    public static function sendOrderCancelled($order) {
        // Lấy email từ bảng users thông qua user_id
        require_once __DIR__ . '/../models/UserModel.php';
        $userModel = new UserModel();
        $user = $userModel->getUserById($order['user_id']);
        
        if (!$user || empty($user['email'])) {
            error_log("Order #{$order['id']} - User #{$order['user_id']} has no email address");
            return false;
        }

        $subject = "❌ Đơn hàng #{$order['id']} đã được hủy";
        $body = self::getEmailTemplate("
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #e74c3c; margin: 0; font-size: 28px;'>❌ Đơn hàng đã được hủy</h1>
                <p style='color: #7f8c8d; margin: 10px 0 0 0; font-size: 16px;'>TechMall - Cửa hàng công nghệ uy tín</p>
            </div>
            
            <div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 25px; border-radius: 10px; margin: 20px 0;'>
                <h2 style='color: #721c24; margin: 0 0 15px 0; font-size: 20px;'>📋Thông báo</h2>
                <p style='margin: 0; color: #721c24; font-size: 16px; line-height: 1.6;'>
                    Chúng tôi rất tiếc thông báo rằng đơn hàng của bạn đã được hủy. Chúng tôi hiểu điều này có thể gây bất tiện cho bạn.
                </p>
            </div>
            
            <div style='background: #f8f9fa; padding: 25px; border-radius: 10px; margin: 20px 0;'>
                <h2 style='color: #2c3e50; margin: 0 0 15px 0; font-size: 20px;'>📋 Thông tin đơn hàng</h2>
                <p style='margin: 8px 0; color: #34495e;'><strong>Mã đơn hàng:</strong> #{$order['id']}</p>
                <p style='margin: 8px 0; color: #34495e;'><strong>Trạng thái:</strong> <span style='color: #e74c3c; font-weight: bold;'>Đã hủy</span></p>
            </div>
            
            <div style='background: #e2e3e5; border: 1px solid #d6d8db; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #495057; margin: 0 0 10px 0; font-size: 18px;'>💡 Hỗ trợ</h3>
                <p style='margin: 0; color: #495057; line-height: 1.6;'>
                    Nếu bạn có bất kỳ thắc mắc nào về việc hủy đơn hàng hoặc cần hỗ trợ, vui lòng liên hệ với chúng tôi. 
                    Chúng tôi luôn sẵn sàng hỗ trợ bạn.
                </p>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <p style='color: #7f8c8d; font-size: 14px; margin: 0;'>
                    Cảm ơn bạn đã tin tưởng TechMall. Chúng tôi hy vọng có cơ hội phục vụ bạn trong tương lai.
                </p>
                <p style='color: #7f8c8d; font-size: 12px; margin: 10px 0 0 0;'>
                    <a href='http://localhost/ecommerce_api/testcase/test%20html/policy.html' style='color: #3498db; text-decoration: none;'>📋 Xem chính sách hủy đơn hàng</a>
                </p>
            </div>
        ");
        return self::sendMail($user['email'], $subject, $body);
    }

    public static function sendCancelRequest($order) {
        // Lấy email từ bảng users thông qua user_id
        require_once __DIR__ . '/../models/UserModel.php';
        $userModel = new UserModel();
        $user = $userModel->getUserById($order['user_id']);
        
        if (!$user || empty($user['email'])) {
            error_log("Order #{$order['id']} - User #{$order['user_id']} has no email address");
            return false;
        }

        $subject = "📝 Yêu cầu hủy đơn hàng #{$order['id']} đã được gửi";
        $body = self::getEmailTemplate("
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #f39c12; margin: 0; font-size: 28px;'>📝 Yêu cầu hủy đơn hàng</h1>
                <p style='color: #7f8c8d; margin: 10px 0 0 0; font-size: 16px;'>TechMall - Cửa hàng công nghệ uy tín</p>
            </div>
            
            <div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 25px; border-radius: 10px; margin: 20px 0;'>
                <h2 style='color: #856404; margin: 0 0 15px 0; font-size: 20px;'>✅ Đã nhận yêu cầu</h2>
                <p style='margin: 0; color: #856404; font-size: 16px; line-height: 1.6;'>
                    Chúng tôi đã nhận được yêu cầu hủy đơn hàng của bạn.Vui lòng chờ xét duyệt.
                </p>
            </div>
            
            <div style='background: #f8f9fa; padding: 25px; border-radius: 10px; margin: 20px 0;'>
                <h2 style='color: #2c3e50; margin: 0 0 15px 0; font-size: 20px;'>📋 Thông tin đơn hàng</h2>
                <p style='margin: 8px 0; color: #34495e;'><strong>Mã đơn hàng:</strong> #{$order['id']}</p>
                <p style='margin: 8px 0; color: #34495e;'><strong>Trạng thái:</strong> <span style='color: #f39c12; font-weight: bold;'>Đang xử lý yêu cầu hủy</span></p>
            </div>
            
            <div style='background: #e3f2fd; border: 1px solid #bbdefb; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #1565c0; margin: 0 0 10px 0; font-size: 18px;'>⏰ Thời gian xử lý</h3>
                <p style='margin: 0; color: #1565c0; line-height: 1.6;'>
                    Chúng tôi sẽ xem xét yêu cầu của bạn và phản hồi trong thời gian sớm nhất có thể, 
                    thường trong vòng 24 giờ làm việc.
                </p>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <p style='color: #7f8c8d; font-size: 14px; margin: 0;'>
                    Cảm ơn bạn đã tin tưởng TechMall. Chúng tôi sẽ xử lý yêu cầu của bạn một cách nhanh chóng và chuyên nghiệp.
                </p>
                <p style='color: #7f8c8d; font-size: 12px; margin: 10px 0 0 0;'>
                    <a href='http://localhost/ecommerce_api/testcase/test%20html/policy.html' style='color: #3498db; text-decoration: none;'>📋 Xem chính sách hủy đơn hàng</a>
                </p>
            </div>
        ");
        return self::sendMail($user['email'], $subject, $body);
    }

    public static function sendOrderShipped($order) {
        // Lấy email từ bảng users thông qua user_id
        require_once __DIR__ . '/../models/UserModel.php';
        $userModel = new UserModel();
        $user = $userModel->getUserById($order['user_id']);
        
        if (!$user || empty($user['email'])) {
            error_log("Order #{$order['id']} - User #{$order['user_id']} has no email address");
            return false;
        }

        $subject = "🚚 Đơn hàng #{$order['id']} đã được giao";
        $body = self::getEmailTemplate("
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #27ae60; margin: 0; font-size: 28px;'>🚚 Đơn hàng đã được giao!</h1>
                <p style='color: #7f8c8d; margin: 10px 0 0 0; font-size: 16px;'>TechMall - Cửa hàng công nghệ uy tín</p>
            </div>
            
            <div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 25px; border-radius: 10px; margin: 20px 0;'>
                <h2 style='color: #155724; margin: 0 0 15px 0; font-size: 20px;'>🎉 Tin vui!</h2>
                <p style='margin: 0; color: #155724; font-size: 16px; line-height: 1.6;'>
                    Đơn hàng của bạn đã được giao thành công! Chúng tôi hy vọng bạn hài lòng với sản phẩm đã mua.
                </p>
            </div>
            
            <div style='background: #f8f9fa; padding: 25px; border-radius: 10px; margin: 20px 0;'>
                <h2 style='color: #2c3e50; margin: 0 0 15px 0; font-size: 20px;'>📋 Thông tin đơn hàng</h2>
                <p style='margin: 8px 0; color: #34495e;'><strong>Mã đơn hàng:</strong> #{$order['id']}</p>
                <p style='margin: 8px 0; color: #34495e;'><strong>Trạng thái:</strong> <span style='color: #27ae60; font-weight: bold;'>Đã giao hàng</span></p>
            </div>
            
            <div style='background: #e8f5e8; border: 1px solid #c8e6c9; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #2e7d32; margin: 0 0 10px 0; font-size: 18px;'>⭐ Đánh giá sản phẩm</h3>
                <p style='margin: 0; color: #2e7d32; line-height: 1.6;'>
                    Chúng tôi rất mong nhận được đánh giá của bạn về sản phẩm và dịch vụ. 
                    Đánh giá của bạn sẽ giúp chúng tôi cải thiện chất lượng phục vụ.
                </p>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <p style='color: #7f8c8d; font-size: 14px; margin: 0;'>
                    Cảm ơn bạn đã tin tưởng và lựa chọn TechMall. Chúc bạn có trải nghiệm sử dụng sản phẩm tuyệt vời!
                </p>
                <p style='color: #7f8c8d; font-size: 12px; margin: 10px 0 0 0;'>
                    <a href='http://localhost/ecommerce_api/testcase/test%20html/policy.html' style='color: #3498db; text-decoration: none;'>📋 Xem chính sách bảo hành & đổi trả</a>
                </p>
            </div>
        ");
        return self::sendMail($user['email'], $subject, $body);
    }

    public static function sendOrderDelivered($order) {
        // Lấy email từ bảng users thông qua user_id
        require_once __DIR__ . '/../models/UserModel.php';
        $userModel = new UserModel();
        $user = $userModel->getUserById($order['user_id']);
        
        if (!$user || empty($user['email'])) {
            error_log("Order #{$order['id']} - User #{$order['user_id']} has no email address");
            return false;
        }

        $subject = "🎊 Đơn hàng #{$order['id']} đã hoàn thành thành công";
        $body = self::getEmailTemplate("
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #8e44ad; margin: 0; font-size: 28px;'>🎊 Đơn hàng đã hoàn thành!</h1>
                <p style='color: #7f8c8d; margin: 10px 0 0 0; font-size: 16px;'>TechMall - Cửa hàng công nghệ uy tín</p>
            </div>
            
            <div style='background: #f3e5f5; border: 1px solid #e1bee7; padding: 25px; border-radius: 10px; margin: 20px 0;'>
                <h2 style='color: #7b1fa2; margin: 0 0 15px 0; font-size: 20px;'>🎉 Chúc mừng!</h2>
                <p style='margin: 0; color: #7b1fa2; font-size: 16px; line-height: 1.6;'>
                    Đơn hàng của bạn đã được hoàn thành thành công! Chúng tôi rất vui mừng được phục vụ bạn.
                </p>
            </div>
            
            <div style='background: #f8f9fa; padding: 25px; border-radius: 10px; margin: 20px 0;'>
                <h2 style='color: #2c3e50; margin: 0 0 15px 0; font-size: 20px;'>📋 Thông tin đơn hàng</h2>
                <p style='margin: 8px 0; color: #34495e;'><strong>Mã đơn hàng:</strong> #{$order['id']}</p>
                <p style='margin: 8px 0; color: #34495e;'><strong>Trạng thái:</strong> <span style='color: #8e44ad; font-weight: bold;'>Hoàn thành</span></p>
            </div>
            
            <div style='background: #e8f5e8; border: 1px solid #c8e6c9; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #2e7d32; margin: 0 0 10px 0; font-size: 18px;'>💝 Cảm ơn bạn!</h3>
                <p style='margin: 0; color: #2e7d32; line-height: 1.6;'>
                    Cảm ơn bạn đã tin tưởng và lựa chọn TechMall. Chúng tôi hy vọng bạn hài lòng với sản phẩm và dịch vụ của chúng tôi.
                </p>
            </div>
            
            <div style='background: #fff3e0; border: 1px solid #ffcc02; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #e65100; margin: 0 0 10px 0; font-size: 18px;'>🔄 Mua sắm tiếp</h3>
                <p style='margin: 0; color: #e65100; line-height: 1.6;'>
                    Chúng tôi luôn có những sản phẩm công nghệ mới nhất và hấp dẫn. Hãy tiếp tục theo dõi để không bỏ lỡ những ưu đãi đặc biệt!
                </p>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <p style='color: #7f8c8d; font-size: 14px; margin: 0;'>
                    Chúc bạn có trải nghiệm sử dụng sản phẩm tuyệt vời và hẹn gặp lại bạn trong những lần mua sắm tiếp theo!
                </p>
            </div>
        ");
        return self::sendMail($user['email'], $subject, $body);
    }

    public static function sendResetPassword($toEmail, $token) {
        $subject = '🔐 Đặt lại mật khẩu tài khoản TechMall';
        $resetLink = "http://127.0.0.1:5500/test%20html/forgotpassword.html#" . urlencode($token);
        $body = self::getEmailTemplate("
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #e74c3c; margin: 0; font-size: 28px;'>🔐 Đặt lại mật khẩu</h1>
                <p style='color: #7f8c8d; margin: 10px 0 0 0; font-size: 16px;'>TechMall - Cửa hàng công nghệ uy tín</p>
            </div>
            
            <div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 25px; border-radius: 10px; margin: 20px 0;'>
                <h2 style='color: #856404; margin: 0 0 15px 0; font-size: 20px;'>🔒 Yêu cầu bảo mật</h2>
                <p style='margin: 0; color: #856404; font-size: 16px; line-height: 1.6;'>
                    Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn. Đây là thao tác bảo mật quan trọng.
                </p>
            </div>
            
            <div style='background: #f8f9fa; padding: 25px; border-radius: 10px; margin: 20px 0;'>
                <h2 style='color: #2c3e50; margin: 0 0 15px 0; font-size: 20px;'>📧 Thông tin tài khoản</h2>
                <p style='margin: 8px 0; color: #34495e;'><strong>Email:</strong> {$toEmail}</p>
                <p style='margin: 8px 0; color: #34495e;'><strong>Thời gian yêu cầu:</strong> " . date('d/m/Y H:i:s') . "</p>
            </div>
            
            <div style='background: #e3f2fd; border: 1px solid #bbdefb; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #1565c0; margin: 0 0 10px 0; font-size: 18px;'>🔗 Link đặt lại mật khẩu</h3>
                <p style='margin: 0 0 15px 0; color: #1565c0; line-height: 1.6;'>
                    Vui lòng nhấp vào nút bên dưới để đặt lại mật khẩu của bạn:
                </p>
                <div style='text-align: center; margin: 20px 0;'>
                    <a href='{$resetLink}' style='background: #3498db; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                        🔐 Đặt lại mật khẩu
                    </a>
                </div>
                <p style='margin: 15px 0 0 0; color: #1565c0; font-size: 14px; line-height: 1.6;'>
                    <strong>Lưu ý:</strong> Link này sẽ hết hạn sau 1 giờ để đảm bảo bảo mật.
                </p>
            </div>
            
            <div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3 style='color: #721c24; margin: 0 0 10px 0; font-size: 18px;'>⚠️ Cảnh báo bảo mật</h3>
                <p style='margin: 0; color: #721c24; line-height: 1.6;'>
                    Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này và liên hệ với chúng tôi ngay lập tức. 
                    Tài khoản của bạn có thể đang gặp rủi ro bảo mật.
                </p>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <p style='color: #7f8c8d; font-size: 14px; margin: 0;'>
                    Để bảo vệ tài khoản của bạn, vui lòng không chia sẻ thông tin này với bất kỳ ai.
                </p>
            </div>
        ");
        return self::sendMail($toEmail, $subject, $body);
    }
}
