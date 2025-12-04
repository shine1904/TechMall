<?php
class Response {
    public static function send($data, $status_code = 200) {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function sendSuccess($data = [], $message = 'Success', $status_code = 200) {
        self::send([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status_code);
    }

    public static function sendError($message = 'Error', $status_code = 500, $errors = []) {
        self::send([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $status_code);
    }
}