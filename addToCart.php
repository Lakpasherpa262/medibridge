<?php
session_start();
include 'scripts/connect.php';

if(!isset($_SESSION['user_id'])) {
    die('Please login to add items to cart');
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        
        if(!$product_id || !$quantity || $quantity < 1) {
            throw new Exception('Invalid product or quantity');
        }
        
        // Check product exists and has enough quantity
        $stmt = $conn->prepare("SELECT P_Quantity FROM products WHERE P_Id = :pid");
        $stmt->bindParam(':pid', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        
        if($stmt->rowCount() === 0) {
            throw new Exception('Product not found');
        }
        
        $product = $stmt->fetch();
        if($product['P_Quantity'] < $quantity) {
            throw new Exception('Not enough stock available');
        }
        
        // Add to cart
        if(!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if(isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        
        // Calculate total items in cart
        $cart_count = array_sum($_SESSION['cart']);
        
        // Return simple response (not JSON)
        echo "success:$cart_count";
        
    } catch(Exception $e) {
        die('error:' . $e->getMessage());
    }
} else {
    die('Invalid request method');
}
?>