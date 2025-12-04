<?php
require_once 'utils/Database.php';

class CategoryModel {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    
    public function getCategories() {
        $query = "SELECT * FROM Category WHERE is_active = TRUE ORDER BY product_type";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCategoryById($id) {
        $query = "SELECT * FROM Category WHERE id = :id AND is_active = TRUE";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function createCategory($data) {
        $query = "INSERT INTO Category (product_type, description) 
                  VALUES (:product_type, :description)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':product_type', $data['product_type']);
        $stmt->bindParam(':description', $data['description']);
        
        return $stmt->execute();
    }
    
    public function updateCategory($id, $data) {
        $query = "UPDATE category SET product_type = :product_type, description = :description 
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':product_type', $data['product_type']);
        $stmt->bindParam(':description', $data['description']);
        
        return $stmt->execute();
    }
    
    public function deleteCategory($id) {
        $query = "UPDATE Category SET is_active = FALSE WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}