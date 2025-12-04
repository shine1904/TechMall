<?php
require_once 'utils/Database.php';

class AdvertisementModel {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    
    public function getAdvertisements() {
        $query = "SELECT * FROM Advertisement WHERE is_active = TRUE AND end_date >= CURDATE() 
                  ORDER BY start_date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAdvertisementById($id) {
        $query = "SELECT * FROM Advertisement WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function createAdvertisement($data) {
        $query = "INSERT INTO Advertisement (company_info, logo, start_date, end_date) 
                  VALUES (:company_info, :logo, :start_date, :end_date)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':company_info', $data['company_info']);
        $stmt->bindParam(':logo', $data['logo']);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        
        return $stmt->execute();
    }
    
    public function updateAdvertisement($id, $data) {
        $query = "UPDATE Advertisement SET company_info = :company_info, logo = :logo, 
                  start_date = :start_date, end_date = :end_date, is_active = :is_active 
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':company_info', $data['company_info']);
        $stmt->bindParam(':logo', $data['logo']);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        $stmt->bindParam(':is_active', $data['is_active']);
        
        return $stmt->execute();
    }
    
    public function deleteAdvertisement($id) {
        $query = "UPDATE Advertisement SET is_active = FALSE WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    public function getActiveAdvertisements() {
        $query = "SELECT * FROM Advertisement 
                  WHERE is_active = TRUE AND start_date <= CURDATE() AND end_date >= CURDATE() 
                  ORDER BY start_date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}