<?php
require_once 'utils/JWT.php';
require_once 'utils/Response.php';

class AuthMiddleware {
    public function authenticate() {
        // Lấy header theo nhiều cách và normalize key về lowercase
        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } elseif (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        }
        $normalized = [];
        foreach ($headers as $key => $value) {
            $normalized[strtolower($key)] = $value;
        }

        // Ưu tiên header chuẩn, fallback sang biến môi trường server
        $auth_header = $normalized['authorization'] ?? null;
        if (!$auth_header) {
            if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
                $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
            } elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $auth_header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
        }

        if (!$auth_header) {
            throw new Exception('Authorization header missing', 401);
        }
        
        $token = str_replace('Bearer ', '', $auth_header);
        
        if (empty($token)) {
            throw new Exception('Access token required', 401);
        }
        
        try {
            $jwt = new JWT();
            $payload = $jwt->decode($token);
            
            return $payload;
        } catch (Exception $e) {
            throw new Exception('Invalid token: ' . $e->getMessage(), 401);
        }
    }
}