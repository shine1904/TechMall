<?php
require_once 'utils/Validator.php';

class ValidationMiddleware {
    private $validator;
    
    public function __construct() {
        $this->validator = new Validator();
    }
    
    
    public function validateRegistration($data) {
        return $this->validator->validateRegistration($data);
    }
    
    public function validateLogin($data) {
        return $this->validator->validateLogin($data);
    }
    
    public function validateProduct($data) {
        return $this->validator->validateProduct($data);
    }
    
    public function validateOrder($data) {
        return $this->validator->validateOrder($data);
    }
    
    public function validateCategory($data) {
        $errors = [];
        
        if (empty($data['product_type'])) {
            $errors['product_type'] = 'Product type is required';
        }
        
        return $errors;
    }
}