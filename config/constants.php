<?php
// JWT Secret Key
define('JWT_SECRET', 'your-secret-key-change-in-production');
define('JWT_ALGORITHM', 'HS256');

// Response messages
define('MSG_SUCCESS', 'Success');
define('MSG_ERROR', 'Error');
define('MSG_UNAUTHORIZED', 'Unauthorized access');
define('MSG_INVALID_TOKEN', 'Invalid token');
define('MSG_INSUFFICIENT_PERMISSIONS', 'Insufficient permissions');
define('MSG_VALIDATION_ERROR', 'Validation error');

// User status
define('USER_ACTIVE', 1);
define('USER_INACTIVE', 0);

// Order status
define('ORDER_PENDING', 'pending');
define('ORDER_CONFIRMED', 'confirmed');
define('ORDER_SHIPPED', 'shipped');
define('ORDER_DELIVERED', 'delivered');
define('ORDER_CANCELLED', 'cancelled');