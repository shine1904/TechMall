<?php
require_once 'models/CategoryModel.php';
require_once 'utils/Response.php';
require_once 'middleware/ValidationMiddleware.php';

class CategoryController {
    private $categoryModel;
    private $validator;
    
    public function __construct() {
        $this->categoryModel = new CategoryModel();
        $this->validator = new ValidationMiddleware();
    }
    
    public function getCategories($params, $user_data) {
        try {
            $categories = $this->categoryModel->getCategories();
            
            Response::sendSuccess($categories);
        } catch (Exception $e) {
            Response::sendError('Error retrieving categories: ' . $e->getMessage());
        }
    }
    
    public function getCategoryById($params, $user_data) {
        try {
            $id = $params[0];
            
            $category = $this->categoryModel->getCategoryById($id);
            
            if ($category) {
                Response::sendSuccess($category);
            } else {
                Response::sendError('Category not found', 404);
            }
        } catch (Exception $e) {
            Response::sendError('Error retrieving category: ' . $e->getMessage());
        }
    }
    
    public function createCategory($params, $user_data) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $errors = $this->validator->validateCategory($data);
            if (!empty($errors)) {
                Response::sendError('Validation failed', 400, $errors);
            }
            
            $success = $this->categoryModel->createCategory($data);
            
            if ($success) {
                $category_id = $this->categoryModel->getLastInsertId();
                Response::sendSuccess(['category_id' => $category_id], 'Category created successfully', 201);
            } else {
                Response::sendError('Failed to create category');
            }
        } catch (Exception $e) {
            Response::sendError('Error creating category: ' . $e->getMessage());
        }
    }
    
    public function updateCategory($params, $user_data) {
        try {
            $id = $params[0];
            $data = json_decode(file_get_contents('php://input'), true);
            
            $category = $this->categoryModel->getCategoryById($id);
            if (!$category) {
                Response::sendError('Category not found', 404);
            }
            
            $success = $this->categoryModel->updateCategory($id, $data);
            
            if ($success) {
                Response::sendSuccess([], 'Category updated successfully');
            } else {
                Response::sendError('Failed to update category');
            }
        } catch (Exception $e) {
            Response::sendError('Error updating category: ' . $e->getMessage());
        }
    }
    
    public function deleteCategory($params, $user_data) {
        try {
            $id = $params[0];
            
            $category = $this->categoryModel->getCategoryById($id);
            if (!$category) {
                Response::sendError('Category not found', 404);
            }
            
            $success = $this->categoryModel->deleteCategory($id);
            
            if ($success) {
                Response::sendSuccess([], 'Category deleted successfully');
            } else {
                Response::sendError('Failed to delete category');
            }
        } catch (Exception $e) {
            Response::sendError('Error deleting category: ' . $e->getMessage());
        }
    }
}