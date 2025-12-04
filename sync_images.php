<?php
// Sync product images - chạy 1 lần để cập nhật thumbnail
require_once 'config/database.php';
require_once 'utils/Database.php';
require_once 'models/ProductModel.php';

echo "=== SYNC PRODUCT IMAGES ===\n";

$productModel = new ProductModel();

// Lấy tất cả sản phẩm
$query = "SELECT id, product_name, thumbnail FROM Product WHERE is_active = TRUE";
$db = (new Database())->getConnection();
$stmt = $db->query($query);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updated = 0;
$skipped = 0;

foreach ($products as $product) {
    $productId = $product['id'];
    $productName = $product['product_name'];
    $currentThumbnail = $product['thumbnail'];
    
    echo "Processing Product ID: $productId - $productName\n";
    
    // Kiểm tra thư mục ảnh
    $imageDir = "public/images/products/$productId";
    
    if (is_dir($imageDir)) {
        // Quét ảnh trong thư mục
        $images = [];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $files = scandir($imageDir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($extension, $allowedExtensions)) {
                $images[] = $file;
            }
        }
        
        if (!empty($images)) {
            // Cập nhật thumbnail trong database
            $thumbnailJson = json_encode($images);
            
            $updateQuery = "UPDATE Product SET thumbnail = :thumbnail WHERE id = :id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':id', $productId);
            $updateStmt->bindParam(':thumbnail', $thumbnailJson);
            
            if ($updateStmt->execute()) {
                echo "  ✅ Updated: " . implode(', ', $images) . "\n";
                $updated++;
            } else {
                echo "  ❌ Failed to update database\n";
            }
        } else {
            echo "  ⚠️ No images found\n";
            $skipped++;
        }
    } else {
        echo "  ⚠️ Directory not found: $imageDir\n";
        $skipped++;
    }
}

echo "\n=== SUMMARY ===\n";
echo "Updated: $updated products\n";
echo "Skipped: $skipped products\n";
echo "Total: " . count($products) . " products\n";
?>