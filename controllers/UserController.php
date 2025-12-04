<?php
require_once 'models/UserModel.php';
require_once 'utils/Response.php';
require_once 'middleware/ValidationMiddleware.php';

class UserController {
    private $userModel;
    private $validator;
    
    public function __construct() {
        $this->userModel = new UserModel();
        $this->validator = new ValidationMiddleware();
    }
    
    public function getUsers($params, $user_data) {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            
            $result = $this->userModel->getUsers($page, $limit);
            
            Response::sendSuccess($result);
        } catch (Exception $e) {
            Response::sendError('Error retrieving users: ' . $e->getMessage());
        }
    }
    
    public function getUserById($params, $user_data) {
        try {
            $id = $params[0];
            
            // Users can only view their own profile unless they're admin
            if ($user_data['user_id'] != $id && !$this->isAdmin($user_data)) {
                Response::sendError('Access denied', 403);
            }
            
            $user = $this->userModel->getUserById($id);
            
            if ($user) {
                Response::sendSuccess($user);
            } else {
                Response::sendError('User not found', 404);
            }
        } catch (Exception $e) {
            Response::sendError('Error retrieving user: ' . $e->getMessage());
        }
    }
    
 public function updateUser($params, $user_data) {
    try {
        $id = $params[0];
        
        // Chỉ cho user tự update hoặc admin
        if ($user_data['user_id'] != $id && !$this->isAdmin($user_data)) {
            Response::sendError('Access denied', 403);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $user = $this->userModel->getUserById($id);
        if (!$user) {
            Response::sendError('User not found', 404);
        }

        // Nếu có password mới thì validate
        if (!empty($data['password'])) {
            $validator = new Validator();
            $errors = $validator->validateRegistration([
                'full_name' => $data['full_name'] ?? $user['full_name'],
                'email'     => $data['email'] ?? $user['email'],
                'password'  => $data['password'],
            ]);

            if (!empty($errors['password'])) {
                Response::sendError($errors['password'], 400);
            }

            // Nếu pass hợp lệ thì hash
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $success = $this->userModel->updateUser($id, $data);
        
        if ($success) {
            Response::sendSuccess([], 'User updated successfully');
        } else {
            Response::sendError('Failed to update user');
        }
    } catch (Exception $e) {
        Response::sendError('Error updating user: ' . $e->getMessage());
    }
}



    
    public function deleteUser($params, $user_data) {
        try {
            $id = $params[0];
            
            // Users cannot delete themselves, only admins can delete users
            if (!$this->isAdmin($user_data)) {
                Response::sendError('Access denied', 403);
            }
            
            if ($user_data['user_id'] == $id) {
                Response::sendError('Cannot delete your own account', 400);
            }
            
            $user = $this->userModel->getUserById($id);
            if (!$user) {
                Response::sendError('User not found', 404);
            }
            
            // Business Rule: Prevent deleting Super Admins if you're not Super Admin
            $targetUserRoles = $this->userModel->getUserRoles($id);
            $targetIsSuperAdmin = $this->isUserSuperAdmin($targetUserRoles);
            
            if ($targetIsSuperAdmin && !$this->isSuperAdmin($user_data)) {
                Response::sendError('Only Super Admins can delete other Super Admin accounts', 403);
                return;
            }
            
            // Business Rule: Ensure at least one Super Admin exists in the system
            if ($targetIsSuperAdmin) {
                $superAdminCount = $this->userModel->countSuperAdmins();
                if ($superAdminCount <= 1) {
                    Response::sendError('Cannot delete the last Super Admin: At least one Super Admin must exist in the system', 403);
                    return;
                }
            }
            
            $success = $this->userModel->deleteUser($id);
            
            if ($success) {
                Response::sendSuccess([], 'User deleted successfully');
            } else {
                Response::sendError('Failed to delete user');
            }
        } catch (Exception $e) {
            Response::sendError('Error deleting user: ' . $e->getMessage());
        }
    }
    
    private function isAdmin($user_data) {
        return in_array('super_admin', $user_data['roles']) || 
               in_array('product_admin', $user_data['roles']) || 
               in_array('order_admin', $user_data['roles']) || 
               in_array('content_admin', $user_data['roles']) || 
               in_array('report_admin', $user_data['roles']);
    }
    
    /**
     * Check if user is Super Admin
     */
    private function isSuperAdmin($user_data) {
        return in_array('super_admin', $user_data['roles']);
    }
    
    /**
     * Check if user roles contain super_admin
     */
    private function isUserSuperAdmin($user_roles) {
        foreach ($user_roles as $role) {
            if ($role['role_name'] === 'super_admin' || $role['id'] == 3) {
                return true;
            }
        }
        return false;
    }
    public function getCurrentUser($params, $user_data) {
    try {
        // Lấy user_id từ dữ liệu xác thực (middleware đã decode token)
        $id = $user_data['user_id'];

        $user = $this->userModel->getUserById($id);
        if ($user) {
            // Trả về thông tin cơ bản cần dùng ở payment.html
            Response::sendSuccess([
                'full_name' => $user['full_name'] ?? '',
                'phone'     => $user['phone'] ?? '',
                'address'   => $user['address'] ?? ''
            ]);
        } else {
            Response::sendError('User not found', 404);
        }
    } catch (Exception $e) {
        Response::sendError('Error retrieving current user: ' . $e->getMessage());
    }
}

    /**
     * Get user roles
     * GET /api/users/{id}/roles
     */
    public function getUserRoles($params, $user_data) {
        try {
            $user_id = $params[0];
            
            // Only admin can view other users' roles
            if ($user_data['user_id'] != $user_id && !$this->isAdmin($user_data)) {
                Response::sendError('Access denied', 403);
                return;
            }
            
            $roles = $this->userModel->getUserRoles($user_id);
            Response::sendSuccess($roles);
            
        } catch (Exception $e) {
            Response::sendError('Error retrieving user roles: ' . $e->getMessage());
        }
    }
    
    /**
     * Update user roles  
     * PUT /api/users/{id}/roles
     */
    public function updateUserRoles($params, $user_data) {
        try {
            $user_id = $params[0];
            
            // Only admin can update user roles
            if (!$this->isAdmin($user_data)) {
                Response::sendError('Admin access required', 403);
                return;
            }
            
            // Business Rule: Prevent Super Admins from modifying their own roles
            if ($user_data['user_id'] == $user_id && $this->isSuperAdmin($user_data)) {
                Response::sendError('Super Admins cannot modify their own roles for security reasons', 403);
                return;
            }
            
            // Business Rule: Prevent modifying roles of other Super Admins (unless you are also Super Admin)
            $targetUser = $this->userModel->getUserById($user_id);
            if (!$targetUser) {
                Response::sendError('User not found', 404);
                return;
            }
            
            $targetUserRoles = $this->userModel->getUserRoles($user_id);
            $targetIsSuperAdmin = $this->isUserSuperAdmin($targetUserRoles);
            
            if ($targetIsSuperAdmin && !$this->isSuperAdmin($user_data)) {
                Response::sendError('Only Super Admins can modify other Super Admin roles', 403);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $role_ids = $input['role_ids'] ?? [];
            
            if (empty($role_ids)) {
                Response::sendError('Role IDs are required', 400);
                return;
            }
            
            // Validate role IDs
            foreach ($role_ids as $role_id) {
                if (!is_numeric($role_id) || $role_id < 1) {
                    Response::sendError('Invalid role ID: ' . $role_id, 400);
                    return;
                }
            }
            
            // Business Rule: Ensure at least one Super Admin exists in the system
            // If this is the last Super Admin and we're removing super_admin role, prevent it
            if ($targetIsSuperAdmin && !in_array(3, $role_ids)) { // Assuming role ID 3 is super_admin
                $superAdminCount = $this->userModel->countSuperAdmins();
                if ($superAdminCount <= 1) {
                    Response::sendError('Cannot remove Super Admin role: At least one Super Admin must exist in the system', 403);
                    return;
                }
            }
            
            $success = $this->userModel->updateUserRoles($user_id, $role_ids);
            
            if ($success) {
                Response::sendSuccess(null, 'User roles updated successfully');
            } else {
                Response::sendError('Failed to update user roles', 500);
            }
            
        } catch (Exception $e) {
            Response::sendError('Error updating user roles: ' . $e->getMessage());
        }
    }
    
    /**
     * Restore deactivated user
     * PUT /api/users/{id}/restore
     */
    public function restoreUser($params, $user_data) {
        try {
            $user_id = $params[0];
            
            // Only admin can restore users
            if (!$this->isAdmin($user_data)) {
                Response::sendError('Admin access required', 403);
                return;
            }
            
            $success = $this->userModel->restoreUser($user_id);
            
            if ($success) {
                Response::sendSuccess(null, 'User restored successfully');
            } else {
                Response::sendError('Failed to restore user', 500);
            }
            
        } catch (Exception $e) {
            Response::sendError('Error restoring user: ' . $e->getMessage());
        }
    }
    
    /**
     * Get inactive users
     * GET /api/users/inactive
     */
    public function getInactiveUsers($params, $user_data) {
        try {
            // Only admin can view inactive users
            if (!$this->isAdmin($user_data)) {
                Response::sendError('Admin access required', 403);
                return;
            }
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            
            $result = $this->userModel->getInactiveUsers($page, $limit);
            
            Response::sendSuccess($result);
        } catch (Exception $e) {
            Response::sendError('Error retrieving inactive users: ' . $e->getMessage());
        }
    }

}