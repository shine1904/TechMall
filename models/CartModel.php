<?php
require_once 'utils/Database.php';
require_once 'models/ProductModel.php';

class CartModel {
    private $db;
    private $productModel;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel();
    }
    
    public function getCart($user_id) {
        // Get or create cart for user
        $cart = $this->getUserCart($user_id);
        
        if (!$cart) {
            $cart_id = $this->createCart($user_id);
        } else {
            $cart_id = $cart['id'];
        }
        
        // Get cart items
        $query = "SELECT ci.*, p.product_name, p.price, p.thumbnail, p.id as product_id
                  FROM Cart_Items ci 
                  JOIN Product p ON ci.product_id = p.id 
                  WHERE ci.cart_id = :cart_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cart_id', $cart_id);
        $stmt->execute();
        
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // ✅ Xử lý thumbnail cho từng item bằng ProductModel
        foreach ($items as &$item) {
            // Sử dụng private method của ProductModel để xử lý thumbnail
            $item['thumbnail'] = $this->processItemThumbnail($item['thumbnail'], $item['product_id']);
        }
        unset($item); // Phá vỡ reference
        
        // Calculate total
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        return [
            'cart_id' => $cart_id,
            'items' => $items,
            'total' => $total
        ];
    }
    
    public function getUserCart($user_id) {
        $query = "SELECT * FROM Cart WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function createCart($user_id) {
        $query = "INSERT INTO Cart (user_id) VALUES (:user_id)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
    
    public function addToCart($cart_id, $product_id, $quantity) {
        // Check if product already in cart
        $query = "SELECT * FROM Cart_Items WHERE cart_id = :cart_id AND product_id = :product_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cart_id', $cart_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem['quantity'] + $quantity;
            return $this->updateCartItem($existingItem['id'], $newQuantity);
        } else {
            // Add new item
            $query = "INSERT INTO Cart_Items (cart_id, product_id, quantity) 
                      VALUES (:cart_id, :product_id, :quantity)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':cart_id', $cart_id);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':quantity', $quantity);
            
            return $stmt->execute();
        }
    }
    
    public function updateCartItem($item_id, $quantity) {
        if ($quantity <= 0) {
            return $this->removeFromCart($item_id);
        }
        
        $query = "UPDATE Cart_Items SET quantity = :quantity WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $item_id);
        $stmt->bindParam(':quantity', $quantity);
        
        return $stmt->execute();
    }
    
    public function removeFromCart($item_id) {
        $query = "DELETE FROM Cart_Items WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $item_id);
        
        return $stmt->execute();
    }
    
    public function clearCart($cart_id) {
        $query = "DELETE FROM Cart_Items WHERE cart_id = :cart_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cart_id', $cart_id);
        
        return $stmt->execute();
    }
    
    /**
     * Xử lý thumbnail cho cart item sử dụng logic của ProductModel
     */
    private function processItemThumbnail($thumbnail, $productId) {
        // Sử dụng reflection để gọi private method của ProductModel
        $reflection = new ReflectionClass($this->productModel);
        $method = $reflection->getMethod('processThumbnail');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->productModel, $thumbnail, $productId);
        
        // Trả về ảnh đầu tiên nếu là array
        return is_array($result) ? $result[0] : $result;
    }
}