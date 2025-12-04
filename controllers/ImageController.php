<?php
require_once 'utils/Response.php';
require_once 'middleware/AuthMiddleware.php';
require_once 'middleware/RBACMiddleware.php';

class ImageController {
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    public function uploadProductImage($params, $user_data) {
        try {
            // Check permissions
            $rbac = new RBACMiddleware();
            if (!$rbac->checkPermission($user_data['user_id'], 'product.update')) {
                Response::sendError('Insufficient permissions', 403);
            }
            
            $product_id = $params[0] ?? null;
            if (!$product_id) {
                Response::sendError('Product ID is required', 400);
            }
            
            if (!isset($_FILES['image'])) {
                Response::sendError('No image file provided', 400);
            }
            
            $file = $_FILES['image'];
            $this->validateImage($file);
            
            // Create product directory
            $product_dir = "uploads/products/$product_id";
            if (!is_dir($product_dir)) {
                mkdir($product_dir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $filepath = "$product_dir/$filename";
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                Response::sendError('Failed to save image', 500);
            }
            
            // Update database
            require_once 'models/ProductModel.php';
            $productModel = new ProductModel();
            
            // Get current images
            $product = $productModel->getProductById($product_id);
            $currentImages = [];
            
            if (!empty($product['thumbnail'])) {
                // Parse existing images
                if ($product['thumbnail'][0] === '[') {
                    $currentImages = json_decode($product['thumbnail'], true);
                } else {
                    $currentImages = [$product['thumbnail']];
                }
            }
            
            // Add new image
            $currentImages[] = $filepath;
            
            // Update database
            $productModel->updateProductThumbnail($product_id, $currentImages);
            
            Response::sendSuccess([
                'image_path' => $filepath,
                'image_url' => $this->getFullUrl($filepath)
            ], 'Image uploaded successfully');
            
        } catch (Exception $e) {
            Response::sendError('Error uploading image: ' . $e->getMessage());
        }
    }
    
    public function setPrimaryImage($params, $user_data) {
        try {
            $rbac = new RBACMiddleware();
            if (!$rbac->checkPermission($user_data['user_id'], 'product.update')) {
                Response::sendError('Insufficient permissions', 403);
            }
            
            $product_id = $params[0] ?? null;
            $image_index = $_POST['image_index'] ?? 0;
            
            if (!$product_id) {
                Response::sendError('Product ID is required', 400);
            }
            
            require_once 'models/ProductModel.php';
            $productModel = new ProductModel();
            $product = $productModel->getProductById($product_id);
            
            if (empty($product['thumbnail'])) {
                Response::sendError('No images found', 404);
            }
            
            $images = json_decode($product['thumbnail'], true);
            
            if ($image_index >= count($images)) {
                Response::sendError('Invalid image index', 400);
            }
            
            // Move selected image to first position
            $selectedImage = $images[$image_index];
            unset($images[$image_index]);
            array_unshift($images, $selectedImage);
            
            // Update database
            $productModel->updateProductThumbnail($product_id, array_values($images));
            
            Response::sendSuccess([], 'Primary image updated successfully');
            
        } catch (Exception $e) {
            Response::sendError('Error setting primary image: ' . $e->getMessage());
        }
    }
    public function serveProductImage($params) {
        try {
            $product_id = $params[0] ?? null;
            $filename   = $params[1] ?? null;

            if (!$product_id || !$filename) {
                Response::sendError('Image not found', 404);
            }

            $filepath = "public/images/products/$product_id/$filename";

            if (!file_exists($filepath)) {
                // Nếu không tìm thấy ảnh, trả về ảnh mặc định
                $this->serveDefaultImage();
                return;
            }

            // Xác định MIME type
            $mime = mime_content_type($filepath);
            if (!in_array($mime, $this->allowedTypes)) {
                $this->serveDefaultImage();
                return;
            }

            // Set cache headers
            $lastModified = filemtime($filepath);
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
            header('Cache-Control: public, max-age=3600'); // Cache 1 hour
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');

            // Check if client has cached version
            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                $ifModifiedSince = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
                if ($lastModified <= $ifModifiedSince) {
                    http_response_code(304);
                    exit;
                }
            }

            // Trả file ra browser
            header('Content-Type: ' . $mime);
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit;

        } catch (Exception $e) {
            $this->serveDefaultImage();
        }
    }

    public function getProductImages($params, $user_data = null) {
        try {
            $product_id = $params[0] ?? null;
            
            if (!$product_id) {
                Response::sendError('Product ID is required', 400);
            }

            $images = $this->getProductImagesList($product_id);
            
            Response::sendSuccess([
                'product_id' => $product_id,
                'images' => $images,
                'total' => count($images)
            ]);

        } catch (Exception $e) {
            Response::sendError('Error getting product images: ' . $e->getMessage());
        }
    }

    private function getProductImagesList($product_id) {
        $product_dir = "public/images/products/$product_id";
        $images = [];

        if (!is_dir($product_dir)) {
            return $images;
        }

        $files = scandir($product_dir);
        $base_url = $this->getBaseUrl();

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $filepath = "$product_dir/$file";
            if (is_file($filepath)) {
                $mime = mime_content_type($filepath);
                if (in_array($mime, $this->allowedTypes)) {
                    $images[] = [
                        'filename' => $file,
                        'url' => "$base_url/api/images/products/$product_id/$file",
                        'size' => filesize($filepath),
                        'type' => $mime
                    ];
                }
            }
        }

        return $images;
    }

    private function serveDefaultImage() {
        $defaultPath = 'public/images/products/default/default-product.jpg';
        
        if (!file_exists($defaultPath)) {
            // Tạo ảnh placeholder đơn giản
            $this->createPlaceholderImage();
        }

        if (file_exists($defaultPath)) {
            $mime = mime_content_type($defaultPath);
            header('Content-Type: ' . $mime);
            header('Content-Length: ' . filesize($defaultPath));
            header('Cache-Control: public, max-age=86400'); // Cache 24 hours
            readfile($defaultPath);
        } else {
            // Fallback: trả về 1x1 pixel transparent PNG
            header('Content-Type: image/png');
            echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        }
        exit;
    }

    private function createPlaceholderImage() {
        $defaultDir = 'public/images/products/default';
        if (!is_dir($defaultDir)) {
            mkdir($defaultDir, 0755, true);
        }

        // Tạo ảnh placeholder 300x300
        $width = 300;
        $height = 300;
        $image = imagecreate($width, $height);
        
        // Colors
        $bg_color = imagecolorallocate($image, 240, 240, 240);
        $text_color = imagecolorallocate($image, 100, 100, 100);
        
        // Add text
        $text = 'No Image';
        $font_size = 5;
        $text_width = imagefontwidth($font_size) * strlen($text);
        $text_height = imagefontheight($font_size);
        $x = ($width - $text_width) / 2;
        $y = ($height - $text_height) / 2;
        
        imagestring($image, $font_size, $x, $y, $text, $text_color);
        
        // Save image
        imagejpeg($image, 'public/images/products/default/default-product.jpg', 80);
        imagedestroy($image);
    }

    private function getBaseUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
        $base = rtrim(dirname($script_name), '/\\');
        if ($base === '/' || $base === '.') $base = '';
        
        return "{$protocol}://{$host}{$base}";
    }

    private function validateImage($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload error: ' . $file['error']);
        }
        
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('File too large. Max size: ' . ($this->maxFileSize / 1024 / 1024) . 'MB');
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime, $this->allowedTypes)) {
            throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $this->allowedTypes));
        }
    }

    
}