<?php
require_once 'utils/Database.php';
require_once 'utils/Response.php';
require_once 'utils/JWT.php';
require_once 'middleware/ValidationMiddleware.php';
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../models/UserModel.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



class AuthController {
    private $db;
    private $validator;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->validator = new ValidationMiddleware();
        $this->userModel = new UserModel();
    }
    
   public function register() {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate input
        $errors = $this->validator->validateRegistration($data);
        if (!empty($errors)) {
            Response::sendError('Validation failed', 400, $errors);
        }

        // Check if email already exists
        $query = "SELECT id FROM users WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $data['email']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            Response::sendError('Email already exists', 400);
        }

        // Hash password
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

        // Chuẩn bị giá trị biến để tránh lỗi bindParam
        $full_name = $data['full_name'];
        $email     = $data['email'];
        $phone     = $data['phone'] ?? null;
        $address   = $data['address'] ?? null;

        // Create user
        $query = "INSERT INTO users (full_name, email, password, phone, address) 
                  VALUES (:full_name, :email, :password, :phone, :address)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);

        if ($stmt->execute()) {
            $user_id = $this->db->lastInsertId();

            // Assign default customer role
            $query = "INSERT INTO user_role (user_id, role_id) VALUES (:user_id, 2)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            Response::sendSuccess(
                ['user_id' => $user_id],
                'User registered successfully',
                201
            );
        } else {
            Response::sendError('Failed to create user');
        }
    } catch (Exception $e) {
        Response::sendError('Error: ' . $e->getMessage());
    }
}

    
    public function login() {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'];
    $password = $data['password'];

    // Lấy user
    $stmt = $this->db->prepare("SELECT id, full_name, email, password, is_active,password_changed 
                                FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        Response::sendError("Invalid credentials", 401);
    }
    
    // Check if user account is active
    if (!$user['is_active']) {
        Response::sendError("Account has been deactivated. Please contact administrator.", 403);
    }

    $userId = $user['id'];

    // Lấy trạng thái login_attempts
    $stmt = $this->db->prepare("SELECT * FROM login_attempts WHERE user_id = :uid");
    $stmt->execute([':uid' => $userId]);
    $attempt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$attempt) {
        // Nếu chưa có record → tạo mới
        $this->db->prepare("INSERT INTO login_attempts (user_id, attempts, last_attempt, locked_until) 
                            VALUES (:uid, 0, NOW(), NULL)")
                 ->execute([':uid' => $userId]);
        $attempt = [
            'attempts' => 0,
            'locked_until' => null
        ];
    }

    // Check nếu đang bị khóa
    if ($attempt['locked_until'] && strtotime($attempt['locked_until']) > time()) {
        $wait = strtotime($attempt['locked_until']) - time();
        Response::sendError("Account locked. Try again after {$wait} seconds", 403);
    }

    // Check password
    if (!password_verify($password, $user['password'])) {
        $newAttempts = $attempt['attempts'] + 1;
        $lock = null;

        if ($newAttempts >= 5) {
            $lock = date("Y-m-d H:i:s", strtotime("+5 minutes"));
            $newAttempts = 0; // reset count sau khi khóa
        }

        $this->db->prepare("UPDATE login_attempts 
                            SET attempts = :a, last_attempt = NOW(), locked_until = :l 
                            WHERE user_id = :uid")
                 ->execute([
                    ':a' => $newAttempts,
                    ':l' => $lock,
                    ':uid' => $userId
                 ]);

        Response::sendError("Invalid credentials", 401);
    }

    // Nếu login thành công
    $this->db->prepare("UPDATE login_attempts 
                        SET attempts = 0, locked_until = NULL, last_attempt = NOW() 
                        WHERE user_id = :uid")
             ->execute([':uid' => $userId]);

    // Lấy roles
    $stmt = $this->db->prepare("SELECT r.role_name 
                                FROM user_role ur 
                                JOIN roles r ON ur.role_id = r.id 
                                WHERE ur.user_id = :uid");
    $stmt->execute([':uid' => $userId]);
    $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Tạo JWT
    $jwt = new JWT();
    $token = $jwt->generateToken([
        'user_id' => $userId,
        'email'   => $user['email'],
        'roles'   => $roles
    ]);

     $message = "Đăng nhập thành công";
    if (!empty($user['password_changed']) && $user['password_changed'] == 1) {
        $message = "Đăng nhập thành công. Bạn vừa đổi mật khẩu thành công";

        // Reset flag về 0 sau khi thông báo
        $this->db->prepare("UPDATE users SET password_changed = 0 WHERE id = ?")
                 ->execute([$userId]);
    }

    Response::sendSuccess([
        'token' => $token,
        'user' => [
            'id' => $userId,
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'roles' => $roles
        ]
    ], $message);
}
private function sendResetEmail($toEmail, $token) {
    require_once __DIR__ . '/../utils/MailService.php';
    return MailService::sendResetPassword($toEmail, $token);
}


    // API: Quên mật khẩu
    public function forgotPassword($params, $user_data = null) {
        $input = json_decode(file_get_contents("php://input"), true);
        $email = $input['email'] ?? null;

        if (!$email) {
            return Response::sendError("Email is required", 400);
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            return Response::sendError("Email not found", 404);
        }

        $token = bin2hex(random_bytes(16));
      $this->db->exec("SET time_zone = '+07:00'");
$expiry = $this->db->query("SELECT NOW() + INTERVAL 1 HOUR as expiry")->fetch()['expiry'];
        $this->userModel->saveResetToken($user['id'], $token, $expiry);

        if ($this->sendResetEmail($email, $token)) {
            return Response::sendSuccess("Reset link sent to email");
        } else {
            return Response::sendError("Failed to send email", 500);
        }
    }

    // API: Reset mật khẩu
    public function resetPassword($params, $user_data = null) {
        $input = json_decode(file_get_contents("php://input"), true);
        $token = $input['token'] ?? null;
        $newPassword = $input['password'] ?? null;

        // Log request để debug
        error_log("Reset password request - Token: " . ($token ? substr($token, 0, 10) . "..." : "null"));

        if (!$token || !$newPassword) {
            error_log("Missing token or password in reset request");
            return Response::sendError("Token and new password are required", 400);
        }

        // Validate password strength
        if (strlen($newPassword) < 12) {
            return Response::sendError("Password must be at least 12 characters long", 400);
        } elseif (!preg_match('/^[A-Za-z0-9@._-]+$/', $newPassword)) {
            return Response::sendError("Password chỉ được chứa chữ, số và ký tự @ . _ -", 400);
        }

        $user = $this->userModel->findByResetToken($token);
        
        if (!$user) {
            error_log("Token not found or expired: " . substr($token, 0, 10) . "...");
            return Response::sendError("Invalid or expired token", 400);
        }

        error_log("Token found for user ID: " . $user['id']);

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $success = $this->userModel->updatePassword($user['id'], $hashed);

        if ($success) {
            error_log("Password updated successfully for user ID: " . $user['id']);
            return Response::sendSuccess("Password reset successful");
        } else {
            error_log("Failed to update password for user ID: " . $user['id']);
            return Response::sendError("Failed to update password", 500);
        }
    }

}