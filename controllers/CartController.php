<?php
require_once 'models/CartModel.php';
require_once 'utils/Response.php';

class CartController {
    private $cartModel;
    
    public function __construct() {
        $this->cartModel = new CartModel();
    }
    
    public function getCart($params, $user_data) {
        try {
            $cart = $this->cartModel->getCart($user_data['user_id']);
            
            Response::sendSuccess($cart);
        } catch (Exception $e) {
            Response::sendError('Error retrieving cart: ' . $e->getMessage());
        }
    }
    
    public function addToCart($params, $user_data) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['product_id']) || empty($data['quantity'])) {
                Response::sendError('Product ID and quantity are required', 400);
            }
            
            // Get user's cart
            $cart = $this->cartModel->getUserCart($user_data['user_id']);
            if (!$cart) {
                $cart_id = $this->cartModel->createCart($user_data['user_id']);
            } else {
                $cart_id = $cart['id'];
            }
            
            $success = $this->cartModel->addToCart($cart_id, $data['product_id'], $data['quantity']);
            
            if ($success) {
                Response::sendSuccess([], 'Product added to cart successfully');
            } else {
                Response::sendError('Failed to add product to cart');
            }
        } catch (Exception $e) {
            Response::sendError('Error adding to cart: ' . $e->getMessage());
        }
    }
    
    public function updateCartItem($params, $user_data) {
        try {
            $item_id = $params[0];
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['quantity'])) {
                Response::sendError('Quantity is required', 400);
            }
            
            $success = $this->cartModel->updateCartItem($item_id, $data['quantity']);
            
            if ($success) {
                Response::sendSuccess([], 'Cart item updated successfully');
            } else {
                Response::sendError('Failed to update cart item');
            }
        } catch (Exception $e) {
            Response::sendError('Error updating cart item: ' . $e->getMessage());
        }
    }
    
    public function removeFromCart($params, $user_data) {
        try {
            $item_id = $params[0];
            
            $success = $this->cartModel->removeFromCart($item_id);
            
            if ($success) {
                Response::sendSuccess([], 'Item removed from cart successfully');
            } else {
                Response::sendError('Failed to remove item from cart');
            }
        } catch (Exception $e) {
            Response::sendError('Error removing from cart: ' . $e->getMessage());
        }
    }
}