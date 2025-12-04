<?php
require_once 'models/ProductModel.php';
require_once 'utils/Response.php';
require_once 'middleware/ValidationMiddleware.php';

class ProductController {
    private $productModel;
    private $validator;
    
    public function __construct() {
        $this->productModel = new ProductModel();
        $this->validator = new ValidationMiddleware();
    }
    
    public function getProducts($params, $user_data) {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            
            $filters = [];
            if (isset($_GET['category_id'])) {
                $filters['category_id'] = $_GET['category_id'];
            }
            if (isset($_GET['min_price'])) {
                $filters['min_price'] = $_GET['min_price'];
            }
            if (isset($_GET['max_price'])) {
                $filters['max_price'] = $_GET['max_price'];
            }
            if (isset($_GET['search'])) {
                $filters['search'] = $_GET['search'];
            }
            
            $products = $this->productModel->getProducts($filters, $page, $limit);
            
            Response::sendSuccess($products);
        } catch (Exception $e) {
            Response::sendError('Error retrieving products: ' . $e->getMessage());
        }
    }
    
    public function getProductById($params, $user_data) {
        try {
            $id = $params[0];
            
            $product = $this->productModel->getProductById($id);
            
            if ($product) {
                Response::sendSuccess($product);
            } else {
                Response::sendError('Product not found', 404);
            }
        } catch (Exception $e) {
            Response::sendError('Error retrieving product: ' . $e->getMessage());
        }
    }
    
    public function createProduct($params, $user_data) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $errors = $this->validator->validateProduct($data);
            if (!empty($errors)) {
                Response::sendError('Validation failed', 400, $errors);
            }
            
            $success = $this->productModel->createProduct($data);
            
            if ($success) {
                $product_id = $this->productModel->getLastInsertId();
                Response::sendSuccess(['product_id' => $product_id], 'Product created successfully', 201);
            } else {
                Response::sendError('Failed to create product');
            }
        } catch (Exception $e) {
            Response::sendError('Error creating product: ' . $e->getMessage());
        }
    }
    
    public function updateProduct($params, $user_data) {
        try {
            $id = $params[0];
            $data = json_decode(file_get_contents('php://input'), true);
            
            $product = $this->productModel->getProductById($id);
            if (!$product) {
                Response::sendError('Product not found', 404);
            }
            
            $success = $this->productModel->updateProduct($id, $data);
            
            if ($success) {
                Response::sendSuccess([], 'Product updated successfully');
            } else {
                Response::sendError('Failed to update product');
            }
        } catch (Exception $e) {
            Response::sendError('Error updating product: ' . $e->getMessage());
        }
    }
    
    public function deleteProduct($params, $user_data) {
        try {
            $id = $params[0];
            
            $product = $this->productModel->getProductById($id);
            if (!$product) {
                Response::sendError('Product not found', 404);
            }
            
            $success = $this->productModel->deleteProduct($id);
            
            if ($success) {
                Response::sendSuccess([], 'Product deleted successfully');
            } else {
                Response::sendError('Failed to delete product');
            }
        } catch (Exception $e) {
            Response::sendError('Error deleting product: ' . $e->getMessage());
        }
    }
    public function search($params, $user_data) {
        try {
            if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
                Response::sendError("Thiếu từ khóa tìm kiếm", 400);
            }

            $keyword = trim($_GET['q']);
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;

            $products = $this->productModel->searchProducts($keyword, $limit);

            if ($products && count($products) > 0) {
                Response::sendSuccess($products);
            } else {
                Response::sendSuccess([], "Không tìm thấy sản phẩm");
            }
        } catch (Exception $e) {
            Response::sendError("Lỗi khi tìm kiếm: " . $e->getMessage(), 500);
        }
    }
     public function getRecommended($params, $user_data) {
        try {
            $excludeId = isset($_GET['exclude']) ? (int)$_GET['exclude'] : null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
            
            $products = $this->productModel->getRecommendedProducts($excludeId, $limit);
            
            Response::sendSuccess($products);
        } catch (Exception $e) {
            Response::sendError('Error retrieving recommended products: ' . $e->getMessage());
        }
    }

    public function syncImages($params, $user_data) {
        try {
            $updated = $this->productModel->syncAllProductImages();
            
            Response::sendSuccess([
                'updated_count' => $updated,
                'message' => "Đã đồng bộ ảnh cho $updated sản phẩm"
            ]);
        } catch (Exception $e) {
            Response::sendError('Error syncing images: ' . $e->getMessage());
        }
    }
}