<?php
require_once 'models/AdvertisementModel.php';
require_once 'utils/Response.php';

class AdvertisementController {
    private $advertisementModel;
    
    public function __construct() {
        $this->advertisementModel = new AdvertisementModel();
    }
    
    public function getAdvertisements($params, $user_data) {
        try {
            $advertisements = $this->advertisementModel->getAdvertisements();
            
            Response::sendSuccess($advertisements);
        } catch (Exception $e) {
            Response::sendError('Error retrieving advertisements: ' . $e->getMessage());
        }
    }
    
    public function getActiveAdvertisements($params, $user_data) {
        try {
            $advertisements = $this->advertisementModel->getActiveAdvertisements();
            
            Response::sendSuccess($advertisements);
        } catch (Exception $e) {
            Response::sendError('Error retrieving active advertisements: ' . $e->getMessage());
        }
    }
    
    public function getAdvertisementById($params, $user_data) {
        try {
            $id = $params[0];
            
            $advertisement = $this->advertisementModel->getAdvertisementById($id);
            
            if ($advertisement) {
                Response::sendSuccess($advertisement);
            } else {
                Response::sendError('Advertisement not found', 404);
            }
        } catch (Exception $e) {
            Response::sendError('Error retrieving advertisement: ' . $e->getMessage());
        }
    }
    
    public function createAdvertisement($params, $user_data) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $success = $this->advertisementModel->createAdvertisement($data);
            
            if ($success) {
                $ad_id = $this->advertisementModel->getLastInsertId();
                Response::sendSuccess(['ad_id' => $ad_id], 'Advertisement created successfully', 201);
            } else {
                Response::sendError('Failed to create advertisement');
            }
        } catch (Exception $e) {
            Response::sendError('Error creating advertisement: ' . $e->getMessage());
        }
    }
    
    public function updateAdvertisement($params, $user_data) {
        try {
            $id = $params[0];
            $data = json_decode(file_get_contents('php://input'), true);
            
            $advertisement = $this->advertisementModel->getAdvertisementById($id);
            if (!$advertisement) {
                Response::sendError('Advertisement not found', 404);
            }
            
            $success = $this->advertisementModel->updateAdvertisement($id, $data);
            
            if ($success) {
                Response::sendSuccess([], 'Advertisement updated successfully');
            } else {
                Response::sendError('Failed to update advertisement');
            }
        } catch (Exception $e) {
            Response::sendError('Error updating advertisement: ' . $e->getMessage());
        }
    }
    
    public function deleteAdvertisement($params, $user_data) {
        try {
            $id = $params[0];
            
            $advertisement = $this->advertisementModel->getAdvertisementById($id);
            if (!$advertisement) {
                Response::sendError('Advertisement not found', 404);
            }
            
            $success = $this->advertisementModel->deleteAdvertisement($id);
            
            if ($success) {
                Response::sendSuccess([], 'Advertisement deleted successfully');
            } else {
                Response::sendError('Failed to delete advertisement');
            }
        } catch (Exception $e) {
            Response::sendError('Error deleting advertisement: ' . $e->getMessage());
        }
    }
}