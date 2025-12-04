<?php
require_once '../config/database.php';

class BaseController {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Validate required fields
     */
    protected function validateRequired($data, $required_fields) {
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Field '$field' is required");
            }
        }
    }

    /**
     * Sanitize input data
     */
    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)));
    }

    /**
     * Get pagination parameters
     */
    protected function getPaginationParams($params) {
        $page = isset($params['page']) ? max(1, intval($params['page'])) : 1;
        $limit = isset($params['limit']) ? min(100, max(1, intval($params['limit']))) : 20;
        $offset = ($page - 1) * $limit;
        
        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset
        ];
    }
}
?>
