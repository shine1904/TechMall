<?php
require_once 'utils/cors.php';
require_once 'config/database.php';
require_once 'utils/Response.php';
require_once 'utils/JWT.php';
require_once 'middleware/AuthMiddleware.php';
require_once 'middleware/RBACMiddleware.php';
require_once 'middleware/AdminOnlyMiddleware.php';

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Lấy request method & URI
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];

// Chuẩn hoá URI -> bỏ prefix /ecommerce_api/index.php hoặc /ecommerce_api
$script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
if (strpos($request_uri, $script_name) === 0) {
    // Trường hợp URL chứa index.php
    $request_uri = substr($request_uri, strlen($script_name));
} else {
    // Trường hợp dùng Apache/Nginx rewrite: URI có dạng /ecommerce_api/api/...
    $base_path = rtrim(str_replace('\\', '/', dirname($script_name)), '/');
    if ($base_path && strpos($request_uri, $base_path) === 0) {
        $request_uri = substr($request_uri, strlen($base_path));
    }
}
$request_uri = rtrim($request_uri, "/");

// Fallback: Một số cấu hình Apache/XAMPP không truyền phần "/index.php/..." vào REQUEST_URI
// hoặc không bật AcceptPathInfo, dẫn tới không match route và báo "Endpoint not found".
// Thử lấy từ PATH_INFO hoặc tham số ?path=/api/...
if ($request_uri === '' || $request_uri === null) {
    if (!empty($_SERVER['PATH_INFO'])) {
        $request_uri = rtrim($_SERVER['PATH_INFO'], '/');
    } elseif (!empty($_GET['path'])) {
        $request_uri = '/' . ltrim(parse_url($_GET['path'], PHP_URL_PATH), '/');
        $request_uri = rtrim($request_uri, '/');
    }
}

// Routes định nghĩa
$routes = [
    // Auth routes (public)
    'POST:/api/register' => ['AuthController', 'register', []],
    'POST:/api/login'    => ['AuthController', 'login', []],

    // User routes (private)
    'GET:/api/users'        => ['UserController', 'getUsers', ['user.read']],
    'GET:/api/users/{id}'   => ['UserController', 'getUserById', ['user.read']],
    'PUT:/api/users/{id}'   => ['UserController', 'updateUser', ['user.update']],
    'DELETE:/api/users/{id}' => ['UserController', 'deleteUser', ['user.delete']],
    'GET:/api/user/profile'  => ['UserController', 'getCurrentUser', ['user.read']],
    
    // User role management (admin only)
    'GET:/api/users/{id}/roles' => ['UserController', 'getUserRoles', ['user.role.manage']],
    'PUT:/api/users/{id}/roles' => ['UserController', 'updateUserRoles', ['user.role.manage']],
    
    // User restoration (admin only)
    'PUT:/api/users/{id}/restore' => ['UserController', 'restoreUser', ['user.update']],
    'GET:/api/users/inactive' => ['UserController', 'getInactiveUsers', ['user.read']],

    // Product routes
    'GET:/api/products'        => ['ProductController', 'getProducts', []], // public
    'GET:/api/products/search' => ['ProductController', 'search', []], // public
    'GET:/api/products/{id}'   => ['ProductController', 'getProductById', []], // public
    'POST:/api/products'       => ['ProductController', 'createProduct', ['product.create']],
    'PUT:/api/products/{id}'   => ['ProductController', 'updateProduct', ['product.update']],
    'DELETE:/api/products/{id}' => ['ProductController', 'deleteProduct', ['product.delete']],

    // Category routes
    'GET:/api/categories'      => ['CategoryController', 'getCategories', []], // public
    'POST:/api/categories'     => ['CategoryController', 'createCategory', ['category.create']],
    'PUT:/api/categories/{id}' => ['CategoryController', 'updateCategory', ['category.update']],
    'DELETE:/api/categories/{id}' => ['CategoryController', 'deleteCategory', ['category.delete']],

    // Order routes (private)   
    'GET:/api/orders'                     => ['OrderController', 'getOrders', ['order.read']],
    'GET:/api/orders/user/{id}'           => ['OrderController', 'getUserOrders', ['order.read']],
    'POST:/api/orders'                    => ['OrderController', 'createOrder', ['order.create']],
    'GET:/api/orders/{id}'                => ['OrderController', 'getOrderById', ['order.read']],

    // Cập nhật đơn hàng chung (admin update thông tin)
    'PUT:/api/orders/{id}'                => ['OrderController', 'updateOrder', ['order.update']],

    // User tự cập nhật shipping info hoặc gửi yêu cầu hủy
    'PUT:/api/orders/{id}/user-update'    => ['OrderController', 'userUpdateOrder', ['order.update']],

    // Update trạng thái đơn (admin đổi trạng thái)
    'PUT:/api/orders/{id}/status'         => ['OrderController', 'updateOrderStatus', ['order.status.update']],

    // Xác nhận đơn (admin)
    'PUT:/api/orders/{order_id}/confirm'  => ['OrderController', 'confirmOrder', ['order.update']],

    // User cancel (authenticated users can cancel their own pending orders)
    'PUT:/api/orders/{id}/cancel'         => ['OrderController', 'cancelOrder', ['authenticated']],
    // Admin xác nhận hủy (admin only)
    'PUT:/api/orders/{id}/cancel/admin'   => ['OrderController', 'adminCancelOrder', ['order.status.update']],
    'PUT:/api/orders/pending-paypal/auto-cancel' => ['OrderController', 'autoCancelPendingPaypal', ['order.status.update']],



    // Cart routes (private)
    'GET:/api/cart'        => ['CartController', 'getCart', ['authenticated']],
    'POST:/api/cart'       => ['CartController', 'addToCart', ['authenticated']],
    'PUT:/api/cart/{id}'   => ['CartController', 'updateCartItem', ['authenticated']],
    'DELETE:/api/cart/{id}' => ['CartController', 'removeFromCart', ['authenticated']],

    // Admin routes (private) - Chỉ super_admin mới truy cập được
    'GET:/api/admin/verify'           => ['AdminController', 'verifyAdmin', ['authenticated']],
    'GET:/api/admin/dashboard'        => ['SuperAdminController', 'getDashboard', []],
    'GET:/api/admin/sales'            => ['AdminController', 'getSalesReport', ['sales.view']],
    'GET:/api/admin/check-access'     => ['SuperAdminController', 'checkAdminAccess', []],
    
    // Admin Product Management (Super Admin Only)
    'GET:/api/admin/products'         => ['SuperAdminController', 'getAllProducts', []],
    'POST:/api/admin/products'        => ['SuperAdminController', 'createProduct', []],
    'PUT:/api/admin/products/{id}'    => ['SuperAdminController', 'updateProduct', []],
    'DELETE:/api/admin/products/{id}' => ['SuperAdminController', 'deleteProduct', []],
    
    // Admin Order Management (Super Admin Only)
    'GET:/api/admin/orders'                   => ['SuperAdminController', 'getAllOrders', []],
    'PUT:/api/admin/orders/{id}/status'       => ['SuperAdminController', 'updateOrderStatus', []],
    'PUT:/api/admin/orders/{id}/confirm'      => ['SuperAdminController', 'confirmOrder', []],
    'PUT:/api/admin/orders/{id}/cancel'       => ['SuperAdminController', 'adminCancelOrder', []],
    
    // Admin User Management (Super Admin Only)  
    'GET:/api/admin/users'            => ['SuperAdminController', 'getAllUsers', []],
    'PUT:/api/admin/users/{id}'       => ['SuperAdminController', 'updateUser', []],
    'DELETE:/api/admin/users/{id}'    => ['SuperAdminController', 'deleteUser', []],
     // Image routes
    'GET:/api/products/{id}/images'  => ['ImageController', 'getProductImages', []], // public
    'POST:/api/products/{id}/images' => ['ImageController', 'uploadProductImage', ['product.update']],
    'POST:/api/products/{id}/images/primary' => ['ImageController', 'setPrimaryImage', ['product.update']],
    'DELETE:/api/products/{id}/images' => ['ImageController', 'deleteProductImage', ['product.update']],
    'GET:/api/images/products/{id}/{filename}' => ['ImageController', 'serveProductImage', []], // public

    // Payment routes (private)
    'POST:/api/payment/{id}/cod' => ['PaymentController', 'processCOD', ['order.create']],
    'POST:/api/payment/{id}/momo' => ['PaymentController', 'createMomoPayment', ['order.create']],
    'GET:/api/payment/{id}/status' => ['PaymentController', 'checkPaymentStatus', ['order.read']],
    'GET:/api/payment/{id}/continue' => ['PaymentController', 'continuePayment', ['order.read']],
    'POST:/api/payment/momo-callback' => ['PaymentController', 'processMomoCallback', []],
    'GET:/api/payment/momo-callback' => ['PaymentController', 'processMomoCallback', []],
    'POST:/api/payment/{id}/paypal' => ['PaymentController', 'createPaypalPayment', ['order.create']],
    'GET:/api/payment/paypal-success' => ['PaymentController', 'processPaypalSuccess', []],
    'GET:/api/payment/paypal-cancel' => ['PaymentController', 'processPaypalCancel', []],


    
    // Serve images directly
    'POST:/api/products/{id}/images' => ['ImageController', 'uploadProductImage', ['product.update']],
    'POST:/api/products/{id}/images/primary' => ['ImageController', 'setPrimaryImage', ['product.update']],
    'DELETE:/api/products/{id}/images' => ['ImageController', 'deleteProductImage', ['product.update']],
    'GET:/api/images/products/{id}/{filename}' => ['ImageController', 'serveProductImage', []],



    // Forgot password (public)
     'POST:/api/forgot-password' => ['AuthController', 'forgotPassword', []],
    'POST:/api/reset-password'  => ['AuthController', 'resetPassword', []],

];

// Tìm route khớp
$matched_route = null;
$params = [];

foreach ($routes as $route => $handler) {
    list($method, $path) = explode(':', $route, 2);

    // Lấy tên param từ {id}, {slug}...
    preg_match_all('/\{([a-z_]+)\}/', $path, $paramNames);

    // Tạo regex thay thế
    $pattern = preg_replace('/\{[a-z_]+\}/', '([^/]+)', $path);
    $pattern = "#^$pattern$#";

    if ($request_method == $method && preg_match($pattern, $request_uri, $matches)) {
        $matched_route = $handler;
        array_shift($matches);

        // ánh xạ: {id} => value
        $params = array_values($matches);

        break;
    }
}



if ($matched_route) {
    list($controller_name, $method_name, $required_permissions) = $matched_route;

    try {
        // Include controller
        require_once "controllers/$controller_name.php";

        $user_data = null;

        // Nếu route yêu cầu quyền => cần Auth
        if (!empty($required_permissions)) {
            error_log("Route requires permissions: " . implode(', ', $required_permissions));
            
            $auth = new AuthMiddleware();
            $user_data = $auth->authenticate();
            
            error_log("Authenticated user: " . json_encode($user_data));

            // Check RBAC
            $rbac = new RBACMiddleware();
            $has_permission = false;
            foreach ($required_permissions as $permission) {
                if ($rbac->checkPermission($user_data['user_id'], $permission)) {
                    $has_permission = true;
                    break;
                }
            }
            if (!$has_permission) {
                error_log("Permission denied for user " . $user_data['user_id'] . " - required: " . implode(', ', $required_permissions));
                Response::sendError('Insufficient permissions', 403);
            }
        }

        // Gọi controller
        $controller = new $controller_name();
        $controller->$method_name($params, $user_data);

    } catch (Exception $e) {
        Response::sendError($e->getMessage(), $e->getCode() ?: 500);
    }
} else {
    Response::sendError('Endpoint not found', 404);
}
