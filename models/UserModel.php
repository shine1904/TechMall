

<?php
require_once 'utils/Database.php';

class UserModel {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    
    public function getUsers($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT u.*, GROUP_CONCAT(r.role_name) as roles 
                  FROM users u 
                  LEFT JOIN user_role ur ON u.id = ur.user_id 
                  LEFT JOIN roles r ON ur.role_id = r.id 
                  WHERE u.is_active = 1
                  GROUP BY u.id 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUserById($id) {
        $query = "SELECT u.*, GROUP_CONCAT(r.role_name) as roles 
                  FROM users u 
                  LEFT JOIN user_role ur ON u.id = ur.user_id 
                  LEFT JOIN roles r ON ur.role_id = r.id 
                  WHERE u.id = :id AND u.is_active = 1
                  GROUP BY u.id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getUserByEmail($email) {
        $query = "SELECT * FROM users WHERE email = :email";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function createUser($data) {
        $query = "INSERT INTO users (full_name, email, password, phone, address, card) 
                  VALUES (:full_name, :email, :password, :phone, :address, :card)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $data['password']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':card', $data['card']);
        
        return $stmt->execute();
    }
    
   public function updateUser($id, $data) {
    $query = "UPDATE users 
              SET full_name = :full_name, email = :email, phone = :phone, 
                  address = :address, card = :card, updated_at = CURRENT_TIMESTAMP";

    if (!empty($data['password'])) {
        $query .= ", password = :password";
    }

    $query .= " WHERE id = :id";

    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':full_name', $data['full_name']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':phone', $data['phone']);
    $stmt->bindParam(':address', $data['address']);
    $stmt->bindParam(':card', $data['card']);

    if (!empty($data['password'])) {
        $stmt->bindParam(':password', $data['password']);
    }

    return $stmt->execute();
}



    
    public function deleteUser($id) {
        $query = "UPDATE users SET is_active = FALSE WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    public function restoreUser($id) {
        $query = "UPDATE users SET is_active = TRUE WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    public function getInactiveUsers($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT u.*, GROUP_CONCAT(r.role_name) as roles 
                  FROM users u 
                  LEFT JOIN user_role ur ON u.id = ur.user_id 
                  LEFT JOIN roles r ON ur.role_id = r.id 
                  WHERE u.is_active = 0
                  GROUP BY u.id 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function assignRole($user_id, $role_id) {
        $query = "INSERT INTO user_role (user_id, role_id) VALUES (:user_id, :role_id) 
                  ON DUPLICATE KEY UPDATE role_id = :role_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':role_id', $role_id);
        
        return $stmt->execute();
    }
    
    public function getUserRoles($user_id) {
        $query = "SELECT r.* FROM user_role ur 
                  JOIN roles r ON ur.role_id = r.id 
                  WHERE ur.user_id = :user_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateUserRoles($user_id, $role_ids) {
        try {
            $this->db->beginTransaction();
            
            // Xóa tất cả role hiện tại
            $deleteQuery = "DELETE FROM user_role WHERE user_id = :user_id";
            $deleteStmt = $this->db->prepare($deleteQuery);
            $deleteStmt->bindParam(':user_id', $user_id);
            $deleteStmt->execute();
            
            // Thêm các role mới
            $insertQuery = "INSERT INTO user_role (user_id, role_id) VALUES (:user_id, :role_id)";
            $insertStmt = $this->db->prepare($insertQuery);
            
            foreach ($role_ids as $role_id) {
                $insertStmt->bindParam(':user_id', $user_id);
                $insertStmt->bindParam(':role_id', $role_id);
                $insertStmt->execute();
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveResetToken($userId, $token, $expiry) {
        $stmt = $this->db->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
        return $stmt->execute([$token, $expiry, $userId]);
    }

    public function findByResetToken($token) {
        try {
            // Log token để debug
            error_log("Searching for token: " . substr($token, 0, 10) . "...");
            
            $this->db->exec("SET time_zone = '+07:00'");
            $stmt = $this->db->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
            $stmt->execute([$token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                error_log("Token found for user: " . $user['email']);
            } else {
                // Kiểm tra xem token có tồn tại nhưng đã hết hạn không
                $stmt = $this->db->prepare("SELECT * FROM users WHERE reset_token = ?");
                $stmt->execute([$token]);
                $expiredUser = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($expiredUser) {
                    error_log("Token exists but expired for user: " . $expiredUser['email']);
                } else {
                    error_log("Token not found in database");
                }
            }
            
            return $user;
        } catch (Exception $e) {
            error_log("Error in findByResetToken: " . $e->getMessage());
            return false;
        }
    }

    public function updatePassword($userId, $hashedPassword) {
    $stmt = $this->db->prepare("
        UPDATE users 
        SET password = ?, 
            reset_token = NULL, 
            reset_token_expiry = NULL,
            password_changed = 1
        WHERE id = ?
    ");
    return $stmt->execute([$hashedPassword, $userId]);
}

    /**
     * Count active Super Admins in the system
     */
    public function countSuperAdmins() {
        try {
            $query = "SELECT COUNT(DISTINCT u.id) as super_admin_count 
                      FROM users u 
                      JOIN user_role ur ON u.id = ur.user_id 
                      JOIN roles r ON ur.role_id = r.id 
                      WHERE u.is_active = 1 AND r.role_name = 'super_admin'";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['super_admin_count'];
        } catch (Exception $e) {
            error_log("Error counting super admins: " . $e->getMessage());
            return 0;
        }
    }

}
