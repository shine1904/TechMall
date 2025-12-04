<?php
class Validator {
    private function hasInvalidChars($text) {
        // Cho phép chữ cái (cả Unicode), số, khoảng trắng, @._-
        return !preg_match('/^[\p{L}0-9\s@._-]+$/u', $text);
    }

    public function validateRegistration($data) {
    $errors = [];

    // Full name
    if (empty($data['full_name'])) {
        $errors['full_name'] = 'Họ tên không được để trống.';
    } elseif (strlen($data['full_name']) < 2) {
        $errors['full_name'] = 'Họ tên phải ít nhất 2 ký tự.';
    } elseif (!preg_match('/^[\p{L}\s]+$/u', $data['full_name'])) {
        $errors['full_name'] = 'Họ tên chỉ được chứa chữ và khoảng trắng.';
    }

    // Email
    if (empty($data['email'])) {
        $errors['email'] = 'Email không được để trống.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không hợp lệ.';
    }

    // Password
    if (empty($data['password'])) {
        $errors['password'] = 'Password không được để trống.';
    } elseif (strlen($data['password']) < 12) {
        $errors['password'] = 'Password phải ít nhất 12 ký tự.';
    } elseif (!preg_match('/^[A-Za-z0-9@._-]+$/', $data['password'])) {
        $errors['password'] = 'Password chỉ được chứa chữ, số và ký tự @ . _ -';
    }

    return $errors;
}


    public function validateLogin($data) {
        $errors = [];

        if (empty($data['email'])) {
            $errors['email'] = 'Email không được để trống.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ.';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Password không được để trống.';
        }

        return $errors;
    }

    public function validateProduct($data) {
        $errors = [];

        if (empty($data['product_name'])) {
            $errors['product_name'] = 'Product name is required';
        }

        if (empty($data['price'])) {
            $errors['price'] = 'Price is required';
        } elseif (!is_numeric($data['price']) || $data['price'] <= 0) {
            $errors['price'] = 'Price must be a positive number';
        }

        if (empty($data['category_id'])) {
            $errors['category_id'] = 'Category is required';
        } elseif (!is_numeric($data['category_id'])) {
            $errors['category_id'] = 'Category must be a number';
        }

        return $errors;
    }

    public function validateOrder($data) {
        $errors = [];

        if (empty($data['fullname'])) {
            $errors['fullname'] = 'Full name is required';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (empty($data['address'])) {
            $errors['address'] = 'Address is required';
        }

        if (empty($data['total']) || !is_numeric($data['total']) || $data['total'] <= 0) {
            $errors['total'] = 'Valid total amount is required';
        }

        return $errors;
    }
}
